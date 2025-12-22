<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\AccountGroupExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\AccountGroupRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\AccountGroupResource;
use App\Imports\AccountGroupImport;
use App\Models\AccountGroup;
use App\Repositories\AccountGroupRepository;
use App\Services\AccountGroupService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class AccountGroupController extends Controller
{
    public function __construct(
        protected AccountGroupRepository $accountGroupRepository,
        protected AccountGroupService $accountGroupService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_ACCOUNT_GROUP->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_ACCOUNT_GROUP->value)->only(['store', 'import']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_ACCOUNT_GROUP->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_ACCOUNT_GROUP->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        // Standard filters and pagination logic matching CategoryController
        $filters = $request->only(['search', 'account_type_id', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $accountGroups = $this->accountGroupRepository->all($paginate, $perPage, $filters, [
            'accountType',
            'parent' => fn ($q) => $q->withTrashed(),
        ]);

        $result = AccountGroupResource::collectionWithMeta($accountGroups, [
            'account_type_id' => $filters['account_type_id'] ?? null,
        ]);

        return ApiResponse::success(
            'Account Groups retrieved successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(AccountGroupRequest $request)
    {
        $accountGroup = $this->accountGroupRepository->create($request->validated());

        return ApiResponse::success('Account Group created successfully.', AccountGroupResource::make($accountGroup));
    }

    public function show(AccountGroup $accountGroup)
    {
        $accountGroup->load([
            'accountType',
            'parent' => fn ($q) => $q->withTrashed(),
        ]);

        return ApiResponse::success('Account Group retrieved successfully.', AccountGroupResource::make($accountGroup));
    }

    public function update(AccountGroupRequest $request, AccountGroup $accountGroup)
    {
        $updatedAccountGroup = $this->accountGroupRepository->update($accountGroup, $request->validated());

        return ApiResponse::success('Account Group updated successfully.', AccountGroupResource::make($updatedAccountGroup));
    }

    public function destroy(Request $request, AccountGroup $accountGroup)
    {
        $this->accountGroupService->delete($accountGroup, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Account Group permanently deleted.' : 'Account Group deleted successfully.');
    }

    public function restore(AccountGroup $accountGroup)
    {
        $accountGroup = $this->accountGroupService->find($accountGroup->id, true);
        $restored = $this->accountGroupService->restore($accountGroup);

        return ApiResponse::success('Account Group restored successfully.', AccountGroupResource::make($restored));
    }

    public function import(ImportRequest $request)
    {
        Excel::import(new AccountGroupImport, $request->file('file'));

        return ApiResponse::success('Account Groups imported successfully.');
    }

    public function export()
    {
        return Excel::download(new AccountGroupExport, 'account_groups_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }
}
