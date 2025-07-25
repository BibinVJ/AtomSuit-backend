<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\Batch;
use App\Enums\PaymentStatus;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = Vendor::first();
        if (!$vendor) {
            $this->command->info('No vendors found. Please seed vendors first.');
            return;
        }

        $items = Item::all();
        if ($items->count() < 5) {
            $this->command->info('Not enough items found. Please seed items first.');
            return;
        }

        // Purchase 1
        $purchase1 = Purchase::create([
            'vendor_id' => $vendor->id,
            'invoice_number' => 'P-INV-000001',
            'purchase_date' => Carbon::now()->subDays(10),
            'payment_status' => PaymentStatus::PAID,
        ]);

        $purchase1Items = $items->take(2);

        foreach ($purchase1Items as $item) {
            $batch = Batch::create([
                'item_id' => $item->id,
                'batch_number' => 'B' . $item->id . 'P1',
                'manufacture_date' => Carbon::now()->subMonths(6),
                'expiry_date' => Carbon::now()->addMonths(6),
                'cost_price' => 10.00,
            ]);

            $purchaseItem = PurchaseItem::create([
                'purchase_id' => $purchase1->id,
                'item_id' => $item->id,
                'batch_id' => $batch->id,
                'quantity' => 100,
                'unit_cost' => 10.00,
            ]);

            StockMovement::create([
                'item_id' => $item->id,
                'batch_id' => $batch->id,
                'source_type' => Purchase::class,
                'source_id' => $purchase1->id,
                'quantity' => $purchaseItem->quantity,
                'transaction_date' => $purchase1->purchase_date,
            ]);
        }

        // Purchase 2
        $purchase2 = Purchase::create([
            'vendor_id' => $vendor->id,
            'invoice_number' => 'P-INV-000002',
            'purchase_date' => Carbon::now()->subDays(5),
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $purchase2Items = $items->skip(2)->take(3);

        foreach ($purchase2Items as $item) {
            $batch = Batch::create([
                'item_id' => $item->id,
                'batch_number' => 'B' . $item->id . 'P2',
                'manufacture_date' => Carbon::now()->subMonths(4),
                'expiry_date' => Carbon::now()->addMonths(8),
                'cost_price' => 12.00,
            ]);

            $purchaseItem = PurchaseItem::create([
                'purchase_id' => $purchase2->id,
                'item_id' => $item->id,
                'batch_id' => $batch->id,
                'quantity' => 50,
                'unit_cost' => 12.00,
            ]);

            StockMovement::create([
                'item_id' => $item->id,
                'batch_id' => $batch->id,
                'source_type' => Purchase::class,
                'source_id' => $purchase2->id,
                'quantity' => $purchaseItem->quantity,
                'transaction_date' => $purchase2->purchase_date,
            ]);
        }
    }
}