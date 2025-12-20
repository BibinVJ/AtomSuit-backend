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
use Illuminate\Support\Facades\Auth;
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
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $with = ['features'];
        if (Auth::check() && Auth::user()->can(PermissionsEnum::VIEW_TENANT->value)) {
            $with[] = 'subscribedTenants';
        }

        // Use central connection to get plans for tenant upgrade purposes
        $plans = tenancy()->central(function () use ($paginate, $perPage, $filters, $with) {
            return $this->planRepository->all($paginate, $perPage, $filters, $with);
        });

        $result = PlanResource::collectionWithMeta($plans, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Plans fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function show(Plan $plan)
    {
        $plan = $this->planRepository->find($plan->id, ['subscribedTenants', 'features']);

        return ApiResponse::success('Plan fetched successfully.', PlanResource::make($plan));
    }

    public function store(PlanRequest $request)
    {
        $plan = $this->planService->create($request->validated());

        return ApiResponse::success('Plan created successfully.', PlanResource::make($plan));
    }

    public function update(PlanRequest $request, Plan $plan)
    {
        $updatedPlan = $this->planService->update($plan, $request->validated());

        return ApiResponse::success('Plan updated successfully.', PlanResource::make($updatedPlan));
    }

    public function destroy(Plan $plan)
    {
        $this->planService->delete($plan);

        return ApiResponse::success('Plan deleted successfully.');
    }
}
