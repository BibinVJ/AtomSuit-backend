<?php

namespace App\Imports;

use App\Models\AccountGroup;
use App\Models\AccountType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AccountGroupImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Simple resolution for Account Type (assuming name provided)
        $accountType = AccountType::where('name', $row['type'])->first();

        // Simple resolution for Parent Group (assuming name provided)
        $parent = null;
        if (! empty($row['parent_group'])) {
            $parent = AccountGroup::where('name', $row['parent_group'])->first();
        }

        return new AccountGroup([
            'name' => $row['name'],
            'code' => $row['code'] ?? null,
            'account_type_id' => $accountType?->id, // Consider validating existence
            'parent_id' => $parent?->id,
            'description' => $row['description'] ?? '',
            'is_system' => false,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'code' => 'nullable|string',
            'type' => 'required|exists:account_types,name',
            'parent_group' => 'nullable|exists:account_groups,name',
            'description' => 'nullable|string',
        ];
    }
}
