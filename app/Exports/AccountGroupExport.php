<?php

namespace App\Exports;

use App\Models\AccountGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountGroupExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return AccountGroup::with('accountType', 'parent')->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'Type',
            'Parent Group',
            'Description',
            'Is System',
        ];
    }

    public function map($accountGroup): array
    {
        return [
            $accountGroup->name,
            $accountGroup->code,
            $accountGroup->accountType->name,
            $accountGroup->parent ? $accountGroup->parent->name : '',
            $accountGroup->description,
            $accountGroup->is_system ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
