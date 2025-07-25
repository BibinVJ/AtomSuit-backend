<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
    ) {
        $this->middleware("permission:" . PermissionsEnum::VIEW_PERMISSION->value)->only(['index']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $permissions = $this->permissionService->getAll($filters);

        return ApiResponse::success('Permissions fetched successfully.', PermissionResource::collection($permissions));
    }
}
