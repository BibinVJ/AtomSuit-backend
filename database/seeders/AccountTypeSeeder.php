<?php

namespace Database\Seeders;

use App\Enums\AccountTypeEnum;
use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Account Types
        foreach (AccountTypeEnum::cases() as $type) {
            AccountType::updateOrCreate(
                ['id' => $type->code()],
                [
                    'name' => $type->value,
                    'code' => $type->code(),
                    'class' => $type->accountClass()->value,
                ]
            );
        }
    }
}
