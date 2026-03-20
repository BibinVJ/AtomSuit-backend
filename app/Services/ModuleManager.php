<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModuleManager
{
    /**
     * Check if a module is enabled for a tenant.
     */
    public function isEnabled(Tenant $tenant, string $slug): bool
    {
        $module = Module::where('slug', $slug)->first();

        if (! $module || ! $module->is_active) {
            return false;
        }

        return DB::table('tenant_modules')
            ->where('tenant_id', $tenant->getTenantKey())
            ->where('module_id', $module->id)
            ->exists();
    }

    /**
     * Get all enabled modules for a tenant.
     */
    public function getEnabledModules(Tenant $tenant): Collection
    {
        return $tenant->modules()->where('is_active', true)->get();
    }

    /**
     * Get all available modules with tenant's enabled status.
     */
    public function getAllWithStatus(Tenant $tenant): Collection
    {
        $enabledIds = DB::table('tenant_modules')
            ->where('tenant_id', $tenant->getTenantKey())
            ->pluck('module_id')
            ->toArray();

        return Module::active()->get()->map(function (Module $module) use ($enabledIds) {
            $module->is_enabled = in_array($module->id, $enabledIds);

            return $module;
        });
    }

    /**
     * Sync tenant modules from their current plan.
     * Adds plan modules (source: 'plan'), preserves admin/purchased modules.
     */
    public function syncFromPlan(Tenant $tenant, Plan $plan): void
    {
        $planModuleIds = $plan->modules()->pluck('modules.id')->toArray();

        // Remove old plan-sourced modules that are no longer in the plan
        TenantModule::where('tenant_id', $tenant->getTenantKey())
            ->where('source', 'plan')
            ->whereNotIn('module_id', $planModuleIds)
            ->delete();

        // Add new plan modules
        foreach ($planModuleIds as $moduleId) {
            TenantModule::updateOrCreate(
                [
                    'tenant_id' => $tenant->getTenantKey(),
                    'module_id' => $moduleId,
                ],
                [
                    'source' => 'plan',
                    'enabled_at' => now(),
                ]
            );
        }
    }

    /**
     * Enable a module for a tenant (admin or purchased).
     */
    public function enableForTenant(Tenant $tenant, string $slug, string $source = 'admin'): TenantModule
    {
        $module = Module::where('slug', $slug)->firstOrFail();

        return TenantModule::updateOrCreate(
            [
                'tenant_id' => $tenant->getTenantKey(),
                'module_id' => $module->id,
            ],
            [
                'source' => $source,
                'enabled_at' => now(),
            ]
        );
    }

    /**
     * Disable a module for a tenant.
     */
    public function disableForTenant(Tenant $tenant, string $slug): bool
    {
        $module = Module::where('slug', $slug)->firstOrFail();

        return TenantModule::where('tenant_id', $tenant->getTenantKey())
            ->where('module_id', $module->id)
            ->delete() > 0;
    }
}
