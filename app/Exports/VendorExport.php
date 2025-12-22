<?php

namespace App\Exports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Vendor::all();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Address',
            'Payables Account',
            'Purchase Account',
            'Purchase Discount Account',
            'Purchase Return Account',
        ];
    }

    public function map($vendor): array
    {
        return [
            $vendor->name,
            $vendor->email,
            $vendor->phone,
            $vendor->address,
            $vendor->payablesAccount?->name,
            $vendor->purchaseAccount?->name,
            $vendor->purchaseDiscountAccount?->name,
            $vendor->purchaseReturnAccount?->name,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
