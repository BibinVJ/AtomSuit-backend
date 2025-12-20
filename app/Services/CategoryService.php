<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Exception;

class CategoryService extends BaseService
{
    public function __construct(protected CategoryRepository $categoryRepository) {
        $this->repository = $categoryRepository;
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $category): void
    {
        /** @var \App\Models\Category $category */
        if ($category->items()->exists()) {
            throw new Exception('Category is assigned to items and cannot be hard deleted.');
        }
    }
}
