<?php

namespace App\Services;

use App\Repositories\ItemRepository;
use Exception;

class ItemService extends BaseService
{
    public function __construct(protected ItemRepository $itemRepository)
    {
        $this->repository = $itemRepository;
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $item): void
    {
        /** @var \App\Models\Item $item */
        if ($item->batches()->exists()) {
            throw new Exception('Cannot hard delete: Item has related batches.');
        }

        if ($item->saleItems()->exists()) {
            throw new Exception('Cannot hard delete: Item has related sales.');
        }

        if ($item->stockMovements()->exists()) {
            throw new Exception('Cannot hard delete: Item has related stock movements.');
        }
    }
}
