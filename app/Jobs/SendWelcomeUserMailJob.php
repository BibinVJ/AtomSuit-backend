<?php

namespace App\Jobs;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendWelcomeUserMailJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        protected User $user,
        protected string $rawPassword = ''
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new WelcomeUserMail($this->user, $this->rawPassword));
    }

    public function __destruct()
    {
        $this->rawPassword = '';
    }
}
