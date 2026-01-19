<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Resources\UserLoginDetailResource;
use App\Repositories\UserLoginDetailRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserLoginDetailController extends Controller
{
    public function __construct(protected UserLoginDetailRepository $repository)
    {
        $this->middleware('permission:'.PermissionsEnum::VIEW_USER_LOGIN_DETAILS->value)->only(['index']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'user_id', 'sort_by', 'sort_direction', 'perPage', 'unpaginated', 'from', 'to', 'role']);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $logs = $this->repository->all($paginate, $perPage, $filters, [
            'user' => fn ($q) => $q->withTrashed(),
        ]);

        $result = UserLoginDetailResource::collectionWithMeta($logs, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'User login details fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }
}
