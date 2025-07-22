<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UserService $userService
    ) {
        $this->middleware("permission:" . PermissionsEnum::VIEW_USER->value)->only(['index']);
        $this->middleware("permission:" . PermissionsEnum::CREATE_USER->value)->only(['store']);
        $this->middleware("permission:" . PermissionsEnum::UPDATE_USER->value)->only(['update']);
        $this->middleware("permission:" . PermissionsEnum::DELETE_USER->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'is_not_admin', 'sort_by', 'sort_direction']);
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $users = $this->userRepository->all($paginate, $perPage, $filters);

        return ApiResponse::success(
            'Users fetched successfully.',
            $paginate ? UserResource::paginated($users) : UserResource::collection($users)
        );
    }

    public function store(UserRequest $request)
    {
        $user = $this->userRepository->create($request->validated());
        return ApiResponse::success('User created successfully.', UserResource::make($user));
    }

    public function update(UserRequest $request, User $user)
    {
        $updatedUser = $this->userRepository->update($user, $request->validated());
        return ApiResponse::success('User updated successfully.', UserResource::make($updatedUser));
    }

    public function destroy(User $user)
    {
        $this->userService->ensureUserIsDeletable($user);
        $this->userRepository->delete($user);
        return ApiResponse::success('User deleted successfully.');
    }
}
