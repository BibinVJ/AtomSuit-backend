<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) { }
    /**
     * Display the home dashboard.
     */
    public function home()
    {
        $dto = $this->dashboardService->getMetrics();
        return ApiResponse::success('Dashboard data fetched.', new DashboardResource($dto));
    }

}
