<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Models\DashboardCard;
use Illuminate\Database\Seeder;
use Spatie\Permission\Contracts\Permission;

class DashboardCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cards = [
            [
                'card_id' => 'total-sales',
                'title' => 'Total Sales',
                'component' => 'TotalSalesCard',
                'description' => 'Total sales amount',
                'permission' => PermissionsEnum::VIEW_SALE->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 0,
                'default_y' => 0,
                'default_order' => 1,
            ],
            [
                'card_id' => 'total-purchase',
                'title' => 'Total Purchase',
                'component' => 'TotalPurchaseCard',
                'description' => 'Total purchase amount',
                'permission' => PermissionsEnum::VIEW_PURCHASE->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 6,
                'default_y' => 0,
                'default_order' => 2,
            ],
            [
                'card_id' => 'total-customers',
                'title' => 'Total Customers',
                'component' => 'TotalCustomersCard',
                'description' => 'Total number of customers',
                'permission' => PermissionsEnum::VIEW_CUSTOMER->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 12,
                'default_y' => 0,
                'default_order' => 3,
            ],
            [
                'card_id' => 'total-items',
                'title' => 'Total Items',
                'component' => 'TotalItemsCard',
                'description' => 'Total number of items',
                'permission' => PermissionsEnum::VIEW_ITEM->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 18,
                'default_y' => 0,
                'default_order' => 4,
            ],
            [
                'card_id' => 'monthly-sales',
                'title' => 'Monthly Sales Chart',
                'component' => 'MonthlySalesChart',
                'description' => 'Sales trend over time',
                'permission' => PermissionsEnum::VIEW_SALE->value,
                'default_width' => 12,
                'default_height' => 8,
                'default_x' => 0,
                'default_y' => 4,
                'default_order' => 5,
            ],
            [
                'card_id' => 'statistics',
                'title' => 'Statistics',
                'component' => 'StatisticsCard',
                'description' => 'Overall statistics',
                'permission' => PermissionsEnum::VIEW_DASHBOARD->value,
                'default_width' => 12,
                'default_height' => 10,
                'default_x' => 12,
                'default_y' => 4,
                'default_order' => 6,
            ],
            [
                'card_id' => 'top-customers',
                'title' => 'Top Customers',
                'component' => 'TopCustomersCard',
                'description' => 'Best customers by revenue',
                'permission' => PermissionsEnum::VIEW_CUSTOMER->value,
                'default_width' => 12,
                'default_height' => 10,
                'default_x' => 0,
                'default_y' => 12,
                'default_order' => 7,
            ],
            [
                'card_id' => 'out-of-stock',
                'title' => 'Out of Stock',
                'component' => 'OutOfStockCard',
                'description' => 'Items that are out of stock',
                'permission' => PermissionsEnum::VIEW_ITEM->value,
                'default_width' => 12,
                'default_height' => 8,
                'default_x' => 12,
                'default_y' => 14,
                'default_order' => 8,
            ],
            [
                'card_id' => 'low-stock',
                'title' => 'Low Stock',
                'component' => 'LowStockCard',
                'description' => 'Items with low stock levels',
                'permission' => PermissionsEnum::VIEW_ITEM->value,
                'default_width' => 12,
                'default_height' => 8,
                'default_x' => 0,
                'default_y' => 22,
                'default_order' => 9,
            ],
            [
                'card_id' => 'expiry-items',
                'title' => 'Expiring Items',
                'component' => 'ExpiringItemsCard',
                'description' => 'Items nearing expiry date',
                'permission' => PermissionsEnum::VIEW_ITEM->value,
                'default_width' => 12,
                'default_height' => 8,
                'default_x' => 12,
                'default_y' => 22,
                'default_order' => 10,
            ],
            [
                'card_id' => 'top-sold',
                'title' => 'Top Selling Items',
                'component' => 'TopSoldCard',
                'description' => 'Best selling items',
                'permission' => PermissionsEnum::VIEW_SALE->value,
                'default_width' => 12,
                'default_height' => 8,
                'default_x' => 0,
                'default_y' => 30,
                'default_order' => 11,
            ],
            [
                'card_id' => 'top-purchased',
                'title' => 'Top Purchased Items',
                'component' => 'TopPurchasedCard',
                'description' => 'Most purchased items',
                'permission' => PermissionsEnum::VIEW_PURCHASE->value,
                'default_width' => 12,
                'default_height' => 8,
                'default_x' => 12,
                'default_y' => 30,
                'default_order' => 12,
            ],
            [
                'card_id' => 'dead-stock',
                'title' => 'Dead Stock',
                'component' => 'DeadStockCard',
                'description' => 'Items with no movement',
                'permission' => PermissionsEnum::VIEW_ITEM->value,
                'default_width' => 12,
                'default_height' => 8,
                'default_x' => 0,
                'default_y' => 38,
                'default_order' => 13,
            ],
        ];

        foreach ($cards as $card) {
            DashboardCard::updateOrCreate(
                ['card_id' => $card['card_id']],
                $card
            );
        }

        $this->command->info('✓ Dashboard cards seeded successfully!');
    }
}
