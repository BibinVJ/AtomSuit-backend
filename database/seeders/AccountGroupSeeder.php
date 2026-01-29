<?php

namespace Database\Seeders;

use App\Enums\AccountTypeEnum;
use App\Models\AccountGroup;
use Illuminate\Database\Seeder;

class AccountGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Account Groups
        $groups = [
            // Assets
            ['name' => 'Current Assets', 'code' => '100', 'account_type_id' => AccountTypeEnum::ASSET->code()],
            ['name' => 'Non-Current Assets', 'code' => '110', 'account_type_id' => AccountTypeEnum::ASSET->code()],

            // Liabilities
            ['name' => 'Current Liabilities', 'code' => '200', 'account_type_id' => AccountTypeEnum::LIABILITY->code()],
            ['name' => 'Non-Current Liabilities', 'code' => '210', 'account_type_id' => AccountTypeEnum::LIABILITY->code()],

            // Equity
            ['name' => 'Owners Equity', 'code' => '300', 'account_type_id' => AccountTypeEnum::EQUITY->code()],

            // Income
            ['name' => 'Operating Income', 'code' => '400', 'account_type_id' => AccountTypeEnum::INCOME->code()],
            ['name' => 'Non-Operating Income', 'code' => '410', 'account_type_id' => AccountTypeEnum::INCOME->code()],

            // COGS
            ['name' => 'Cost of Goods Sold', 'code' => '500', 'account_type_id' => AccountTypeEnum::COGS->code()],

            // Expenses
            ['name' => 'Operating Expenses', 'code' => '600', 'account_type_id' => AccountTypeEnum::EXPENSE->code()],
        ];

        foreach ($groups as $group) {
            AccountGroup::updateOrCreate(
                ['code' => $group['code']],
                $group
            );
        }
    }
}
