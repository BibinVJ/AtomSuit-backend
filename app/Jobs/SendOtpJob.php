<?php

namespace App\Jobs;

use App\Enums\OtpPurposeEnum;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOtpJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected User $user,
        protected string $otp,
        protected OtpPurposeEnum $purpose
    ) {}

    public function handle(): void
    {
        // Email
        Mail::to($this->user->email)->send(new OtpMail($this->otp, $this->purpose));

        // SMS (dummy example)
        if ($this->user->phone) {
            // Integrate your real SMS provider here
            \Log::info("Sending SMS to {$this->user->phone}: OTP = {$this->otp}");
        }
    }
}
