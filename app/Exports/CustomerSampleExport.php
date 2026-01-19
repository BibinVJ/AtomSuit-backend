<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerSampleExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            [
                'John Doe',
                'john@example.com',
                '1234567890',
                '123 Main St, Springfield',
                'Sales Revenue',
                'Sales Discounts',
                'Accounts Receivable',
                'Sales Returns',
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
            'Sales Account',
            'Sales Discount Account',
            'Receivables Account',
            'Sales Return Account',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
