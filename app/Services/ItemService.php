<?php

namespace App\Services;

use App\Models\Item;
use Exception;

class ItemService
{
    public function ensureItemIsDeletable(Item $item)
    {
        // chekc for batch table and stock movements
        // if ($item->items()->exists()) {
        //     throw new Exception('Item is assigned to items and cannot be deleted.');
        // }
    }
}
