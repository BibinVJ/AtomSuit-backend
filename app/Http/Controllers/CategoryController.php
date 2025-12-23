<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\CategoryExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CategoryResource;
use App\Imports\CategoryImport;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected CategoryService $categoryService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_CATEGORY->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_CATEGORY->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_CATEGORY->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_CATEGORY->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $categories = $this->categoryRepository->all($paginate, $perPage, $filters, [
            'salesAccount',
            'cogsAccount',
            'inventoryAccount',
            'inventoryAdjustmentAccount',
            'purchaseAccount',
        ]);

        $result = CategoryResource::collectionWithMeta($categories, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Categories fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
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

    public function destroy(Request $request, Category $category)
    {
        $this->categoryService->delete($category, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Category permanently deleted.' : 'Category deleted successfully.');
    }

    public function restore(Category $category)
    {
        $category = $this->categoryService->restore($category);

        return ApiResponse::success('Category restored successfully.', CategoryResource::make($category));
    }

    public function export()
    {
        return Excel::download(new CategoryExport, 'categories_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }

    public function import(ImportRequest $request)
    {
        Excel::import(new CategoryImport, $request->file('file'));

        return ApiResponse::success('Categories imported successfully.');
    }

    public function downloadSample()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings
        {
            public function collection()
            {
                return collect([
                    [
                        'Sample Category',
                        'This is a sample category description',
                        'active',
                    ],
                ]);
            }

            public function headings(): array
            {
                return [
                    'Name',
                    'Description',
                    'Status',
                ];
            }
        }, 'sample_categories.xlsx');
    }
}
