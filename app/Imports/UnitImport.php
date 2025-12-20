<?php

namespace App\Imports;

use App\Models\Unit;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UnitImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Unit([
            'name' => $row['name'],
            'code' => $row['code'] ?? Str::slug($row['name']),
            'description' => $row['description'] ?? '',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:units,name',
            'code' => 'sometimes|nullable|string|unique:units,code',
            'description' => 'nullable|string',
        ];
    }
}
