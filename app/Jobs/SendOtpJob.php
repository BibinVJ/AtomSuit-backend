<?php

namespace App\Jobs;

use App\Enums\OtpChannelEnum;
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

    /**
     * @param  array<OtpChannelEnum>  $channels
     */
    public function __construct(
        protected User|string|null $recipient,
        protected string $otp,
        protected OtpPurposeEnum $purpose,
        protected array $channels
    ) {}

    public function handle(): void
    {
        foreach ($this->channels as $channel) {
            match ($channel) {
                OtpChannelEnum::EMAIL => $this->sendEmail(),
                OtpChannelEnum::SMS => $this->sendSms(),
            };
        }
    }

    protected function sendEmail(): void
    {
        $email = $this->recipient instanceof User ? $this->recipient->email : $this->recipient;
        if ($email) {
            Mail::to($email)->send(new OtpMail($this->otp, $this->purpose));
        }
    }

    protected function sendSms(): void
    {
        $phone = $this->recipient instanceof User ? $this->recipient->mobile : $this->recipient;
        if ($phone) {
            \Log::info("Sending SMS to {$phone}: OTP = {$this->otp}");
            // TODO: Integrate SMS provider (Twilio, Msg91, AWS SNS, etc.)
        }
    }
}
