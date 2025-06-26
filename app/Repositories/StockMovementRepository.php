<?php

namespace App\Repositories;

use App\Models\StockMovement;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Support\Collection;

class StockMovementRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new StockMovement();
    }

    public function getAvailableStockByItemFifo(int $itemId): Collection
    {
        return StockMovement::selectRaw('batch_id, item_id, SUM(quantity) as available_qty')
            ->where('item_id', $itemId)
            ->where('quantity', '>', 0)
            ->groupBy('batch_id', 'item_id')
            ->havingRaw('available_qty > 0')
            ->orderBy('transaction_date')
            ->get();
    }

    // public function getFifoAvailableStock(array $filters = []): Collection
    // {
    //     return StockMovement::selectRaw('batch_id, item_id, SUM(quantity) as available_qty')
    //         ->when(isset($filters['item_id']), fn($q) => $q->where('item_id', $filters['item_id']))
    //         ->when(isset($filters['warehouse_id']), fn($q) => $q->where('warehouse_id', $filters['warehouse_id']))
    //         ->groupBy('batch_id', 'item_id')
    //         ->havingRaw('available_qty > 0')
    //         ->orderBy('transaction_date')
    //         ->get();
    // }

    public function getFifoAvailableStock(array $filters = []): Collection
    {
        return StockMovement::query()
            ->where('quantity', '>', 0)
            ->when(isset($filters['item_id']), fn($q) => $q->where('item_id', $filters['item_id']))
            ->when(isset($filters['warehouse_id']), fn($q) => $q->where('warehouse_id', $filters['warehouse_id']))
            ->orderBy('transaction_date') // FIFO style
            ->get()
            ->groupBy(fn($movement) => $movement->batch_id . '_' . $movement->item_id)
            ->map(function ($group) {
                $totalQty = $group->sum('quantity');
                if ($totalQty <= 0) return null;

                $first = $group->first();

                return (object) [
                    'batch_id'       => $first->batch_id,
                    'item_id'        => $first->item_id,
                    'available_qty'  => $totalQty,
                    'transaction_date' => $first->transaction_date,
                ];
            })
            ->filter()
            ->values()
            ->sortBy('transaction_date')
            ->values();
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
