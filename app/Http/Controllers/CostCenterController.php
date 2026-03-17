<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\CostCenterRequest;
use App\Http\Resources\CostCenterResource;
use App\Models\CostCenter;
use App\Repositories\CostCenterRepository;
use App\Services\CostCenterService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CostCenterController extends Controller
{
    public function __construct(
        protected CostCenterRepository $costCenterRepository,
        protected CostCenterService $costCenterService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_COST_CENTER->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_COST_CENTER->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_COST_CENTER->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_COST_CENTER->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction', 'trashed', 'type']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $costCenters = $this->costCenterRepository->all($paginate, $perPage, $filters, [
            'warehouse',
            'parent',
        ]);

        $result = CostCenterResource::collectionWithMeta($costCenters, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Cost Centers fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(CostCenterRequest $request)
    {
        $costCenter = $this->costCenterRepository->create($request->validated());

        return ApiResponse::success('Cost Center created successfully.', CostCenterResource::make($costCenter));
    }

    public function show(CostCenter $costCenter)
    {
        $costCenter->load(['warehouse', 'parent']);

        return ApiResponse::success('Cost Center fetched successfully.', CostCenterResource::make($costCenter));
    }

    public function update(CostCenterRequest $request, CostCenter $costCenter)
    {
        $updatedCostCenter = $this->costCenterRepository->update($costCenter, $request->validated());

        return ApiResponse::success('Cost Center updated successfully.', CostCenterResource::make($updatedCostCenter));
    }

    public function destroy(Request $request, CostCenter $costCenter)
    {
        $this->costCenterService->delete($costCenter, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Cost Center permanently deleted.' : 'Cost Center deleted successfully.');
    }

    public function restore(CostCenter $costCenter)
    {
        $costCenter = $this->costCenterService->restore($costCenter);

        return ApiResponse::success('Cost Center restored successfully.', CostCenterResource::make($costCenter));
    }
}
