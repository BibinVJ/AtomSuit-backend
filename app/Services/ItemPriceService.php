<?php

namespace App\Services;

use App\Models\ItemPrice;
use App\Repositories\ItemPriceRepository;

class ItemPriceService extends BaseService
{
    public function __construct(
        protected readonly ItemPriceRepository $itemPriceRepository
    ) {
        $this->repository = $itemPriceRepository;
    }

    public function create(array $data): ItemPrice
    {
        return ItemPrice::create($data);
    }

    public function update(ItemPrice $itemPrice, array $data): ItemPrice
    {
        $itemPrice->update($data);

        return $itemPrice;
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $model): void
    {
        // Generally safe to delete item prices unless we want to enforce audit trails strictly.
        // If used in sales/purchases, those usually store the frozen price, so it might be okay.
        // For now, no strict validation needed, but method must exist if we want to support force delete logic cleanly.

    }
}
