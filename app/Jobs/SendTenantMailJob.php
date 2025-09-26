<?php

namespace App\Jobs;

use App\Mail\UserMail;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendTenantMailJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        protected Tenant $tenant,
        protected string $subject,
        protected string $body
    ) {}

    public function handle(): void
    {
        Mail::to($this->tenant->email)->send(new UserMail($this->subject, $this->body));
    }
}
