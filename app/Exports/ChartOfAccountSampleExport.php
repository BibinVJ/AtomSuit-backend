<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ChartOfAccountSampleExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            [
                'Cash on Hand',
                '1001',
                'Current Assets',
                'Cash account',
                '0.00',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'Group',
            'Description',
            'Opening Balance',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
