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
        $metrics = $this->dashboardService->getMetrics();

        // For central (superadmin), return array directly
        // For tenant, wrap in DashboardResource
        if (is_array($metrics)) {
            return ApiResponse::success('Dashboard data fetched.', $metrics);
        }

        return ApiResponse::success('Dashboard data fetched.', new DashboardResource($metrics));
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

    /**
     * Get available dashboard cards for the user.
     */
    public function getCards()
    {
        $cards = $this->dashboardService->getAvailableCards();

        return ApiResponse::success('Dashboard cards fetched.', $cards);
    }
}
