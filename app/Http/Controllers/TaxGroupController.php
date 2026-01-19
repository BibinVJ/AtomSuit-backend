<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\TaxGroupExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\TaxGroupRequest;
use App\Http\Resources\TaxGroupResource;
use App\Models\TaxGroup;
use App\Repositories\TaxGroupRepository;
use App\Services\TaxGroupService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class TaxGroupController extends Controller
{
    public function __construct(
        protected TaxGroupRepository $taxGroupRepository,
        protected TaxGroupService $taxGroupService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_TAX_GROUP->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_TAX_GROUP->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_TAX_GROUP->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_TAX_GROUP->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated'));
        $perPage = $request->integer('perPage', 15);

        $taxGroups = $this->taxGroupRepository->all($paginate, $perPage, $filters, ['taxRates']);

        $result = TaxGroupResource::collectionWithMeta($taxGroups);

        return ApiResponse::success(
            'Tax groups fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function show(TaxGroup $taxGroup)
    {
        $taxGroup = $this->taxGroupRepository->find($taxGroup->id, with: ['taxRates']);

        return ApiResponse::success('Tax group fetched successfully.', TaxGroupResource::make($taxGroup));
    }

    public function store(TaxGroupRequest $request)
    {
        $taxGroup = $this->taxGroupRepository->create($request->validated());

        return ApiResponse::success('Tax group created successfully.', TaxGroupResource::make($taxGroup));
    }

    public function update(TaxGroupRequest $request, TaxGroup $taxGroup)
    {
        $updatedTaxGroup = $this->taxGroupRepository->update($taxGroup, $request->validated());

        return ApiResponse::success('Tax group updated successfully.', TaxGroupResource::make($updatedTaxGroup));
    }

    public function destroy(Request $request, TaxGroup $taxGroup)
    {
        $this->taxGroupService->delete($taxGroup, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Tax group permanently deleted.' : 'Tax group deleted successfully.');
    }

    public function restore(TaxGroup $taxGroup)
    {
        $taxGroup = $this->taxGroupService->restore($taxGroup);

        return ApiResponse::success('Tax group restored successfully.', TaxGroupResource::make($taxGroup));
    }

    public function export()
    {
        return Excel::download(new TaxGroupExport, 'tax_groups_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }
}
