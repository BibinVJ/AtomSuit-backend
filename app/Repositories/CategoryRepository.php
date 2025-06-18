<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    public function all($paginate = false, $perPage = 15)
    {
        if ($paginate) {
            return Category::paginate($perPage);
        }

        return Category::all();
    }

    public function create(array $data): Category
    {
        return Category::create($data)->refresh();
    }

    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
