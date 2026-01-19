<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Models\PriceList;

class PricingService
{
    // TODO: check if this is correctly fetching from the price list and implemetnt  it properly.
    /**
     * Get the price for an item in a specific context.
     *
     * Resolution Order:
     * 1. Customer specific Price List (if assigned)
     * 2. Default Price List for the target currency
     * 3. Item's master selling_price (fallback, if currencies match)
     */
    public function getPrice(Item $item, ?Customer $customer = null, ?Currency $currencyOverride = null, float $quantity = 1): float
    {
        // 1. Determine Target Currency
        $targetCurrencyId = $currencyOverride ? $currencyOverride->id : ($customer ? $customer->currency_id : setting('currency'));

        // If no currency context, default to system currency
        if (! $targetCurrencyId) {
            $targetCurrencyId = setting('currency');
        }

        // 2. Check Customer's Assigned Price List
        if ($customer && $customer->price_list_id) {
            $price = $this->getPriceFromList($customer->price_list_id, $item->id, $quantity);
            if ($price !== null) {
                return $price;
            }
        }

        // 3. Find a suitable Price List for the Target Currency
        // We look for a Price List that matches the currency and is either marked generic or we pick the first one.
        // In this implementation, we assume the first created list for a currency is the "Standard" one.
        $defaultList = PriceList::where('currency_id', $targetCurrencyId)
            ->where('type', 'sales')
            ->orderBy('id')
            ->first();

        if ($defaultList) {
            $price = $this->getPriceFromList($defaultList->id, $item->id, $quantity);
            if ($price !== null) {
                return $price;
            }
        }

        // 4. Fallback: No price found
        return 0.0;
    }

    /**
     * Helper to query item_prices table.
     */
    protected function getPriceFromList(int $priceListId, int $itemId, float $quantity)
    {
        $tier = ItemPrice::where('price_list_id', $priceListId)
            ->where('item_id', $itemId)
            ->where('min_quantity', '<=', $quantity)
            ->orderByDesc('min_quantity')
            ->first();

        return $tier ? $tier->price : null;
    }
}
