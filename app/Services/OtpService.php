<?php

namespace App\Services;

use App\Enums\OtpPurposeEnum;
use App\Exceptions\OtpVerificationException;
use App\Jobs\SendOtpJob;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Either user_id or identifier can be used to generate and verify otp
 */
class OtpService
{
    public function generate(User|string|null $recipient, OtpPurposeEnum $purpose, int $validForMinutes = 10): string
    {
        $isUser = $recipient instanceof User;
        $identifier = $isUser ? null : $recipient;

        $query = Otp::query()
            ->when($isUser, fn($q) => $q->where('user_id', $recipient->id))
            ->when(!$isUser, fn($q) => $q->where('identifier', $identifier))
            ->where('purpose', $purpose->value);

        $latestOtp = $query->latest()->first();

        if ($latestOtp && $latestOtp->created_at->diffInSeconds(now()) < 60) {
            throw new OtpVerificationException('Please wait before requesting another OTP.');
        }

        $otp = (string) rand(100000, 999999);
        $expiresAt = now()->addMinutes($validForMinutes);

        // cleanup old
        $query->delete();

        // store new
        Otp::create([
            'user_id'    => $isUser ? $recipient->id : null,
            'identifier' => $isUser ? null : $identifier,
            'otp'        => Hash::make($otp),
            'purpose'    => $purpose->value,
            'expires_at' => $expiresAt,
        ]);

        // Dispatch notification (Job handles SMS/email)
        dispatch(new SendOtpJob($recipient, $otp, $purpose));

        return $otp;
    }

    public function verify(User|string|null $recipient, OtpPurposeEnum $purpose, string $inputOtp): bool
    {
        $isUser = $recipient instanceof User;
        $identifier = $isUser ? null : $recipient;

        $record = Otp::query()
            ->when($isUser, fn($q) => $q->where('user_id', $recipient->id))
            ->when(!$isUser, fn($q) => $q->where('identifier', $identifier))
            ->where('purpose', $purpose->value)
            ->latest()
            ->first();

        if (! $record || $record->verified_at) {
            throw new OtpVerificationException('No valid OTP found');
        }

        if ($record->expires_at->isPast()) {
            throw new OtpVerificationException('OTP has expired');
        }

        if (! Hash::check($inputOtp, $record->otp)) {
            throw new OtpVerificationException('OTP is incorrect');
        }

        $record->update(['verified_at' => now()]);

        return true;
    }
}
