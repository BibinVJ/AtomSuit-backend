<?php

namespace App\Repositories;

use App\Models\Unit;

class UnitRepository
{
    public function all(bool $paginate = false, int $perPage = 15, array $filters = [])
    {
        $query = Unit::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        $query->orderBy('name');

        return $paginate
            ? $query->paginate($perPage)
            : $query->get();
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
