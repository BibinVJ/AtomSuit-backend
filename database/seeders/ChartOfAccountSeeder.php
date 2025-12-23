<?php

namespace Database\Seeders;

use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Account Types
        $accountTypes = [
            ['name' => 'Asset', 'code' => '1', 'class' => 'debit'],
            ['name' => 'Liability', 'code' => '2', 'class' => 'credit'],
            ['name' => 'Equity', 'code' => '3', 'class' => 'credit'],
            ['name' => 'Income', 'code' => '4', 'class' => 'credit'],
            ['name' => 'Cost of Goods Sold', 'code' => '5', 'class' => 'debit'],
            ['name' => 'Expense', 'code' => '6', 'class' => 'debit'],
        ];

        foreach ($accountTypes as $type) {
            AccountType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        // Helper to get Type ID
        $getTypeId = fn ($name) => AccountType::where('name', $name)->first()->id;

        // Seed Account Groups
        $groups = [
            // Assets
            ['name' => 'Current Assets', 'code' => '100', 'account_type_id' => $getTypeId('Asset')],
            ['name' => 'Non-Current Assets', 'code' => '110', 'account_type_id' => $getTypeId('Asset')],

            // Liabilities
            ['name' => 'Current Liabilities', 'code' => '200', 'account_type_id' => $getTypeId('Liability')],
            ['name' => 'Non-Current Liabilities', 'code' => '210', 'account_type_id' => $getTypeId('Liability')],

            // Equity
            ['name' => 'Owners Equity', 'code' => '300', 'account_type_id' => $getTypeId('Equity')],

            // Income
            ['name' => 'Operating Income', 'code' => '400', 'account_type_id' => $getTypeId('Income')],
            ['name' => 'Non-Operating Income', 'code' => '410', 'account_type_id' => $getTypeId('Income')],

            // COGS
            ['name' => 'Cost of Goods Sold', 'code' => '500', 'account_type_id' => $getTypeId('Cost of Goods Sold')],

            // Expenses
            ['name' => 'Operating Expenses', 'code' => '600', 'account_type_id' => $getTypeId('Expense')],
        ];

        foreach ($groups as $group) {
            AccountGroup::firstOrCreate(
                ['code' => $group['code']],
                $group
            );
        }

        // Helper to get Group ID
        $getGroupId = fn ($code) => AccountGroup::where('code', $code)->first()->id;

        // Seed Chart of Accounts
        $accounts = [
            // Current Assets
            ['name' => 'Cash', 'code' => '1001', 'account_group_id' => $getGroupId('100')],
            ['name' => 'Petty Cash', 'code' => '1002', 'account_group_id' => $getGroupId('100')],
            ['name' => 'Accounts Receivable', 'code' => '1003', 'account_group_id' => $getGroupId('100')],
            ['name' => 'Inventory', 'code' => '1004', 'account_group_id' => $getGroupId('100')],

            // Current Liabilities
            ['name' => 'Accounts Payable', 'code' => '2001', 'account_group_id' => $getGroupId('200')],
            ['name' => 'Sales Tax Payable', 'code' => '2002', 'account_group_id' => $getGroupId('200')],

            // Equity
            ['name' => 'Retained Earnings', 'code' => '3001', 'account_group_id' => $getGroupId('300')],

            // Income
            ['name' => 'Sales Revenue', 'code' => '4001', 'account_group_id' => $getGroupId('400')],
            ['name' => 'Sales Discounts', 'code' => '4002', 'account_group_id' => $getGroupId('400')],
            ['name' => 'Sales Returns', 'code' => '4003', 'account_group_id' => $getGroupId('400')],

            // COGS
            ['name' => 'Cost of Goods Sold', 'code' => '5001', 'account_group_id' => $getGroupId('500')],
            ['name' => 'Purchase Discounts', 'code' => '5002', 'account_group_id' => $getGroupId('500')],
            ['name' => 'Purchase Returns', 'code' => '5003', 'account_group_id' => $getGroupId('500')],

            // Expenses
            ['name' => 'Rent Expense', 'code' => '6001', 'account_group_id' => $getGroupId('600')],
            ['name' => 'Salaries Expense', 'code' => '6002', 'account_group_id' => $getGroupId('600')],
            ['name' => 'Inventory Adjustment', 'code' => '6003', 'account_group_id' => $getGroupId('600')],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::firstOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}
