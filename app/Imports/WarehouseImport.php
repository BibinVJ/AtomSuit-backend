<?php

namespace App\Imports;

use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WarehouseImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Warehouse([
            'name' => $row['name'],
            'code' => $row['code'] ?? null,
            'description' => $row['description'] ?? null,
            'address_line_1' => $row['address_line_1'] ?? null,
            'address_line_2' => $row['address_line_2'] ?? null,
            'city' => $row['city'] ?? null,
            'state' => $row['state'] ?? null,
            'country' => $row['country'] ?? null,
            'zip_code' => $row['zip_code'] ?? null,
            'phone' => $row['phone'] ?? null,
            'email' => $row['email'] ?? null,
        ]);
    }
}
