<?php

namespace App\Models;

use App\Enums\PlanIntervalEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'interval',
        'interval_count',
        'is_trial_plan',
        'trial_duration_in_days',
        'is_expired_user_plan',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'interval' => PlanIntervalEnum::class,
        'is_trial_plan' => 'boolean',
        'is_expired_user_plan' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscribedTenants(): HasManyThrough
    {
        return $this->hasManyThrough(
            Tenant::class,
            Subscription::class,
            'plan_id',
            'id',
            'id',
            'tenant_id'
        );
    }
}
