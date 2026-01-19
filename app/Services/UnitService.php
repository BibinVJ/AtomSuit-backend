<?php

namespace App\Services;

use App\Repositories\UnitRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class UnitService extends BaseService
{
    public function __construct(protected UnitRepository $unitRepository)
    {
        $this->repository = $unitRepository;
    }

    protected function validateForceDelete(Model $unit): void
    {
        /** @var \App\Models\Unit $unit */
        if ($unit->items()->exists()) {
            throw new Exception('Unit is assigned to items and cannot be hard deleted.');
        }
    }
}
