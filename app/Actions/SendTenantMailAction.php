<?php

namespace App\Actions;

use App\Jobs\SendTenantMailJob;
use App\Models\Tenant;

class SendTenantMailAction
{
    public function execute(Tenant $tenant, string $subject, string $body): void
    {
        dispatch(new SendTenantMailJob($tenant, $subject, $body));
    }
}
