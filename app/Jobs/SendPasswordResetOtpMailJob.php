<?php

namespace App\Jobs;

use App\Mail\PasswordResetOtpMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendPasswordResetOtpMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $toMail;
    protected string $otp;

    /**
     * Create a new job instance.
     */
    public function __construct(string $toMail, string $otp)
    {
        $this->toMail = $toMail;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->toMail)->send(new PasswordResetOtpMail($this->otp));
    }
}
