<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Enums\RolesEnum;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::role(RolesEnum::ADMIN->value)->first();
        if (! $user) {
            $this->command->info('No users found. Please seed users first.');

            return;
        }

        $vendor = Vendor::first();
        if (! $vendor) {
            $this->command->info('No vendors found. Please seed vendors first.');

            return;
        }

        $items = Item::all();
        if ($items->count() < 5) {
            $this->command->info('Not enough items found. Please seed items first.');

            return;
        }

        // Purchase 1
        $purchase1 = Purchase::updateOrCreate(
            ['invoice_number' => 'P-INV-000001'],
            [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'purchase_date' => Carbon::now()->subDays(10),
                'payment_status' => PaymentStatus::PAID,
            ]
        );

        $purchase1Items = $items->take(2);

        foreach ($purchase1Items as $item) {
            $batch = Batch::updateOrCreate(
                ['batch_number' => 'B'.$item->id.'P1'],
                [
                    'item_id' => $item->id,
                    'manufacture_date' => Carbon::now()->subMonths(6),
                    'expiry_date' => Carbon::now()->addMonths(6),
                    'cost_price' => 10.00,
                ]
            );

            $purchaseItem = PurchaseItem::updateOrCreate(
                [
                    'purchase_id' => $purchase1->id,
                    'item_id' => $item->id,
                    'batch_id' => $batch->id,
                ],
                [
                    'quantity' => 100,
                    'unit_cost' => 10.00,
                ]
            );

            StockMovement::updateOrCreate(
                [
                    'source_type' => Purchase::class,
                    'source_id' => $purchase1->id,
                    'item_id' => $item->id,
                    'batch_id' => $batch->id,
                ],
                [
                    'quantity' => $purchaseItem->quantity,
                    'transaction_date' => $purchase1->purchase_date,
                ]
            );
        }

        // Purchase 2
        $purchase2 = Purchase::updateOrCreate(
            ['invoice_number' => 'P-INV-000002'],
            [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'purchase_date' => Carbon::now()->subDays(5),
                'payment_status' => PaymentStatus::PENDING,
            ]
        );

        $purchase2Items = $items->skip(2)->take(3);

        foreach ($purchase2Items as $item) {
            $batch = Batch::updateOrCreate(
                ['batch_number' => 'B'.$item->id.'P2'],
                [
                    'item_id' => $item->id,
                    'manufacture_date' => Carbon::now()->subMonths(4),
                    'expiry_date' => Carbon::now()->addMonths(8),
                    'cost_price' => 12.00,
                ]
            );

            $purchaseItem = PurchaseItem::updateOrCreate(
                [
                    'purchase_id' => $purchase2->id,
                    'item_id' => $item->id,
                    'batch_id' => $batch->id,
                ],
                [
                    'quantity' => 50,
                    'unit_cost' => 12.00,
                ]
            );

            StockMovement::updateOrCreate(
                [
                    'source_type' => Purchase::class,
                    'source_id' => $purchase2->id,
                    'item_id' => $item->id,
                    'batch_id' => $batch->id,
                ],
                [
                    'quantity' => $purchaseItem->quantity,
                    'transaction_date' => $purchase2->purchase_date,
                ]
            );
        }
    }
}
