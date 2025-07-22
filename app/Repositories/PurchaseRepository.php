<?php

namespace App\Repositories;

use App\Models\Purchase;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class PurchaseRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Purchase();
    }

    public function sanitizePurchaseItem(array $item): array
    {
        return collect($item)->only((new \App\Models\PurchaseItem)->getFillable())->toArray();
    }

    public function addItems(Purchase $purchase, array $items): void
    {
        $purchase->items()->createMany($items);
    }

    public function addItem(Purchase $purchase, array $item): void
    {
        $purchase->items()->create($item);
    }

    /**
     * $purchase - existing purchase
     * $items - new items to be synced.
     * todo: move this to service/action/repo as required
     */
    public function syncItems(Purchase $purchase, array $items): void
    {
        // Get item IDs
        $existingIds = $purchase->items()->pluck('id')->toArray();
        $incomingIds = collect($items)->pluck('id')->filter()->toArray();

        // Delete removed items
        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            $purchase->items()->whereIn('id', $toDelete)->delete();
        }

        // Upsert or create each item
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $purchase->items()->where('id', $item['id'])->update($this->sanitizePurchaseItem($item));
            } else {
                $purchase->items()->create($item);
            }
        }
    }
}
