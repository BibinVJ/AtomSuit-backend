<?php

namespace App\Imports;

use App\Models\Category;
use App\Rules\UniqueInTrash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoryImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
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
            'name' => ['required', 'string', new UniqueInTrash('categories', 'name')],
            'description' => 'nullable|string',
        ];
    }
}
