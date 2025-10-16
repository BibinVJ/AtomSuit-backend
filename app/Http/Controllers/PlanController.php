<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\PlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Repositories\PlanRepository;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlanController extends Controller
{
    public function __construct(
        protected PlanRepository $planRepository,
        protected PlanService $planService,
    ) {
        $this->middleware('permission:' . PermissionsEnum::CREATE_PLAN->value)->only(['store']);
        $this->middleware('permission:' . PermissionsEnum::UPDATE_PLAN->value)->only(['update']);
        $this->middleware('permission:' . PermissionsEnum::DELETE_PLAN->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'sort_by', 'sort_direction']);
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $plans = $this->planRepository->all($paginate, $perPage, $filters);

        if ($paginate) {
            $paginated = PlanResource::paginated($plans);

            return ApiResponse::success(
                'Plans fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Plans fetched successfully.',
            PlanResource::collection($plans),
            Response::HTTP_OK,
            ['total' => count($plans)]
        );
    }

    public function show(Plan $plan)
    {
        $plan = $this->planRepository->find($plan->id, ['subscribedTenants']);

        return ApiResponse::success('Plan fetched successfully.', PlanResource::make($plan));
    }

    public function store(PlanRequest $request)
    {
        $plan = $this->planRepository->create($request->validated());

        return ApiResponse::success('Plan created successfully.', PlanResource::make($plan));
    }

    public function update(PlanRequest $request, Plan $plan)
    {
        $updatedPlan = $this->planRepository->update($plan, $request->validated());

        return ApiResponse::success('Plan updated successfully.', PlanResource::make($updatedPlan));
    }

    public function destroy(Plan $plan)
    {
        $this->planService->delete($plan);

        return ApiResponse::success('Plan deleted successfully.');
    }
}
