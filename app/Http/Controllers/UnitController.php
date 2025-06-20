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

class UnitController extends Controller
{
    public function __construct(
        protected UnitRepository $unitRepo,
        protected UnitService $unitService
    ) {
        $this->middleware("permission:" . PermissionsEnum::VIEW_UNIT->value)->only(['index']);
        $this->middleware("permission:" . PermissionsEnum::CREATE_UNIT->value)->only(['store']);
        $this->middleware("permission:" . PermissionsEnum::UPDATE_UNIT->value)->only(['update']);
        $this->middleware("permission:" . PermissionsEnum::DELETE_UNIT->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search']);
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $units = $this->unitRepo->all($paginate, $perPage, $filters);

        return $paginate
            ? ApiResponse::success('Units fetched successfully.', UnitResource::paginated($units))
            : ApiResponse::success('Units fetched successfully.', UnitResource::collection($units));
    }

    public function store(UnitRequest $request)
    {
        $unit = $this->unitRepo->create($request->validated());
        return ApiResponse::success('Unit created successfully.', UnitResource::make($unit));
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $this->unitRepo->update($unit, $request->validated());
        return ApiResponse::success('Unit updated successfully.', UnitResource::make($unit));
    }

    public function destroy(Unit $unit)
    {
        $this->unitService->ensureUnitIsDeletable($unit);
        $this->unitRepo->delete($unit);
        return ApiResponse::success('Unit deleted successfully.');
    }
}
