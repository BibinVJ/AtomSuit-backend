<?php

namespace App\Actions\Sales;

use App\Models\DeliveryNoteItem;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Repositories\StockMovementRepository;
use Illuminate\Validation\ValidationException;

class ValidateDnQuantities
{
    public function __construct(
        protected StockMovementRepository $stockMovementRepository
    ) {}

    public function validate(?SalesOrder $so, array $items, ?int $warehouseId = null): void
    {
        $allowNegative = Setting::getValue('allow_negative_inventory', false);
        if (! $so) {
            return;
        }

        foreach ($items as $itemData) {
            if (! isset($itemData['sales_order_item_id'])) {
                continue;
            }

            $soItem = $so->items()->find($itemData['sales_order_item_id']);

            if (! $soItem) {
                throw ValidationException::withMessages(['items' => "Invalid Sales Order Item ID: {$itemData['sales_order_item_id']}"]);
            }

            // Calculate how much has already been dispatched
            $previouslyDispatched = DeliveryNoteItem::where('sales_order_item_id', $soItem->id)
                ->sum('dispatched_quantity');

            $totalRequested = $previouslyDispatched + $itemData['dispatched_quantity'];

            if ($totalRequested > $soItem->quantity) {
                throw ValidationException::withMessages([
                    'items' => "Cannot dispatch {$itemData['dispatched_quantity']} for item {$soItem->item->name}. Only ".max(0, $soItem->quantity - $previouslyDispatched).' remaining on order.',
                ]);
            }

            // 2. Physical Stock Validation (if negative inventory is disabled)
            if (! $allowNegative && $warehouseId) {
                $availableStock = $this->stockMovementRepository->totalByItemAndWarehouse($itemData['item_id'], $warehouseId);

                if ($itemData['dispatched_quantity'] > $availableStock) {
                    $itemName = $soItem?->item?->name ?? "Item ID: {$itemData['item_id']}";
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$itemName} in the selected warehouse. Available: {$availableStock}, Requested: {$itemData['dispatched_quantity']}.",
                    ]);
                }
            }
        }
    }
}
