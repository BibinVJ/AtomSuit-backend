<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exports\UnitExport;
use App\Imports\UnitImport;
use Maatwebsite\Excel\Facades\Excel;

class UnitController extends Controller
{

    public function __construct(
        protected UnitRepository $unitRepository,
        protected UnitService $unitService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_UNIT->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_UNIT->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_UNIT->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_UNIT->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $units = $this->unitRepository->all($paginate, $perPage, $filters);

        $result = UnitResource::collectionWithMeta($units, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Units fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(UnitRequest $request)
    {
        $unit = $this->unitRepository->create($request->validated());

        return ApiResponse::success('Unit created successfully.', UnitResource::make($unit));
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $updatedUnit = $this->unitRepository->update($unit, $request->validated());

        return ApiResponse::success('Unit updated successfully.', UnitResource::make($updatedUnit));
    }

    public function destroy(Request $request, int $id)
    {
        $unit = Unit::withTrashed()->findOrFail($id);
        
        $this->unitService->delete($unit, $request->boolean('force'));
        return ApiResponse::success($request->boolean('force') ? 'Unit permanently deleted.' : 'Unit deleted successfully.');
    }

    public function restore(int $id)
    {
        $unit = $this->unitService->restore($id);

        return ApiResponse::success('Unit restored successfully.', UnitResource::make($unit));
    }

    public function export()
    {
        return Excel::download(new UnitExport, 'units_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    public function import(ImportRequest $request)
    {
        Excel::import(new UnitImport, $request->file('file'));

        return ApiResponse::success('Units imported successfully.');
    }

    public function downloadSample()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function collection()
            {
                return collect([
                    [
                        'Sample Unit',
                        'SU',
                        'Sample Description',
                        'active',
                    ],
                ]);
            }

            public function headings(): array
            {
                return [
                    'Name',
                    'Code',
                    'Description',
                    'Status',
                ];
            }
        }, 'sample_units.xlsx');
    }
}
