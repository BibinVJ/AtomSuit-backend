<?php

namespace App\Http\Controllers\Auth;


use App\Helpers\ApiResponse;
use App\Helpers\AuthResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Register a new user and return token.
     */

    public function register(RegisterRequest $request)
    {
        $dto = $this->authService->register($request->validated());
        return ApiResponse::success('User registered successfully.', AuthResponseFormatter::format($dto));
    }

    /**
     * Login user and return token.
     */
    public function login(LoginRequest $request)
    {
        $dto = $this->authService->login(
            $request->validated()['email'],
            $request->validated()['password']
        );
        return ApiResponse::success('User authenticated successfully.', AuthResponseFormatter::format($dto));
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return ApiResponse::success('Logout successful.');
    }

    /**
     * Logout user from all devices.
     */
    public function logoutFromAllDevices(Request $request)
    {
        $this->authService->logout($request->user(), true);
        return ApiResponse::success('Logged out from all devices successfully.');
    }
}
