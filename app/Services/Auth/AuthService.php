<?php

namespace App\Services\Auth;

use App\DataTransferObjects\AuthenticatedUserDTO;
use App\Enums\TenantStatusEnum;
use App\Enums\UserStatus;
use App\Jobs\SendWelcomeUserMailJob;
use App\Models\User;
use App\Models\CentralUser;
use App\Models\Tenant;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected TokenService $tokenService,
        protected SessionTrackerService $sessionTracker
    ) {}

    /**
     * Get the appropriate user model class
     */
    protected function getUserModel(): string
    {
        return tenant() ? User::class : CentralUser::class;
    }

    /**
     * Register a new tenant
     * 
     * Registration is allowed only in the central domain.
     * And user is not created in the users table, but a tenant is created.
     */
    public function register(array $data): AuthenticatedUserDTO
    {
        if (tenant()) {
            throw new UnauthorizedHttpException('', 'Registration is only allowed in the central domain');
        }

        Tenant::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => TenantStatusEnum::ACTIVE->value,
            'trial_ends_at' => null,
            'grace_period_ends_at' => null,
            'email_verified_at' => now(),
            'password' => Hash::make('Example@123'),
            'load_sample_data' => true,
            'domain_name' => 'company',
            'plan_id' => $plan->id,
        ]);

        // $token = $this->tokenService->create($user);

        // return new AuthenticatedUserDTO($user, $token);
    }

    public function login(string $identifier, string $password): AuthenticatedUserDTO
    {
        $userModel = $this->getUserModel();

        \Log::info($userModel);

        // Determine if identifier is an email or phone
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        if (! tenant()) {
            // Central user login - only email is supported
            if (!$isEmail) {
                throw new UnauthorizedHttpException('', 'Only email login is supported for central users');
            }

            $user = $userModel::where('email', $identifier)->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                throw new UnauthorizedHttpException('', 'Invalid credentials');
            }

            // Central users don't require email verification check for now
            // You can add it if needed: 
            // if (is_null($user->email_verified_at)) {
            //     throw new UnauthorizedHttpException('', 'Email not verified');
            // }

        } else {
            // Tenant user login - supports both email and phone
            $user = $userModel::where($isEmail ? 'email' : 'phone', $identifier)->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                throw new UnauthorizedHttpException('', 'Invalid credentials');
            }

            if ($isEmail && is_null($user->email_verified_at)) {
                throw new UnauthorizedHttpException('', 'Email not verified');
            }

            if (! $isEmail && is_null($user->phone_verified_at)) {
                throw new UnauthorizedHttpException('', 'Phone not verified');
            }
        }

        if ($user->status->value !== UserStatus::ACTIVE->value) {
            throw new UnauthorizedHttpException('', 'User account is not active');
        }

        $token = $this->tokenService->create($user);

        return new AuthenticatedUserDTO($user, $token);
    }

    public function logout(User|CentralUser $user, bool $fromAllDevices = false): void
    {
        if ($fromAllDevices) {
            $this->tokenService->revokeAll($user);
            $this->sessionTracker->markAllSessionsLoggedOut($user);
        } else {
            $this->tokenService->revokeCurrent($user);
            $this->sessionTracker->markSessionLoggedOutByToken($user->token()?->id);
        }
    }

    public function resetUserPassword(User $user, string $newPassword): void
    {
        $this->userRepository->updatePassword($user, $newPassword);
    }
}
