<?php

namespace App\Http\Controllers\Auth;


use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendResetOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetTokenController extends Controller
{
    public function __construct(protected AuthService $authService) {}


    /**
     * Send reset OTP to user email.
     */
    public function sendResetOtp(SendResetOtpRequest $request)
    {
        $this->authService->sendResetOtp($request->validated()['email']);

        return ApiResponse::success('OTP sent to your email.');
    }

    /**
     * Verify reset OTP.
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $validatedData = $request->validated();
        if (!$this->authService->verifyOtp($validatedData['email'], $validatedData['otp'])) {
            return ApiResponse::error('Invalid or expired OTP.', [], Response::HTTP_BAD_REQUEST);
        }

        return ApiResponse::success('OTP verified.');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validatedData = $request->validated();

        // if (!$this->authService->verifyOtp($validatedData['email'], $validatedData['otp'])) {
        //     return ApiResponse::error('Invalid or expired OTP.', [], Response::HTTP_BAD_REQUEST);
        // }

        $this->authService->resetPassword($validatedData['email'], $validatedData['new_password'], $validatedData['otp']);

        return ApiResponse::success('Password reset successful.');
    }

}
