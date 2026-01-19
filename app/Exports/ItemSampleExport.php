<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemSampleExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            [
                'SKU001',
                'Sample Product',
                'General',
                'Pieces',
                'This is a sample product description',
                'product',
                '100.00',
                'active',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Name',
            'Category',
            'Unit',
            'Description',
            'Type',
            'Selling Price',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
