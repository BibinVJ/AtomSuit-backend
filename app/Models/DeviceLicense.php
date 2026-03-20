<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceLicense extends Model
{
    protected $fillable = [
        'tenant_id',
        'device_key',
        'device_name',
        'is_activated',
        'activated_at',
        'source',
        'provisioned_by',
        'expires_at',
    ];

    protected $casts = [
        'is_activated' => 'boolean',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope: only activated devices.
     */
    public function scopeActivated($query)
    {
        return $query->where('is_activated', true);
    }

    /**
     * Scope: only for a specific tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
