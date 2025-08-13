<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitController extends Controller
{
    public function __construct(
        protected UnitRepository $unitRepository,
        protected UnitService $unitService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_UNIT->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_UNIT->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_UNIT->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_UNIT->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'sort_by', 'sort_direction']);
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $units = $this->unitRepository->all($paginate, $perPage, $filters);

        if ($paginate) {
            $paginated = UnitResource::paginated($units);

            return ApiResponse::success(
                'Units fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Units fetched successfully.',
            UnitResource::collection($units),
            Response::HTTP_OK,
            ['total' => count($units)]
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

    public function destroy(Unit $unit)
    {
        $this->unitService->delete($unit);

        return ApiResponse::success('Unit deleted successfully.');
    }
}
