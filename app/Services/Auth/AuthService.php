<?php

namespace App\Services\Auth;

use App\DataTransferObjects\AuthenticatedUserDTO;
use App\Enums\UserStatus;
use App\Jobs\SendWelcomeUserMailJob;
use App\Models\User;
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

    public function register(array $data): AuthenticatedUserDTO
    {
        $user = $this->userRepository->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'status'   => UserStatus::PENDING,
            'status_updated_at' => now(),
        ]);

        $user->assignRole($data['role']);
        $token = $this->tokenService->create($user);

        // dispatch welcome mail with user credentials
        dispatch(new SendWelcomeUserMailJob($user, $data['password']));
        
        return new AuthenticatedUserDTO($user, $token);
    }

    public function login(string $email, string $password): AuthenticatedUserDTO
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new UnauthorizedHttpException('', 'Invalid credentials');
        }

        if ($user->status !== UserStatus::ACTIVE) {
            throw new UnauthorizedHttpException('', 'User account is not active');
        }

        $token = $this->tokenService->create($user);
        return new AuthenticatedUserDTO($user, $token);
    }

    public function logout(User $user, bool $fromAllDevices = false): void
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
