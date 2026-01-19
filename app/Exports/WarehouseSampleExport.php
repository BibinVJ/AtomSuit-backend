<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehouseSampleExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            [
                'Main Warehouse',
                'WH-001',
                'Main storage facility',
                '123 Main St',
                'Suite 100',
                'New York',
                'NY',
                'USA',
                '10001',
                '123-456-7890',
                'warehouse@example.com',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'Description',
            'Address Line 1',
            'Address Line 2',
            'City',
            'State',
            'Country',
            'Zip Code',
            'Phone',
            'Email',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
