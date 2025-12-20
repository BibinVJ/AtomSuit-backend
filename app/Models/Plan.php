<?php

namespace App\Models;

use App\Enums\PlanIntervalEnum;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'interval',
        'interval_count',
        'is_trial_plan',
        'trial_duration_in_days',
        'is_expired_user_plan',
        'stripe_product_id',
        'stripe_price_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'interval' => PlanIntervalEnum::class,
        'is_trial_plan' => 'boolean',
        'is_expired_user_plan' => 'boolean',
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
            'plan_id',      // Foreign key on subscriptions table
            'id',            // Foreign key on tenants table
            'id',            // Local key on plans table
            'user_id'        // Local key on subscriptions table (NOT tenant_id)
        );
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class)->orderBy('display_order');
    }

    /**
     * Check if plan has a specific feature.
     */
    public function hasFeature(string $featureKey): bool
    {
        return $this->features()->where('feature_key', $featureKey)->exists();
    }

    /**
     * Get feature value by key.
     */
    public function getFeature(string $featureKey, $default = null)
    {
        $feature = $this->features()->where('feature_key', $featureKey)->first();

        return $feature ? $feature->value : $default;
    }

    /**
     * Get all features as key-value array.
     */
    public function getFeaturesArray(): array
    {
        return $this->features->pluck('value', 'feature_key')->toArray();
    }
}
