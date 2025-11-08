<?php

namespace App\Http\Controllers;

use App\Actions\SendTenantMailAction;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\TenantRequest;
use App\Http\Requests\UserSendMailRequest;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Repositories\TenantRepository;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantController extends Controller
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected TenantService $tenantService,
        protected SendTenantMailAction $sendTenantMailAction
    ) {
        $this->middleware('permission:' . PermissionsEnum::VIEW_TENANT->value)->only(['index']);
        $this->middleware('permission:' . PermissionsEnum::CREATE_TENANT->value)->only(['store']);
        $this->middleware('permission:' . PermissionsEnum::UPDATE_TENANT->value)->only(['update']);
        $this->middleware('permission:' . PermissionsEnum::DELETE_TENANT->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_by', 'sort_direction']);
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $tenants = $this->tenantRepository->all($paginate, $perPage, $filters, ['domain', 'subscriptions']);

        if ($paginate) {
            $paginated = TenantResource::paginated($tenants);

            return ApiResponse::success(
                'Tenants fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Tenants fetched successfully.',
            TenantResource::collection($tenants),
            Response::HTTP_OK,
            ['total' => count($tenants)]
        );
    }

    public function store(TenantRequest $request)
    {
        $tenant = $this->tenantService->create($request->validated());

        return ApiResponse::success('Tenant created successfully.', TenantResource::make($tenant));
    }

    public function destroy(Tenant $tenant)
    {
        $this->tenantService->delete($tenant);

        return ApiResponse::success('Tenant deleted successfully.');
    }

    public function stats()
    {
        $data = $this->tenantService->getStats();

        return ApiResponse::success('Tenant stats fetched.', meta: $data);
    }

    public function sendMail(UserSendMailRequest $request, Tenant $tenant)
    {
        $this->sendTenantMailAction->execute($tenant, $request->validated()['subject'], $request->validated()['body']);

        return ApiResponse::success('Mail sent successfully.');
    }
}