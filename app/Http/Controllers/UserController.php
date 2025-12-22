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
        $this->middleware('permission:'.PermissionsEnum::UPDATE_USER->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_USER->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search', 'is_not_admin', 'role', 'from', 'to', 'sort_by', 'sort_direction', 'trashed']);
        $filters['exclude_current'] = true;
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $users = $this->userRepository->all($paginate, $perPage, $filters, [
            'roles' => fn ($q) => $q->withTrashed(),
        ]);

        $result = UserResource::collectionWithMeta($users, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Users fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
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

    public function destroy(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return ApiResponse::error('You cannot delete your own account.', Response::HTTP_FORBIDDEN);
        }

        $this->userService->delete($user, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'User permanently deleted.' : 'User deleted successfully.');
    }

    public function restore(User $user)
    {
        $user = $this->userService->restore($user);

        return ApiResponse::success('User restored successfully.', UserResource::make($user));
    }

    public function sendMail(UserSendMailRequest $request, User $user)
    {
        $this->sendUserMailAction->execute($user, $request->validated()['subject'], $request->validated()['body']);

        return ApiResponse::success('Mail sent successfully.');
    }
}
