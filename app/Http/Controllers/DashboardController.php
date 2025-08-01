<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\DashboardLayoutRequest;
use App\Http\Resources\DashboardLayoutResource;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Display the home dashboard.
     */
    public function home()
    {
        $dto = $this->dashboardService->getMetrics();

        return ApiResponse::success('Dashboard data fetched.', new DashboardResource($dto));
    }

    /**
     * Get the active layout for the dashboard.
     */
    public function getlayout()
    {
        $layouts = $this->dashboardService->getLayouts();

        return ApiResponse::success('Dashboard layouts fetched.', DashboardLayoutResource::collection($layouts));
    }

    /**
     * update the active layout for the dashboard.
     */
    public function updateLayout(DashboardLayoutRequest $request)
    {
        $layouts = $this->dashboardService->updateLayout($request->validated());

        return ApiResponse::success('Dashboard layouts updated.', DashboardLayoutResource::collection($layouts));
    }
}
