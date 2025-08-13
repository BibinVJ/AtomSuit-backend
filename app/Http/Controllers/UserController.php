<?php

namespace App\Http\Controllers;

use App\Actions\SendUserMailAction;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserSendMailRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UserService $userService,
        protected SendUserMailAction $sendUserMailAction
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_USER->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_USER->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_USER->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_USER->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'is_not_admin', 'role', 'sort_by', 'sort_direction']);
        $filters['exclude_current'] = true;
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $users = $this->userRepository->all($paginate, $perPage, $filters);

        if ($paginate) {
            $paginated = UserResource::paginated($users);

            return ApiResponse::success(
                'Users fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Users fetched successfully.',
            UserResource::collection($users),
            Response::HTTP_OK,
            ['total' => count($users)]
        );
    }

    public function store(UserRequest $request)
    {
        $user = $this->userService->create($request->validated());

        return ApiResponse::success('User created successfully.', UserResource::make($user));
    }

    public function update(UserRequest $request, User $user)
    {
        $updatedUser = $this->userService->update($user, $request->validated());

        return ApiResponse::success('User updated successfully.', UserResource::make($updatedUser));
    }

    public function destroy(User $user)
    {
        $this->userService->delete($user);

        return ApiResponse::success('User deleted successfully.');
    }

    public function sendMail(UserSendMailRequest $request, User $user)
    {
        $this->sendUserMailAction->execute($user, $request->validated()['subject'], $request->validated()['body']);

        return ApiResponse::success('Mail sent successfully.');
    }
}
