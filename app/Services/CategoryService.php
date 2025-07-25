<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Exception;

class CategoryService
{
    public function __construct(protected CategoryRepository $categoryRepository) {}
    
    public function delete(Category $category)
    {
        if ($category->items()->exists()) {
            throw new Exception('Category is assigned to items and cannot be deleted.');
        }

        $this->categoryRepository->delete($category);
    }
}
