<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Exception;

class CategoryService
{
    public function __construct(protected CategoryRepository $categoryRepository) {}

    public function delete(Category $category, bool $force = false)
    {
        if ($force) {
            if ($category->items()->exists()) {
                throw new Exception('Category is assigned to items and cannot be hard deleted.');
            }
            return $this->categoryRepository->forceDelete($category);
        }

        return $this->categoryRepository->delete($category);
    }

    public function restore(int $id): Category
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $this->categoryRepository->restore($category);

        return $category;
    }
}
