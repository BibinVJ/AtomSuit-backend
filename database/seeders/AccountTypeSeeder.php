<?php

namespace Database\Seeders;

use App\Enums\AccountTypeEnum;
use App\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        Schema::disableForeignKeyConstraints();
        AccountType::truncate();
        Schema::enableForeignKeyConstraints();

        // Seed Account Types
        foreach (AccountTypeEnum::cases() as $type) {
            AccountType::create([
                'id' => $type->code(), // Use code as ID
                'name' => $type->value,
                'code' => $type->code(),
                'class' => $type->accountClass()->value,
            ]);
        }
    }
}
