<?php

namespace App\Exports;

use App\Models\ChartOfAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ChartOfAccountExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return ChartOfAccount::with('accountGroup')->get();
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

    public function map($chartOfAccount): array
    {
        return [
            $chartOfAccount->name,
            $chartOfAccount->code,
            $chartOfAccount->accountGroup->name,
            $chartOfAccount->description,
            $chartOfAccount->opening_balance,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
