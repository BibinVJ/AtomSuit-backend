<?php

namespace App\Repositories;

use App\Models\Sale;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class SaleRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Sale();
    }

    public function sanitizeSaleItem(array $item): array
    {
        return collect($item)->only((new \App\Models\SaleItem)->getFillable())->toArray();
    }

    public function addItems(Sale $sale, array $items): void
    {
        $sale->items()->createMany($items);
    }

    public function addItem(Sale $sale, array $item): void
    {
        $sale->items()->create($item);
    }

    public function syncItems(Sale $sale, array $items): void
    {
        // Get item IDs
        $existingIds = $sale->items()->pluck('id')->toArray();
        $incomingIds = collect($items)->pluck('id')->filter()->toArray();

        // Delete removed items
        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            $sale->items()->whereIn('id', $toDelete)->delete();
        }

        // Upsert or create each item
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $sale->items()->where('id', $item['id'])->update($this->sanitizeSaleItem($item));
            } else {
                $sale->items()->create($item);
            }
        }
    }
}
