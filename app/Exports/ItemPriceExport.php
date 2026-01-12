<?php

namespace App\Exports;

use App\Models\ItemPrice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemPriceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return ItemPrice::with(['priceList', 'item'])->get();
    }

    public function headings(): array
    {
        return [
            'Price List',
            'Item Name',
            'Min Quantity',
            'Price',
        ];
    }

    public function map($itemPrice): array
    {
        return [
            $itemPrice->priceList->name,
            $itemPrice->item->name,
            $itemPrice->min_quantity,
            $itemPrice->price,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
