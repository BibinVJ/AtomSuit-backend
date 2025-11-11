<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_type',
        'feature_value',
        'display_name',
        'description',
        'display_order',
    ];

    /**
     * Get the typed value based on feature_type.
     */
    public function getValueAttribute()
    {
        return match ($this->feature_type) {
            'boolean' => filter_var($this->feature_value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->feature_value,
            default => $this->feature_value,
        };
    }

    /**
     * Get the plan that owns this feature.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
