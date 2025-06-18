<?php

namespace App\Services;

use App\Models\Unit;
use Exception;

class UnitService
{
    public function ensureUnitIsDeletable(Unit $unit)
    {
        if ($unit->items()->exists()) {
            throw new Exception('Unit is assigned to items and cannot be deleted.');
        }
    }
}
