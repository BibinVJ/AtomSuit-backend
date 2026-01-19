<?php

namespace App\Services;

use App\Models\CentralUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class ContextAwareService
{
    /**
     * Get the appropriate user model class
     */
    protected function getUserModel(): string
    {
        return tenant() ? User::class : CentralUser::class;
    }

    /**
     * Check if we're in central context
     */
    protected function isCentralContext(): bool
    {
        return tenant() === null;
    }

    /**
     * Check if we're in tenant context
     */
    protected function isTenantContext(): bool
    {
        return tenant() !== null;
    }
}
