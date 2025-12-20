<?php

namespace App\Exports;

use App\Models\ExchangeRate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExchangeRateExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return ExchangeRate::with(['baseCurrency', 'targetCurrency'])->get();
    }

    public function headings(): array
    {
        return [
            'Base Currency',
            'Target Currency',
            'Rate',
            'Effective Date',
        ];
    }

    public function map($rate): array
    {
        return [
            $rate->baseCurrency->code,
            $rate->targetCurrency->code,
            $rate->rate,
            $rate->effective_date->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
