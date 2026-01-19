<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorSampleExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            [
                'Jane Doe',
                'jane@example.com',
                '9876543210',
                '456 Elm St, Metropolis',
                'Accounts Payable',
                'Cost of Goods Sold',
                'Purchase Discounts',
                'Purchase Returns',
            ],
        ]);
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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
