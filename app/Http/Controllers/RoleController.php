<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Repositories\RoleRepository;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function __construct(
        protected RoleRepository $roleRepository,
        protected RoleService $roleService,
    ) {
        $this->middleware("permission:" . PermissionsEnum::VIEW_ROLE->value)->only(['index']);
        $this->middleware("permission:" . PermissionsEnum::CREATE_ROLE->value)->only(['store']);
        $this->middleware("permission:" . PermissionsEnum::UPDATE_ROLE->value)->only(['update']);
        $this->middleware("permission:" . PermissionsEnum::DELETE_ROLE->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search']);
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $filters['exclude_roles'] = [
            RolesEnum::SUPER_ADMIN->value,
            RolesEnum::ADMIN->value,
        ];

        $roles = $this->roleRepository->all($paginate, $perPage, $filters, ['permissions']);

        return ApiResponse::success(
            'Roles fetched successfully.',
            $paginate ? RoleResource::paginated($roles) : RoleResource::collection($roles)
        );
    }

    public function show(Role $role)
    {
        if (in_array($role->name, [RolesEnum::SUPER_ADMIN->value, RolesEnum::ADMIN->value])) {
            return ApiResponse::error('Access denied to view this role.', Response::HTTP_FORBIDDEN);
        }

        $role = $this->roleRepository->find($role->id, ['permissions']);
        return ApiResponse::success('Role fetched successfully.', RoleResource::make($role));
    }

    public function store(RoleRequest $request)
    {
        $role = $this->roleService->create($request->validated());
        return ApiResponse::success('Role created successfully.', RoleResource::make($role));
    }

    public function update(RoleRequest $request, Role $role)
    {
        if (in_array($role->name, [RolesEnum::SUPER_ADMIN->value, RolesEnum::ADMIN->value])) {
            return ApiResponse::error('Cannot update a protected role.', code: Response::HTTP_FORBIDDEN);
        }

        $updatedRole = $this->roleService->update($role, $request->validated());
        return ApiResponse::success('Role updated successfully.', RoleResource::make($updatedRole));
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, [RolesEnum::SUPER_ADMIN->value, RolesEnum::ADMIN->value])) {
            return ApiResponse::error('Cannot delete a protected role.', code: Response::HTTP_FORBIDDEN);
        }

        $this->roleService->delete($role);
        return ApiResponse::success('Role deleted successfully.');
    }
}
