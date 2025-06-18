<?php

namespace App\Repositories;

use App\Models\Unit;

class UnitRepository
{
    public function all($paginate = false, $perPage = 15)
    {
        if ($paginate) {
            return Unit::paginate($perPage);
        }

        return Unit::all();
    }

    public function create(array $data): Unit
    {
        return Unit::create($data)->refresh();
    }

    public function update(Unit $unit, array $data): bool
    {
        return $unit->update($data);
    }

    public function delete(Unit $unit): ?bool
    {
        return $unit->delete();
    }
}
