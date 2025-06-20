<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    public function all(bool $paginate = false, int $perPage = 15, array $filters = [])
    {
        $query = Category::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        $query->orderBy('name');

        return $paginate
            ? $query->paginate($perPage)
            : $query->get();
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
