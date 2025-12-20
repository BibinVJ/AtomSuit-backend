<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use App\Enums\ItemType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Str;

class ItemImport implements ToModel, WithHeadingRow, WithValidation
{
    public function __construct()
    {
        // 
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $category = Category::firstOrCreate(
            ['name' => trim($row['category'])]
        );

        $unitName = trim($row['unit']);
        $unit = Unit::firstOrCreate(
            ['name' => $unitName],
            ['code' => Str::slug($unitName)]
        );

        return new Item([
            'sku' => $row['sku'],
            'name' => $row['name'],
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'description' => $row['description'] ?? '',
            'type' => strtolower(trim($row['type'] ?? 'product')) === 'service' ? ItemType::SERVICE : ItemType::PRODUCT,
            'selling_price' => $row['selling_price'] ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'sku' => 'required|unique:items,sku',
            'name' => 'required|string',
            'category' => 'required|string',
            'unit' => 'required|string',
            'type' => 'nullable|string',
            'selling_price' => 'nullable|numeric|min:0',
        ];
    }
}
