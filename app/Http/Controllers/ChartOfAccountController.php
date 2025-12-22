<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\ChartOfAccountExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\ChartOfAccountRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Imports\ChartOfAccountImport;
use App\Models\ChartOfAccount;
use App\Repositories\ChartOfAccountRepository;
use App\Services\ChartOfAccountService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ChartOfAccountController extends Controller
{
    public function __construct(
        protected ChartOfAccountRepository $chartOfAccountRepository,
        protected ChartOfAccountService $chartOfAccountService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_CHART_OF_ACCOUNT->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_CHART_OF_ACCOUNT->value)->only(['store', 'import']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_CHART_OF_ACCOUNT->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_CHART_OF_ACCOUNT->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'account_group_id', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $chartOfAccounts = $this->chartOfAccountRepository->all($paginate, $perPage, $filters, [
            'accountGroup' => fn ($q) => $q->withTrashed(),
        ]);

        $result = ChartOfAccountResource::collectionWithMeta($chartOfAccounts, [
            'account_group_id' => $filters['account_group_id'] ?? null,
        ]);

        return ApiResponse::success(
            'Chart of Accounts retrieved successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(ChartOfAccountRequest $request)
    {
        $chartOfAccount = $this->chartOfAccountRepository->create($request->validated());

        return ApiResponse::success('Chart of Account created successfully.', ChartOfAccountResource::make($chartOfAccount));
    }

    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load(['accountGroup' => fn ($q) => $q->withTrashed()]);

        return ApiResponse::success('Chart of Account retrieved successfully.', ChartOfAccountResource::make($chartOfAccount));
    }

    public function update(ChartOfAccountRequest $request, ChartOfAccount $chartOfAccount)
    {
        $updatedChartOfAccount = $this->chartOfAccountRepository->update($chartOfAccount, $request->validated());

        return ApiResponse::success('Chart of Account updated successfully.', ChartOfAccountResource::make($updatedChartOfAccount));
    }

    public function destroy(Request $request, ChartOfAccount $chartOfAccount)
    {
        $this->chartOfAccountService->delete($chartOfAccount, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Chart of Account permanently deleted.' : 'Chart of Account deleted successfully.');
    }

    public function restore(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount = $this->chartOfAccountService->find($chartOfAccount->id, true);
        $restored = $this->chartOfAccountService->restore($chartOfAccount);

        return ApiResponse::success('Chart of Account restored successfully.', ChartOfAccountResource::make($restored));
    }

    public function import(ImportRequest $request)
    {
        Excel::import(new ChartOfAccountImport, $request->file('file'));

        return ApiResponse::success('Chart of Accounts imported successfully.');
    }

    public function export()
    {
        return Excel::download(new ChartOfAccountExport, 'chart_of_accounts_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }
}
