<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepository $categoryRepo,
        protected CategoryService $categoryService
    ) {
        $this->middleware("permission:" . PermissionsEnum::VIEW_CATEGORY->value)->only(['index']);
        $this->middleware("permission:" . PermissionsEnum::CREATE_CATEGORY->value)->only(['store']);
        $this->middleware("permission:" . PermissionsEnum::UPDATE_CATEGORY->value)->only(['update']);
        $this->middleware("permission:" . PermissionsEnum::DELETE_CATEGORY->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search']);
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $categories = $this->categoryRepo->all($paginate, $perPage, $filters);

        return $paginate
            ? ApiResponse::success('Categories fetched.', CategoryResource::paginated($categories))
            : ApiResponse::success('Categories fetched.', CategoryResource::collection($categories));
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryRepo->create($request->validated());
        return ApiResponse::success('Category created successfully.', CategoryResource::make($category));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $this->categoryRepo->update($category, $request->validated());
        return ApiResponse::success('Category updated successfully.', CategoryResource::make($category));
    }

    public function destroy(Category $category)
    {
        $this->categoryService->ensureCategoryIsDeletable($category);
        $this->categoryRepo->delete($category);
        return ApiResponse::success('Category deleted successfully.');
    }
}
