<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Purchase::with(['vendor', 'user'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Invoice Number',
            'Vendor',
            'Purchase Date',
            'Status',
            'Payment Status',
            'Total',
            'Note',
        ];
    }

    public function map($purchase): array
    {
        return [
            $purchase->id,
            $purchase->invoice_number,
            $purchase->vendor->name ?? '',
            $purchase->purchase_date->format('Y-m-d'),
            $purchase->status->value ?? $purchase->status,
            $purchase->payment_status->value ?? $purchase->payment_status,
            $purchase->items->sum(fn ($i) => $i->quantity * $i->unit_cost),
            $purchase->note,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
