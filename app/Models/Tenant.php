<?php

namespace App\Models;

use App\Enums\TenantStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Cashier\Billable;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, Billable;

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

    public function subscriptions(): HasMany
    {
        // Cashier's Subscription model, FK user_id references tenants.id
        return $this->hasMany(\Laravel\Cashier\Subscription::class, 'user_id', 'id');
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(\Laravel\Cashier\Subscription::class, 'user_id', 'id')
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
            && optional($this->currentSubscription)->is_active;
    }

    /**
     * Check if tenant is in trial period
     */
    // public function isTrial(): bool
    // {
    //     return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    // }

}
