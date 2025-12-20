<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inr = Currency::where('code', 'INR')->first();
        $usd = Currency::where('code', 'USD')->first();

        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '1234567890',
                'address' => '123 Main St',
                'currency_id' => $inr?->id,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '0987654321',
                'address' => '456 Elm St',
                'currency_id' => $usd?->id,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['email' => $customer['email']], $customer);
        }
    }
}
