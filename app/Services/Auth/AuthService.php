<?php

namespace App\Services\Auth;

use App\DataTransferObjects\AuthenticatedUserDTO;
use App\Enums\UserStatus;
use App\Helpers\AuthResponseFormatter;
use App\Http\Resources\UserResource;
use App\Models\CentralUser;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\ContextAwareService;
use App\Services\TenantService;
use Illuminate\Support\Facades\Hash;
use Laravel\Cashier\Cashier;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService extends ContextAwareService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected TokenService $tokenService,
        protected SessionTrackerService $sessionTracker
    ) {}

    /**
     * Register a new tenant
     *
     * Central-only: Provision tenant, admin, token, and optionally prepare checkout.
     */
    public function register(array $data): array
    {
        if (tenant()) {
            throw new UnauthorizedHttpException('', 'Registration is only allowed in the central domain');
        }

        // Resolve trial plan for initial access
        $trialPlan = Plan::where('is_trial_plan', true)->first();
        if (! $trialPlan) {
            throw new UnauthorizedHttpException('', 'Trial plan is not configured');
        }

        // Create tenant via service to set trial and validate domain
        $tenant = app(TenantService::class)->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'plan_id' => $trialPlan->id,
            'domain_name' => $data['domain_name'],
            'load_sample_data' => (bool) ($data['load_sample_data'] ?? false),
        ]);

        // Create admin token inside tenant context
        $authPayload = null;
        $tenant->run(function () use ($tenant, &$authPayload) {
            /** @var User|null $found */
            $found = User::where('email', $tenant->email)->first();
            if (! $found) {
                throw new UnauthorizedHttpException('', 'Admin user not created');
            }
            $token = $this->tokenService->create($found);

            $dto = new AuthenticatedUserDTO($found, $token);
            $authPayload = AuthResponseFormatter::format($dto);
            // Resolve resource and all nested resources to plain array within tenant context
            if (isset($authPayload['user']) && $authPayload['user'] instanceof UserResource) {
                $authPayload['user'] = json_decode(
                    $authPayload['user']->toResponse(app('request'))->getContent(),
                    true
                );
            }
        });

        // Domain URL
        $domain = $tenant->domains()->first();
        $subdomainUrl = $domain ? ('http://'.$domain->domain) : null; // protocol configurable

        // If a paid plan is selected, prepare checkout
        $checkoutUrl = null;
        if (! empty($data['plan_id'])) {
            $selectedPlan = Plan::findOrFail((int) $data['plan_id']);

            $client = Cashier::stripe();
            if (empty($selectedPlan->stripe_price_id)) {
                throw new UnauthorizedHttpException('', 'Selected plan is not linked to Stripe');
            }

            if (! $tenant->stripe_id) {
                $customer = $client->customers->create([
                    'email' => $tenant->email,
                    'name' => $tenant->name,
                    'metadata' => ['tenant_id' => $tenant->id],
                ]);

                $tenant->stripe_id = $customer->id;
                $tenant->save();
            }

            $session = $client->checkout->sessions->create([
                'mode' => 'subscription',
                'customer' => $tenant->stripe_id,
                'line_items' => [[
                    'price' => $selectedPlan->stripe_price_id,
                    'quantity' => 1,
                ]],
                'success_url' => $subdomainUrl.'/dashboard?payment_status=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $subdomainUrl.'/dashboard?payment_status=fail',
                'client_reference_id' => $tenant->id,
            ]);

            $checkoutUrl = $session->url;
        }

        return [
            'auth' => $authPayload,
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'subdomain_url' => $subdomainUrl,
            ],
            'checkout_url' => $checkoutUrl,
        ];
    }

    public function login(string $identifier, string $password): AuthenticatedUserDTO
    {
        $userModel = $this->getUserModel();

        // Determine if identifier is an email or phone
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        if ($this->isCentralContext()) {
            // Central user login - only email is supported
            if (! $isEmail) {
                throw new UnauthorizedHttpException('', 'Only email login is supported for central users');
            }

            $user = $userModel::where('email', $identifier)->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                throw new UnauthorizedHttpException('', 'Invalid credentials');
            }

            if (is_null($user->email_verified_at)) {
                throw new UnauthorizedHttpException('', 'Email not verified');
            }

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
