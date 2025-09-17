<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants.
     */
    public function index(): JsonResponse
    {
        $tenants = Tenant::with('domains')->paginate(15);

        return response()->json([
            'data' => $tenants->items(),
            'pagination' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ],
        ]);
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'domain' => 'required|string|max:255|unique:domains,domain',
            'plan' => 'string|in:basic,pro,enterprise',
        ]);

        $tenant = Tenant::create([
            'data' => [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'plan' => $validated['plan'] ?? 'basic',
            ],
        ]);

        $tenant->domains()->create([
            'domain' => $validated['domain'],
        ]);

        return response()->json([
            'message' => 'Tenant created successfully',
            'tenant' => $tenant->load('domains'),
        ], 201);
    }

    /**
     * Display the specified tenant.
     */
    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json([
            'tenant' => $tenant->load('domains'),
        ]);
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'plan' => 'sometimes|string|in:basic,pro,enterprise',
            'domain' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('domains', 'domain')->ignore($tenant->domains->first()?->id),
            ],
        ]);

        $data = $tenant->data ?? [];

        if (isset($validated['name'])) {
            $data['name'] = $validated['name'];
        }

        if (isset($validated['email'])) {
            $data['email'] = $validated['email'];
        }

        if (isset($validated['plan'])) {
            $data['plan'] = $validated['plan'];
        }

        $tenant->update(['data' => $data]);

        if (isset($validated['domain']) && $tenant->domains->first()) {
            $tenant->domains->first()->update([
                'domain' => $validated['domain'],
            ]);
        }

        return response()->json([
            'message' => 'Tenant updated successfully',
            'tenant' => $tenant->load('domains'),
        ]);
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy(Tenant $tenant): JsonResponse
    {
        $tenant->delete();

        return response()->json([
            'message' => 'Tenant deleted successfully',
        ]);
    }
}