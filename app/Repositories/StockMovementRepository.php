<?php

namespace App\Repositories;

use App\Models\StockMovement;

class StockMovementRepository
{
    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    public function totalByItem(int $itemId): int
    {
        return StockMovement::where('item_id', $itemId)->sum('quantity');
    }

    public function totalByBatch(int $batchId): int
    {
        return StockMovement::where('batch_id', $batchId)->sum('quantity');
    }
}
