<?php

namespace App\Services;

use App\Jobs\SendContactMailJob;

class ContactService
{
    public function sendContactMail(array $data): void
    {
        $toEmail = $data['toEmail'] ?? config('mail.contact_recipient');
        dispatch(new SendContactMailJob($data, $toEmail));
    }
}
