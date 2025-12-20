<?php

namespace App\Http\Controllers\Auth;

use App\Enums\PermissionsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SystemMonitoringLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemMonitoringAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(SystemMonitoringLoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            // Check if user has permission to access system analytics
            /** @var \App\Models\User $user */
            $user = Auth::guard('web')->user();
            if ($user->can(PermissionsEnum::VIEW_SYSTEM_ANALYTICS->value)) {
                return redirect()->route('admin.dashboard');
            }

            Auth::guard('web')->logout();

            return back()->withErrors([
                'email' => 'You do not have permission to access this area.',
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
