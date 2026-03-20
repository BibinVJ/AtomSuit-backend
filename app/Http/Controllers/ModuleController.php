<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Module;
use App\Models\Tenant;
use App\Services\ModuleManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleController extends Controller
{
    public function __construct(protected ModuleManager $moduleManager) {}

    /**
     * List all modules (Super Admin — central context).
     */
    public function index()
    {
        $modules = Module::all();

        return ApiResponse::success('Modules retrieved successfully.', $modules);
    }

    /**
     * List modules with enabled status for the current tenant.
     */
    public function tenantModules()
    {
        $tenant = tenant();

        if (! $tenant) {
            return ApiResponse::error('No tenant context.', Response::HTTP_BAD_REQUEST);
        }

        $modules = $this->moduleManager->getAllWithStatus($tenant);

        return ApiResponse::success('Modules retrieved successfully.', $modules);
    }

    /**
     * Enable a module for a specific tenant (Super Admin).
     */
    public function enableForTenant(Request $request, Tenant $tenant)
    {
        $request->validate([
            'slug' => 'required|string|exists:modules,slug',
        ]);

        $tenantModule = $this->moduleManager->enableForTenant(
            $tenant,
            $request->input('slug'),
            'admin'
        );

        return ApiResponse::success('Module enabled successfully.', [
            'tenant_id' => $tenant->getTenantKey(),
            'module' => $tenantModule->module->slug,
            'source' => $tenantModule->source,
        ], Response::HTTP_CREATED);
    }

    /**
     * Disable a module for a specific tenant (Super Admin).
     */
    public function disableForTenant(Request $request, Tenant $tenant, string $slug)
    {
        $this->moduleManager->disableForTenant($tenant, $slug);

        return ApiResponse::success('Module disabled successfully.');
    }

    /**
     * Get modules for a specific tenant (Super Admin view).
     */
    public function tenantModulesList(Tenant $tenant)
    {
        $modules = $this->moduleManager->getAllWithStatus($tenant);

        return ApiResponse::success('Tenant modules retrieved successfully.', $modules);
    }
}
