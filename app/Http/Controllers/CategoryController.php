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
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected CategoryService $categoryService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_CATEGORY->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_CATEGORY->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_CATEGORY->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_CATEGORY->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'sort_by', 'sort_direction']);
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $categories = $this->categoryRepository->all($paginate, $perPage, $filters);

        if ($paginate) {
            $paginated = CategoryResource::paginated($categories);

            return ApiResponse::success(
                'Categories fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Categories fetched successfully.',
            CategoryResource::collection($categories),
            Response::HTTP_OK,
            ['total' => count($categories)]
        );
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryRepository->create($request->validated());

        return ApiResponse::success('Category created successfully.', CategoryResource::make($category));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $updatedCategory = $this->categoryRepository->update($category, $request->validated());

        return ApiResponse::success('Category updated successfully.', CategoryResource::make($updatedCategory));
    }

    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);

        return ApiResponse::success('Category deleted successfully.');
    }
}
