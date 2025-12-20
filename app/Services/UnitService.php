<?php

namespace App\Services;

use App\Models\Unit;
use App\Repositories\UnitRepository;
use Exception;

class UnitService
{
    public function __construct(protected UnitRepository $unitRepository) {}

    public function delete(Unit $unit, bool $force = false)
    {
        if ($force) {
            if ($unit->items()->exists()) {
                throw new Exception('Unit is assigned to items and cannot be hard deleted.');
            }
            return $this->unitRepository->forceDelete($unit);
        }

        return $this->unitRepository->delete($unit);
    }

    public function restore(int $id): Unit
    {
        $unit = Unit::onlyTrashed()->findOrFail($id);
        $this->unitRepository->restore($unit);

        return $unit;
    }
}
