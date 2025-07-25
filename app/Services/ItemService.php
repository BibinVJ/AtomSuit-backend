<?php

namespace App\Services;

use App\Models\Item;
use App\Repositories\ItemRepository;
use Exception;

class ItemService
{
    public function __construct(protected ItemRepository $itemRepository) {}

    public function delete(Item $item)
    {
        // chekc for batch table and stock movements
        // if ($item->items()->exists()) {
        //     throw new Exception('Item is assigned to items and cannot be deleted.');
        // }

        $this->itemRepository->delete($item);
    }
}
