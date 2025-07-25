<?php

namespace App\Services;

use App\Models\Unit;
use App\Repositories\UnitRepository;
use Exception;

class UnitService
{
    public function __construct(protected UnitRepository $unitRepository) {}

    public function delete(Unit $unit)
    {
        if ($unit->items()->exists()) {
            throw new Exception('Unit is assigned to items and cannot be deleted.');
        }

        $this->unitRepository->delete($unit);
    }
}
