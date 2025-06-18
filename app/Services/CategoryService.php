<?php

namespace App\Services;

use App\Models\Category;
use Exception;

class CategoryService
{
    public function ensureCategoryIsDeletable(Category $category)
    {
        if ($category->items()->exists()) {
            throw new Exception('Category is assigned to items and cannot be deleted.');
        }
    }
}
