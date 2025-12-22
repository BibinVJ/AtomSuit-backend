<?php

namespace App\Imports;

use App\Models\AccountGroup;
use App\Models\ChartOfAccount;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ChartOfAccountImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $accountGroup = AccountGroup::where('name', $row['group'])->first();

        return new ChartOfAccount([
            'name' => $row['name'],
            'code' => $row['code'],
            'account_group_id' => $accountGroup?->id,
            'description' => $row['description'] ?? '',
            'opening_balance' => $row['opening_balance'] ?? 0,
            'is_enabled' => isset($row['enabled']) && strtolower($row['enabled']) === 'yes' ? true : false,
            'is_system' => false,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'code' => ['required', 'string', new \App\Rules\UniqueInTrash('chart_of_accounts', 'code')],
            'group' => 'required|exists:account_groups,name',
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
        ];
    }
}
