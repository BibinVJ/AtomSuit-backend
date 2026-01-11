<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\TaxRateRequest;
use App\Http\Resources\TaxRateResource;
use App\Models\TaxRate;
use App\Repositories\TaxRateRepository;
use App\Services\TaxRateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaxRateController extends Controller
{
    public function __construct(
        protected TaxRateRepository $taxRateRepository,
        protected TaxRateService $taxRateService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_TAX_RATE->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_TAX_RATE->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_TAX_RATE->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_TAX_RATE->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated'));
        $perPage = $request->integer('perPage', 15);

        $taxRates = $this->taxRateRepository->all($paginate, $perPage, $filters, [
            'salesAccount', 'purchaseAccount',
        ]);

        $result = TaxRateResource::collectionWithMeta($taxRates);

        return ApiResponse::success(
            'Tax rates fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function show(TaxRate $taxRate)
    {
        $taxRate = $this->taxRateRepository->find($taxRate->id, with: ['salesAccount', 'purchaseAccount']);

        return ApiResponse::success('Tax rate fetched successfully.', TaxRateResource::make($taxRate));
    }

    public function store(TaxRateRequest $request)
    {
        $taxRate = $this->taxRateRepository->create($request->validated());

        return ApiResponse::success('Tax rate created successfully.', TaxRateResource::make($taxRate));
    }

    public function update(TaxRateRequest $request, TaxRate $taxRate)
    {
        $updatedTaxRate = $this->taxRateRepository->update($taxRate, $request->validated());

        return ApiResponse::success('Tax rate updated successfully.', TaxRateResource::make($updatedTaxRate));
    }

    public function destroy(Request $request, TaxRate $taxRate)
    {
        $this->taxRateService->delete($taxRate, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Tax rate permanently deleted.' : 'Tax rate deleted successfully.');
    }

    public function restore(TaxRate $taxRate)
    {
        $taxRate = $this->taxRateService->restore($taxRate);

        return ApiResponse::success('Tax rate restored successfully.', TaxRateResource::make($taxRate));
    }
}
