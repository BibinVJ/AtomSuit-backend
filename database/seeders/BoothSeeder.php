<?php

namespace Database\Seeders;

use App\Models\Booth;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoothSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Booth::create([
            'unique_id'  => 'A-123',
            'name'       => 'Premium Corner Booth',
            'description' => 'Located near the main entrance with maximum visibility.',
            'image'      => 'booths/premium-corner.jpg',
            'size'       => '4x4 meters',
            'price'      => 500.00,
            'status'     => 'available',
            'is_active'  => true,
        ]);

        Booth::create([
            'unique_id'  => 'B-425',
            'name'       => 'Standard Inline Booth',
            'description' => 'Standard booth in the center row.',
            'image'      => 'booths/standard-inline.jpg',
            'size'       => '3x3 meters',
            'price'      => 300.46,
            'status'     => 'available',
            'is_active'  => true,
        ]);
    }
}
