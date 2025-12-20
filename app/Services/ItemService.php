<?php

namespace App\Services;

use App\Models\Item;
use App\Repositories\ItemRepository;
use Exception;

class ItemService
{
    public function __construct(protected ItemRepository $itemRepository) {}

    public function delete(Item $item, bool $force = false)
    {
        if ($force) {
            // Check for relations before hard delete
            if ($item->batches()->exists()) {
                throw new Exception('Cannot hard delete: Item has related batches.');
            }

            if ($item->saleItems()->exists()) {
                throw new Exception('Cannot hard delete: Item has related sales.');
            }

            if ($item->stockMovements()->exists()) {
                throw new Exception('Cannot hard delete: Item has related stock movements.');
            }

            return $this->itemRepository->forceDelete($item);
        }

        return $this->itemRepository->delete($item);
    }

    public function restore(int $id): Item
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $this->itemRepository->restore($item);

        return $item;
    }
}
