<?php

namespace App\Exports;

use App\Models\Currency;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CurrencyExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Currency::all();
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Symbol',
            'Is Default',
            'Active',
        ];
    }

    public function map($currency): array
    {
        return [
            $currency->code,
            $currency->name,
            $currency->symbol,
            $currency->is_default ? 'Yes' : 'No',
            $currency->active ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
