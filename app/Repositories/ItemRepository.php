<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository
{
    public function all($paginate = false, $perPage = 15)
    {
        return $paginate ? Item::with(['category', 'unit'])->paginate($perPage) : Item::with(['category', 'unit'])->get();
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
