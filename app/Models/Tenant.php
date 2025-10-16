<?php

namespace App\Models;

use App\Enums\TenantStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;

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
            'data',
        ];
    }

    public function domain(): HasOne
    {
        return $this->hasOne(Domain::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription():HasOne
    {
        return $this->hasOne(Subscription::class)->where('is_active', true)->latestOfMany();
    }

    // Shortcut to current plan via current subscription
    public function currentPlan(): HasOne
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
    // public function isTrial(): bool
    // {
    //     return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    // }

}
