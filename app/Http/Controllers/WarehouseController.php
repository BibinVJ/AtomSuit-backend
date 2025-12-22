<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Repositories\WarehouseRepository;
use App\Services\WarehouseService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WarehouseController extends Controller
{
    public function __construct(
        protected WarehouseRepository $warehouseRepository,
        protected WarehouseService $warehouseService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_WAREHOUSE->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_WAREHOUSE->value)->only(['store', 'import']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_WAREHOUSE->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_WAREHOUSE->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $warehouses = $this->warehouseRepository->all($paginate, $perPage, $filters);

        $result = WarehouseResource::collectionWithMeta($warehouses);

        return ApiResponse::success(
            'Warehouses fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(WarehouseRequest $request)
    {
        $warehouse = $this->warehouseService->create($request->validated());

        return ApiResponse::success('Warehouse created successfully.', WarehouseResource::make($warehouse));
    }

    public function show(Warehouse $warehouse)
    {
        return ApiResponse::success('Warehouse fetched successfully.', WarehouseResource::make($warehouse));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $updatedWarehouse = $this->warehouseService->update($warehouse, $request->validated());

        return ApiResponse::success('Warehouse updated successfully.', WarehouseResource::make($updatedWarehouse));
    }

    public function destroy(Request $request, Warehouse $warehouse)
    {
        $this->warehouseService->delete($warehouse, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Warehouse permanently deleted.' : 'Warehouse deleted successfully.');
    }

    public function restore(Warehouse $warehouse)
    {
        $warehouse = $this->warehouseService->restore($warehouse);

        return ApiResponse::success('Warehouse restored successfully.', WarehouseResource::make($warehouse));
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\WarehouseExport, 'warehouses_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }

    public function import(\App\Http\Requests\ImportRequest $request)
    {
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\WarehouseImport, $request->file('file'));

        return ApiResponse::success('Warehouses imported successfully.');
    }

    public function downloadSample()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings
        {
            public function collection()
            {
                return collect([
                    [
                        'Main Warehouse',
                        'WH-001',
                        'Main storage facility',
                        '123 Main St',
                        'Suite 100',
                        'New York',
                        'NY',
                        'USA',
                        '10001',
                        '123-456-7890',
                        'warehouse@example.com',
                    ],
                ]);
            }

            public function headings(): array
            {
                return [
                    'Name',
                    'Code',
                    'Description',
                    'Address Line 1',
                    'Address Line 2',
                    'City',
                    'State',
                    'Country',
                    'Zip Code',
                    'Phone',
                    'Email',
                ];
            }
        }, 'sample_warehouses.xlsx');
    }
}
