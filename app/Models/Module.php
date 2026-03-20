<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Plans that include this module.
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_modules')
            ->withTimestamps();
    }

    /**
     * Tenants that have this module enabled.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_modules')
            ->withPivot('source', 'enabled_at')
            ->withTimestamps();
    }

    /**
     * Scope: only modules (not integrations).
     */
    public function scopeModules($query)
    {
        return $query->where('type', 'module');
    }

    /**
     * Scope: only integrations.
     */
    public function scopeIntegrations($query)
    {
        return $query->where('type', 'integration');
    }

    /**
     * Scope: only globally active.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
