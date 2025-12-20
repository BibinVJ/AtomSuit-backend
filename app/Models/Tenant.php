<?php

namespace App\Models;

use App\Enums\TenantStatusEnum;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Cashier\Billable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use AppAudit, Billable, HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'status',
        'trial_ends_at',
        'grace_period_ends_at',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'data',

        // for creating purpose alone
        'email_verified_at',
        'password',
        'load_sample_data',
        'domain_name',
        'plan_id',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'status' => TenantStatusEnum::class,
        'data' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'status',
            'trial_ends_at',
            'grace_period_ends_at',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'data',
        ];
    }

    public function domain(): HasOne
    {
        return $this->hasOne(Domain::class);
    }

    public function plan()
    {
        // Direct plan relationship (used for trial/lifetime plans)
        return $this->currentSubscription?->plan ?? $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        // Use our extended Subscription model
        return $this->hasMany(Subscription::class, 'user_id', 'id');
    }

    public function currentSubscription(): HasOne
    {
        // Use our extended Subscription model which has the plan relationship
        return $this->hasOne(Subscription::class, 'user_id', 'id')
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->where('name', 'default')
            ->latestOfMany();
    }

    public function currentPlanPriceId(): ?string
    {
        $sub = $this->currentSubscription()->first();

        return $sub?->items()->first()?->stripe_price;
    }

    /**
     * Check if tenant is active
     */
    public function isActive(): bool
    {
        return $this->status === TenantStatusEnum::ACTIVE->value
            && optional($this->currentSubscription)->stripe_status === 'active';
    }

    /**
     * Check if tenant is in trial period
     */
    // public function isTrial(): bool
    // {
    //     return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    // }

    /**
     * Get the current active plan (from subscription or direct plan_id).
     */
    public function getCurrentPlan(): ?Plan
    {
        // First check active subscription's plan
        $subscription = $this->currentSubscription;
        if ($subscription && $subscription->plan) {
            return $subscription->plan;
        }

        // Fallback to direct plan relationship (for trial/lifetime)
        return $this->plan;
    }

    /**
     * Check if tenant has access to a specific feature.
     */
    public function hasFeature(string $featureKey): bool
    {
        $plan = $this->getCurrentPlan();

        return $plan ? $plan->hasFeature($featureKey) : false;
    }

    /**
     * Get feature value for tenant's current plan.
     */
    public function getFeature(string $featureKey, $default = null)
    {
        $plan = $this->getCurrentPlan();

        return $plan ? $plan->getFeature($featureKey, $default) : $default;
    }

    /**
     * Check if a module is enabled for this tenant.
     */
    public function hasModule(string $moduleName): bool
    {
        return (bool) $this->getFeature("module_{$moduleName}", false);
    }

    /**
     * Get quota/limit for a feature (e.g., device_limit, storage_gb).
     */
    public function getQuota(string $quotaKey, $default = 0)
    {
        return $this->getFeature($quotaKey, $default);
    }
}
