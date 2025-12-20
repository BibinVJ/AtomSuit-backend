<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Item::with(['category', 'unit'])->get();
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
        ];
    }

    public function map($item): array
    {
        return [
            $item->sku,
            $item->name,
            $item->category->name,
            $item->unit->name,
            $item->description,
            $item->type->value,
            $item->selling_price,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
