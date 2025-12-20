<?php

namespace App\Services;

use App\Enums\TenantStatusEnum;
use App\Models\ArchivedTenant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\Tenant;
use App\Repositories\TenantRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TenantService
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected DomainService $domainService,
        protected StripeSubscriptionService $stripeSubscriptionService
    ) {}

    /**
     * Get comprehensive tenant statistics.
     */
    public function getStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', TenantStatusEnum::ACTIVE->value)->count();
        $suspendedTenants = Tenant::where('status', TenantStatusEnum::SUSPENDED->value)->count();

        // Tenants on trial (trial_ends_at is in the future)
        $onTrial = Tenant::where('trial_ends_at', '>', now())
            ->where('status', TenantStatusEnum::ACTIVE->value)
            ->count();

        // Tenants in grace period
        $inGracePeriod = Tenant::where('grace_period_ends_at', '>', now())
            ->count();

        // Expired tenants (trial/grace period ended)
        $expired = Tenant::where(function ($query) {
            $query->where('trial_ends_at', '<=', now())
                ->orWhere('grace_period_ends_at', '<=', now());
        })
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->whereIn('stripe_status', ['active', 'trialing']);
            })
            ->count();

        // Paid subscribers (active Stripe subscriptions)
        $paidSubscribers = Tenant::whereHas('subscriptions', function ($query) {
            $query->whereIn('stripe_status', ['active', 'trialing']);
        })->count();

        // Breakdown by plan - get from subscriptions or direct plan relationship
        $planBreakdown = [];

        // Get plans from active subscriptions
        $subscriptionPlans = Subscription::whereIn('stripe_status', ['active', 'trialing'])
            ->with('plan:id,name')
            ->get()
            ->groupBy(fn ($sub) => $sub->plan?->name ?? 'Unknown')
            ->map(fn ($subs) => $subs->count())
            ->toArray();

        // Get plans from tenants without active subscriptions (trial/expired users)
        $directPlans = Tenant::with('plan:id,name')
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->whereIn('stripe_status', ['active', 'trialing']);
            })
            ->get()
            ->groupBy(fn ($tenant) => $tenant->plan?->name ?? 'No Plan')
            ->map(fn ($tenants) => $tenants->count())
            ->toArray();

        // Merge both arrays
        foreach ($subscriptionPlans as $planName => $count) {
            $planBreakdown[$planName] = ($planBreakdown[$planName] ?? 0) + $count;
        }

        foreach ($directPlans as $planName => $count) {
            $planBreakdown[$planName] = ($planBreakdown[$planName] ?? 0) + $count;
        }

        // Revenue stats (from subscription invoices)
        $totalRevenue = SubscriptionInvoice::where('payment_status', 'paid')
            ->sum('amount');

        $monthlyRevenue = SubscriptionInvoice::where('payment_status', 'paid')
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('amount');

        // Recent registrations (last 30 days)
        $recentRegistrations = Tenant::where('created_at', '>=', now()->subDays(30))->count();

        // Archived tenants
        $archivedTotal = ArchivedTenant::count();

        return [
            'overview' => [
                'total' => $totalTenants,
                'active' => $activeTenants,
                'suspended' => $suspendedTenants,
                'on_trial' => $onTrial,
                'in_grace_period' => $inGracePeriod,
                'expired' => $expired,
                'paid_subscribers' => $paidSubscribers,
                'recent_registrations' => $recentRegistrations,
                'archived' => $archivedTotal,
            ],
            'plans' => $planBreakdown,
            'revenue' => [
                'total' => number_format($totalRevenue, 2),
                'this_month' => number_format($monthlyRevenue, 2),
                'currency' => 'USD',
            ],
            'growth' => [
                'last_30_days' => $recentRegistrations,
                'conversion_rate' => $totalTenants > 0
                    ? number_format(($paidSubscribers / $totalTenants) * 100, 2).'%'
                    : '0%',
            ],
        ];
    }

    public function create(array $data): Tenant
    {
        $plan = Plan::findOrFail($data['plan_id']);

        // check domain availability
        $this->domainService->checkDomainAvailability($data['domain_name']);

        $tenant = Tenant::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => $data['email_verified_at'] ?? null,
            'status' => TenantStatusEnum::ACTIVE->value,
            'trial_ends_at' => $plan->is_trial_plan ? now()->addDays($plan->trial_duration_in_days) : null,
            'grace_period_ends_at' => null,
            'plan_id' => $plan->id,
            'domain_name' => $data['domain_name'],
            'load_sample_data' => $data['load_sample_data'],
        ]);

        // Create manual subscription for paid plans (offline payment)
        // Skip for trial plans as they will create subscription after payment
        if (! $plan->is_trial_plan) {
            $this->createManualSubscription($tenant, $plan);
        }

        return $tenant;
    }

    /**
     * Create a manual subscription for offline payments or admin-created tenants.
     * This is used when payment is received outside of Stripe (cash, bank transfer, etc.)
     * or when superadmin creates a tenant directly with a paid plan.
     * Also creates an initial invoice record.
     */
    public function createManualSubscription(Tenant $tenant, Plan $plan): Subscription
    {
        // Generate a unique subscription ID for manual subscriptions
        $stripeId = 'manual_'.$plan->slug.'_'.$tenant->id;

        $subscription = $tenant->subscriptions()->updateOrCreate(
            ['stripe_id' => $stripeId],
            [
                'name' => 'default',
                'type' => 'default',
                'stripe_status' => 'active',
                'stripe_price' => $plan->stripe_price_id,
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => null, // No end date for lifetime, or set based on plan
                'plan_id' => $plan->id,
            ]
        );

        // Create initial invoice for manual subscription (if not exists)
        if (! $subscription->subscriptionInvoices()->exists()) {
            SubscriptionInvoice::create([
                'subscription_id' => $subscription->id,
                'amount' => $plan->price,
                'currency' => 'USD',
                'payment_status' => 'paid',
                'transaction_id' => 'manual_'.$subscription->id.'_initial',
                'invoice_date' => now(),
                'metadata' => [
                    'payment_method' => 'manual',
                    'note' => 'Offline payment - initial subscription',
                    'plan_name' => $plan->name,
                ],
            ]);

            Log::info('Manual subscription invoice created', [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'amount' => $plan->price,
            ]);
        }

        return $subscription;
    }

    /**
     * Delete tenant and archive their information.
     * This will:
     * 1. Archive tenant data for future marketing/records
     * 2. Cancel active Stripe subscriptions
     * 3. Delete tenant database and files
     * 4. Delete tenant record from database
     *
     * @param  string|null  $reason  Reason for deletion
     */
    public function delete(Tenant $tenant, ?string $reason = null): void
    {
        // Archive tenant data first (before deletion)
        $this->archiveTenant($tenant, $reason);

        // Cancel active Stripe subscriptions
        $this->stripeSubscriptionService->cancelTenantSubscriptions($tenant);

        // Delete tenant (this will trigger tenant database and file deletion via events)
        $this->tenantRepository->delete($tenant);
    }

    /**
     * Archive tenant data for future reference and marketing.
     */
    protected function archiveTenant(Tenant $tenant, ?string $reason = null): ArchivedTenant
    {
        $currentSubscription = $tenant->currentSubscription;
        $plan = $tenant->plan ?? $currentSubscription?->plan;
        $domain = $tenant->domain;

        return ArchivedTenant::create([
            'tenant_id' => $tenant->id,
            'name' => $tenant->name,
            'email' => $tenant->email,
            'phone' => $tenant->phone,
            'domain' => $domain?->domain ?? 'N/A',
            'plan_name' => $plan?->name,
            'plan_price' => $plan?->price,
            'stripe_id' => $tenant->stripe_id,
            'stripe_subscription_id' => $currentSubscription?->stripe_id,
            'registered_at' => $tenant->created_at,
            'deleted_at' => now(),
            'deletion_reason' => $reason,
            'metadata' => [
                'trial_ends_at' => $tenant->trial_ends_at,
                'status' => $tenant->status->value ?? $tenant->status,
                'total_subscriptions' => $tenant->subscriptions()->count(),
            ],
        ]);
    }
}
