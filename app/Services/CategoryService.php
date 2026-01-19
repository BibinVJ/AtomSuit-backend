<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class CategoryService extends BaseService
{
    public function __construct(protected CategoryRepository $categoryRepository)
    {
        $this->repository = $categoryRepository;
    }

    protected function validateForceDelete(Model $category): void
    {
        /** @var \App\Models\Category $category */
        if ($category->items()->exists()) {
            throw new Exception('Category is assigned to items and cannot be hard deleted.');
        }
    }
}
