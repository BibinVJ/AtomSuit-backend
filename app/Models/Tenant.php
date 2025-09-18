<?php

namespace App\Models;

use App\Enums\TenantStatusEnum;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'status',
        'trial_ends_at',
        'grace_period_ends_at',
        'current_subscription_id',
        'data',
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

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // Current active subscription (optional)
    public function currentSubscription()
    {
        return $this->belongsTo(Subscription::class, 'current_subscription_id');
    }

    // Shortcut to current plan via current subscription
    public function currentPlan()
    {
        return $this->currentSubscription()?->plan();
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
    public function isTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

}
