<?php

namespace App\Actions;

use App\Jobs\SendUserMailJob;
use App\Models\User;

class SendUserMailAction
{
    public function execute(User $user, string $subject, string $body): void
    {
        dispatch(new SendUserMailJob($user, $subject, $body));
    }
}
