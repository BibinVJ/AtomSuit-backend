<?php

namespace App\Exports;

use App\Models\TaxGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaxGroupExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return TaxGroup::with('taxRates')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Tax Rates',
        ];
    }

    public function map($taxGroup): array
    {
        return [
            $taxGroup->id,
            $taxGroup->name,
            $taxGroup->taxRates->pluck('name')->join(', '),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
