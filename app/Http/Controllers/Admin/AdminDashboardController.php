<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetTenantContextRequest;
use App\Models\Tenant;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with tenant list.
     */
    public function index()
    {
        $tenants = Tenant::with('domains')->get();
        return view('admin.dashboard', compact('tenants'));
    }

    /**
     * Set the tenant context in session for analytics/tools.
     */
    public function setTenantContext(SetTenantContextRequest $request)
    {
        $validated = $request->validated();

        session(['admin_tenant_id' => $validated['tenant_id']]);

        return redirect()->back()->with('success', 'Tenant context set successfully.');
    }

    /**
     * Clear the tenant context.
     */
    public function clearTenantContext()
    {
        session()->forget('admin_tenant_id');

        return redirect()->back()->with('success', 'Tenant context cleared.');
    }
}
