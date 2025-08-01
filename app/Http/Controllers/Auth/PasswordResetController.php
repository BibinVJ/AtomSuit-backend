<?php

namespace App\Http\Controllers\Auth;

use App\Enums\OtpPurposeEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendResetOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Repositories\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\OtpService;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected UserRepository $userRepository,
        protected OtpService $otpService
    ) {}

    /**
     * Step 1: Send OTP to user’s email and mobile
     */
    public function sendPasswordResetOtp(SendResetOtpRequest $request)
    {
        $user = $this->userRepository->findByEmail($request->validated()['email']);
        $this->otpService->sendPasswordResetOtp($user, OtpPurposeEnum::PASSWORD_RESET);

        return ApiResponse::success('OTP sent to your email and mobile.');
    }

    /**
     * Step 2: Verify OTP (optional pre-check)
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $user = $this->userRepository->findByEmail($request->validated()['email']);

        if (! $this->otpService->verify($user, OtpPurposeEnum::PASSWORD_RESET, $request->validated()['otp'])) {
            return ApiResponse::error('Invalid or expired OTP.', [], Response::HTTP_BAD_REQUEST);
        }

        return ApiResponse::success('OTP verified successfully.');
    }

    /**
     * Step 3: Final reset password after verifying OTP
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = $this->userRepository->findByEmail($request->validated()['email']);

        if (! $this->otpService->verify($user, OtpPurposeEnum::PASSWORD_RESET, $request->validated()['otp'])) {
            return ApiResponse::error('Invalid or expired OTP.', [], Response::HTTP_BAD_REQUEST);
        }

        $this->authService->resetUserPassword($user, $request->validated()['new_password']);

        return ApiResponse::success('Password reset successfully.');
    }
}
