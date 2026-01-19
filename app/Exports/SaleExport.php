<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SaleExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Sale::with(['customer', 'user'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Invoice Number',
            'Customer',
            'Sale Date',
            'Status',
            'Payment Status',
            'Total',
            'Note',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->invoice_number,
            $sale->customer->name ?? '',
            $sale->sale_date->format('Y-m-d'),
            $sale->status->value ?? $sale->status,
            $sale->payment_status->value ?? $sale->payment_status,
            $sale->items->sum(fn ($i) => $i->quantity * $i->unit_price),
            $sale->note,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
