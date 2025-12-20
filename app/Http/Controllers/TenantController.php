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
        $this->middleware('permission:'.PermissionsEnum::VIEW_TENANT->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_TENANT->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_TENANT->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_TENANT->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction', 'plan_id']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $tenants = $this->tenantRepository->all($paginate, $perPage, $filters, ['domain', 'currentSubscription.plan', 'plan']);

        $result = TenantResource::collectionWithMeta($tenants, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Tenants fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
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
        $stats = $this->tenantService->getStats();

        return ApiResponse::success(
            'Tenant statistics fetched successfully.',
            $stats,
            Response::HTTP_OK
        );
    }

    public function sendMail(UserSendMailRequest $request, Tenant $tenant)
    {
        $this->sendTenantMailAction->execute($tenant, $request->validated()['subject'], $request->validated()['body']);

        return ApiResponse::success('Mail sent successfully.');
    }
}
