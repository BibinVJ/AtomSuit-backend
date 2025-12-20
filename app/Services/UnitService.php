<?php

namespace App\Services;

use App\Models\Unit;
use App\Repositories\UnitRepository;
use Exception;

class UnitService extends BaseService
{
    public function __construct(protected UnitRepository $unitRepository) {
        $this->repository = $unitRepository;
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $unit): void
    {
        /** @var \App\Models\Unit $unit */
        if ($unit->items()->exists()) {
            throw new Exception('Unit is assigned to items and cannot be hard deleted.');
        }
    }
}
