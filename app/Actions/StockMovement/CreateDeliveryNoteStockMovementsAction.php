<?php

namespace App\Actions\StockMovement;

use App\Models\Batch;
use App\Models\DeliveryNote;
use App\Repositories\StockMovementRepository;

class CreateDeliveryNoteStockMovementsAction
{
    public function __construct(protected StockMovementRepository $stockRepo) {}

    public function execute(DeliveryNote $dn): void
    {
        /** @var \App\Models\DeliveryNoteItem $item */
        foreach ($dn->items as $item) {
            $remainingToDeduct = $item->dispatched_quantity;

            /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\Batch> $batches */
            $batches = Batch::where('item_id', $item->item_id)
                ->whereHas('stockMovements') // Only batches with movements
                ->get();

            $batches = $batches->filter(fn ($b) => $b->stockOnHand() > 0)
                ->sortBy('created_at');

            /** @var \App\Models\Batch $batch */
            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                $batchStock = $batch->stockOnHand();
                $toDeduct = min($remainingToDeduct, $batchStock);

                $this->stockRepo->create([
                    'item_id' => $item->item_id,
                    'batch_id' => $batch->id,
                    'transaction_date' => $dn->dispatch_date ?? now(),
                    'warehouse_id' => $dn->warehouse_id,
                    'quantity' => -($toDeduct),
                    'rate' => $item->unit_price,
                    'standard_cost' => $item->unit_price,
                    'source_type' => DeliveryNote::class,
                    'source_id' => $dn->id,
                    'description' => "Goods dispatched from Batch: {$batch->batch_number}",
                    'reference' => $dn->dn_number,
                ]);

                $remainingToDeduct -= $toDeduct;
            }

            // Fallback: If no batches found or still remaining (allow negative inventory check happens in validator)
            if ($remainingToDeduct > 0) {
                $this->stockRepo->create([
                    'item_id' => $item->item_id,
                    'batch_id' => null,
                    'transaction_date' => $dn->dispatch_date ?? now(),
                    'warehouse_id' => $dn->warehouse_id,
                    'quantity' => -($remainingToDeduct),
                    'rate' => $item->unit_price,
                    'standard_cost' => $item->unit_price,
                    'source_type' => DeliveryNote::class,
                    'source_id' => $dn->id,
                    'description' => 'Goods dispatched (Global Pool / No Batch)',
                    'reference' => $dn->dn_number,
                ]);
            }
        }
    }
}
