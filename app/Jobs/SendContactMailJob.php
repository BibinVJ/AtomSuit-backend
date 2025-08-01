<?php

namespace App\Jobs;

use App\Mail\ContactFormMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContactMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $toEmail;

    protected array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, string $toEmail)
    {
        $this->toEmail = $toEmail;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->toEmail)->send(new ContactFormMail($this->data));
    }
}
