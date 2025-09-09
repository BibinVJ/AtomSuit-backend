<?php

namespace App\Jobs;

use App\Enums\OtpPurposeEnum;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        protected User|string|null $recipient,
        protected string $otp,
        protected OtpPurposeEnum $purpose
    ) {}

    public function handle(): void
    {
        if ($this->recipient instanceof User) {
            // Email
            if ($this->recipient->email) {
                Mail::to($this->recipient->email)
                    ->send(new OtpMail($this->otp, $this->purpose));
            }

            // SMS
            if ($this->recipient->mobile) {
                \Log::info("Sending SMS to {$this->recipient->mobile}: OTP = {$this->otp}");
            }
        } elseif (is_string($this->recipient)) {
            // Treat string as identifier (like phone number or email)
            if (filter_var($this->recipient, FILTER_VALIDATE_EMAIL)) {
                Mail::to($this->recipient)
                    ->send(new OtpMail($this->otp, $this->purpose));
            } else {
                \Log::info("Sending SMS to {$this->recipient}: OTP = {$this->otp}");
            }
        }
    }
}
