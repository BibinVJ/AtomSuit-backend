<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'Vendor One',
                'email' => 'vendor.one@example.com',
                'phone' => '1234567890',
                'address' => '123 Vendor St',
                'is_active' => true,
            ],
            [
                'name' => 'Vendor Two',
                'email' => 'vendor.two@example.com',
                'phone' => '9876543210',
                'address' => '456 Vendor Ave',
                'is_active' => true,
            ]
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(['email' => $vendor['email']], $vendor);
        }
    }
}
