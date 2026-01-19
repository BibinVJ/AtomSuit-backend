<?php

namespace App\Imports;

use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Rules\UniqueInTrash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomerImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $salesAccount = ChartOfAccount::where('name', $row['sales_account'])->first();
        $salesDiscountAccount = ChartOfAccount::where('name', $row['sales_discount_account'])->first();
        $receivablesAccount = ChartOfAccount::where('name', $row['receivables_account'])->first();
        $salesReturnAccount = ChartOfAccount::where('name', $row['sales_return_account'])->first();

        return new Customer([
            'name' => $row['name'],
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'currency_id' => 1, // Default to primary currency if not specified (TODO: handle currency lookup)
            'sales_account_id' => $salesAccount?->id,
            'sales_discount_account_id' => $salesDiscountAccount?->id,
            'receivables_account_id' => $receivablesAccount?->id,
            'sales_return_account_id' => $salesReturnAccount?->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => ['nullable', 'email', new UniqueInTrash('customers', 'email')],
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'sales_account' => 'nullable|string|exists:chart_of_accounts,name',
            'sales_discount_account' => 'nullable|string|exists:chart_of_accounts,name',
            'receivables_account' => 'nullable|string|exists:chart_of_accounts,name',
            'sales_return_account' => 'nullable|string|exists:chart_of_accounts,name',
        ];
    }
}
