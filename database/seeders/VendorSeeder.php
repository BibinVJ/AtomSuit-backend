<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inr = Currency::where('code', 'INR')->first();
        $usd = Currency::where('code', 'USD')->first();

        $vendors = [
            [
                'name' => 'Vendor One',
                'email' => 'vendor.one@example.com',
                'phone' => '1234567890',
                'address' => '123 Vendor St',
                'currency_id' => $inr?->id,
            ],
            [
                'name' => 'Vendor Two',
                'email' => 'vendor.two@example.com',
                'phone' => '9876543210',
                'address' => '456 Vendor Ave',
                'currency_id' => $usd?->id,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(['email' => $vendor['email']], $vendor);
        }
    }
}
