<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();
        if (! $customer) {
            $this->command->info('No customers found. Please seed customers first.');

            return;
        }

        $items = Item::all();

        if ($items->count() < 3) {
            $this->command->info('Not enough items to seed a sale. Please seed purchases first.');

            return;
        }

        $sale = Sale::updateOrCreate(
            ['invoice_number' => 'S-INV-000001'],
            [
                'customer_id' => $customer->id,
                'sale_date' => Carbon::now(),
                'payment_status' => PaymentStatus::PENDING,
            ]
        );

        foreach ($items->take(3) as $item) {
            /** @var \App\Models\Batch $batch */
            $batch = $item->batches
                ->filter(fn (\App\Models\Batch $batch) => $batch->stockOnHand() > 0)
                ->first();

            if (! $batch || $batch->stockOnHand() <= 0) {
                $this->command->info("No stock available for item: {$item->name}. Skipping.");

                continue;
            }

            $quantityToSell = min(10, $batch->stockOnHand());

            $saleItem = SaleItem::updateOrCreate(
                [
                    'sale_id' => $sale->id,
                    'item_id' => $item->id,
                ],
                [
                    'quantity' => $quantityToSell,
                    'unit_price' => 20.00,
                ]
            );

            StockMovement::updateOrCreate(
                [
                    'source_type' => Sale::class,
                    'source_id' => $sale->id,
                    'item_id' => $item->id,
                    'batch_id' => $batch->id,
                ],
                [
                    'quantity' => -$quantityToSell,
                    'transaction_date' => $sale->sale_date,
                ]
            );
        }
    }
}
