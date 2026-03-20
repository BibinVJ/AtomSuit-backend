<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // Core modules
            ['slug' => 'pos', 'name' => 'Point of Sale', 'type' => 'module', 'description' => 'POS devices, offline sales, and sync'],
            ['slug' => 'restaurant', 'name' => 'Restaurant', 'type' => 'module', 'description' => 'Menu items, modifiers, dining areas, kitchen orders'],
            ['slug' => 'retail', 'name' => 'Retail', 'type' => 'module', 'description' => 'Barcoding, packing, label printing, department store features'],
            ['slug' => 'pharmacy', 'name' => 'Pharmacy', 'type' => 'module', 'description' => 'Prescription management, drug inventory, expiry tracking'],
            ['slug' => 'grocery', 'name' => 'Grocery', 'type' => 'module', 'description' => 'Grocery and department store management'],

            // Integrations
            ['slug' => 'shopify', 'name' => 'Shopify', 'type' => 'integration', 'description' => 'Sync products and orders with Shopify'],
            ['slug' => 'google-analytics', 'name' => 'Google Analytics', 'type' => 'integration', 'description' => 'Track sales and user behavior analytics'],
            ['slug' => 'mailchimp', 'name' => 'Mailchimp', 'type' => 'integration', 'description' => 'Email marketing and customer engagement'],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }
    }
}
