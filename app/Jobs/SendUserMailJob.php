<?php

namespace App\Jobs;

use App\Mail\UserMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendUserMailJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        protected User $user,
        protected string $subject,
        protected string $body
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new UserMail($this->subject, $this->body));
    }
}
