<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoryImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Category([
            'name' => $row['name'],
            'description' => $row['description'] ?? '',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:categories,name',
            'description' => 'nullable|string',
        ];
    }
}
