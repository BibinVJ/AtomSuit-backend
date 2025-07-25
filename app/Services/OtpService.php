<?php

namespace App\Services;

use App\Enums\OtpPurposeEnum;
use App\Exceptions\OtpVerificationException;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Jobs\SendOtpJob;

class OtpService
{
    public function generate(User $user, OtpPurposeEnum $purpose, int $validForMinutes = 10): string
    {
        $latestOtp = Otp::where('user_id', $user->id)
            ->where('purpose', $purpose->value)
            ->latest()
            ->first();

        if ($latestOtp && $latestOtp->created_at->diffInSeconds(now()) < 60) {
            throw new \Exception('Please wait before requesting another OTP.');
        }
        
        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes($validForMinutes);

        // Delete old OTPs for this user & purpose
        Otp::where('user_id', $user->id)->where('purpose', $purpose->value)->delete();

        Otp::create([
            'user_id'    => $user->id,
            'otp'        => Hash::make($otp),
            'purpose'    => $purpose->value,
            'expires_at' => $expiresAt,
        ]);

        return $otp;
    }

    public function sendPasswordResetOtp(User $user)
    {
        $otp = $this->generate($user, OtpPurposeEnum::PASSWORD_RESET);
        dispatch(new SendOtpJob($user, $otp, OtpPurposeEnum::PASSWORD_RESET));
    }

    public function verify(User $user, OtpPurposeEnum $purpose, string $inputOtp): bool
    {
        $record = Otp::where('user_id', $user->id)
            ->where('purpose', $purpose->value)
            ->latest()
            ->first();

        if (!$record || $record->verified_at) {
            throw new OtpVerificationException('No valid OTP found');
        }

        if (Carbon::parse($record->expires_at)->isPast()) {
            throw new OtpVerificationException('OTP has expired');
        }

        if (!Hash::check($inputOtp, $record->otp)) {
            throw new OtpVerificationException('OTP is incorrect');
        }

        $record->update(['verified_at' => now()]);

        return true;
    }
}
