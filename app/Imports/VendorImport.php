<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VendorImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $payablesAccount = \App\Models\ChartOfAccount::where('name', $row['payables_account'])->first();
        $purchaseAccount = \App\Models\ChartOfAccount::where('name', $row['purchase_account'])->first();
        $purchaseDiscountAccount = \App\Models\ChartOfAccount::where('name', $row['purchase_discount_account'])->first();
        $purchaseReturnAccount = \App\Models\ChartOfAccount::where('name', $row['purchase_return_account'])->first();

        return new Vendor([
            'name' => $row['name'],
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'currency_id' => 1, // Default to primary currency if not specified (TODO: handle currency lookup)
            'payables_account_id' => $payablesAccount?->id,
            'purchase_account_id' => $purchaseAccount?->id,
            'purchase_discount_account_id' => $purchaseDiscountAccount?->id,
            'purchase_return_account_id' => $purchaseReturnAccount?->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => ['nullable', 'email', new \App\Rules\UniqueInTrash('vendors', 'email')],
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'payables_account' => 'nullable|string|exists:chart_of_accounts,name',
            'purchase_account' => 'nullable|string|exists:chart_of_accounts,name',
            'purchase_discount_account' => 'nullable|string|exists:chart_of_accounts,name',
            'purchase_return_account' => 'nullable|string|exists:chart_of_accounts,name',
        ];
    }
}
