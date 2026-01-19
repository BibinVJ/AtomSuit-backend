<?php

namespace Database\Seeders;

use App\Models\AccountGroup;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            ['name' => 'GRN Clearing Account', 'code' => '2003', 'account_group_id' => $getGroupId('200')],

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
            ['name' => 'Exchange Gain/Loss', 'code' => '6004', 'account_group_id' => $getGroupId('600')],
            ['name' => 'Bank Charges', 'code' => '6005', 'account_group_id' => $getGroupId('600')],
            ['name' => 'Round Off', 'code' => '6006', 'account_group_id' => $getGroupId('600')],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::firstOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}
