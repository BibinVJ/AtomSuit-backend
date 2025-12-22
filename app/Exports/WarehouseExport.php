<?php

namespace App\Exports;

use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WarehouseExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Warehouse::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Code',
            'Description',
            'Address Line 1',
            'Address Line 2',
            'City',
            'State',
            'Country',
            'Zip Code',
            'Phone',
            'Email',
            'Created At',
        ];
    }

    public function map($warehouse): array
    {
        return [
            $warehouse->id,
            $warehouse->name,
            $warehouse->code,
            $warehouse->description,
            $warehouse->address_line_1,
            $warehouse->address_line_2,
            $warehouse->city,
            $warehouse->state,
            $warehouse->country,
            $warehouse->zip_code,
            $warehouse->phone,
            $warehouse->email,
            $warehouse->created_at,
        ];
    }
}
