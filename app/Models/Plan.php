<?php

namespace App\Models;

use App\Enums\PlanIntervalEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'interval',
        'interval_count',
        'is_trial_plan',
        'trial_duration',
        'is_expired_user_plan',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'interval' => PlanIntervalEnum::class,
        'is_trial_plan' => 'boolean',
        'is_expired_user_plan' => 'boolean',
    ];

    public function tenant(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
