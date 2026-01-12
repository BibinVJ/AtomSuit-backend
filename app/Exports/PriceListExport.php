<?php

namespace App\Exports;

use App\Models\PriceList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PriceListExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return PriceList::with('currency')->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'Type',
            'Currency',
            'Tax Inclusive',
            'Description',
        ];
    }

    public function map($list): array
    {
        return [
            $list->name,
            $list->code,
            $list->type,
            $list->currency->code,
            $list->is_tax_inclusive ? 'Yes' : 'No',
            $list->description,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
