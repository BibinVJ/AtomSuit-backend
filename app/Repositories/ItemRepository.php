<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository
{
    public function all(bool $paginate = false, int $perPage = 15, array $filters = [])
    {
        $query = Item::with(['category', 'unit', 'salesAccount', 'cogsAccount', 'inventoryAccount', 'inventoryAdjustmentAccount']);

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('sku', 'like', '%' . $filters['search'] . '%');
                    // ->orWhereHas('category', fn($catQ) =>
                    //     $catQ->where('name', 'like', '%' . $filters['search'] . '%'))
                    // ->orWhereHas('unit', fn($unitQ) =>
                    //     $unitQ->where('name', 'like', '%' . $filters['search'] . '%'));
            });
        }

        $query->orderBy('name');

        return $paginate
            ? $query->paginate($perPage)
            : $query->get();
    }

    public function create(array $data): Item
    {
        return Item::create($data)->refresh();
    }

    public function update(Item $item, array $data): bool
    {
        return $item->update($data);
    }

    public function delete(Item $item): ?bool
    {
        return $item->delete();
    }
}
