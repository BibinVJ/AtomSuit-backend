<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Customer;
use App\Models\Item;
use App\Enums\PaymentStatus;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Customer::first();
        if (!$customer) {
            $this->command->info('No customers found. Please seed customers first.');
            return;
        }

        $items = Item::all();

        if ($items->count() < 3) {
            $this->command->info('Not enough items to seed a sale. Please run the PurchaseSeeder first.');
            return;
        }

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'S-INV-000001',
            'sale_date' => Carbon::now(),
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $saleItems = $items->take(3);

        foreach ($saleItems as $item) {
            $batch = $item->batches()->where('id', '>', 0)->first();

            if (!$batch) {
                $this->command->info("No stock available for item: {$item->name}. Skipping sale item.");
                continue;
            }

            $quantityToSell = 10;
            if ($batch->stockOnHand() < $quantityToSell) {
                $quantityToSell = $batch->stockOnHand();
            }

            if ($quantityToSell <= 0) {
                $this->command->info("No stock available for item: {$item->name}. Skipping sale item.");
                continue;
            }

            SaleItem::create([
                'sale_id' => $sale->id,
                'item_id' => $item->id,
                'quantity' => $quantityToSell,
                'unit_price' => 20.00,
            ]);

            StockMovement::create([
                'item_id' => $item->id,
                'batch_id' => $batch->id,
                'source_type' => Sale::class,
                'source_id' => $sale->id,
                'quantity' => -$quantityToSell,
                'transaction_date' => $sale->sale_date,
            ]);
        }
    }
}
