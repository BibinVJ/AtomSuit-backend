<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Repositories\SaleRepository;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService,
        protected SaleRepository $saleRepo
    ) {
        $this->middleware("permission:" . PermissionsEnum::VIEW_SALE->value)->only(['index']);
        $this->middleware("permission:" . PermissionsEnum::VIEW_SALE->value)->only(['show']);
        $this->middleware("permission:" . PermissionsEnum::CREATE_SALE->value)->only(['store']);
        $this->middleware("permission:" . PermissionsEnum::UPDATE_SALE->value)->only(['update']);
        $this->middleware("permission:" . PermissionsEnum::DELETE_SALE->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'sort_by', 'sort_direction']);
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $sales = $this->saleRepo->all($paginate, $perPage, $filters, ['items.item','customer']);

        return ApiResponse::success(
            'Sales fetched successfully.',
            $paginate ? SaleResource::paginated($sales) : SaleResource::collection($sales)
        );
    }

    public function show(Sale $sale)
    {
        $sale = $this->saleRepo->find($sale->id, with: [
            'items.item',
            'customer',
        ]);
        return ApiResponse::success('Sale fetched successfully.', SaleResource::make($sale));
    }

    public function store(StoreSaleRequest $request)
    {
        $sale = $this->saleService->create($request->validated());
        return ApiResponse::success('Sale created', SaleResource::make($sale));
    }

    public function update(StoreSaleRequest $request, Sale $sale)
    {
        $updatedSale = $this->saleService->update($sale, $request->validated());
        return ApiResponse::success('Sale updated.', SaleResource::make($updatedSale));
    }

    public function destroy(Sale $sale)
    {
        $this->saleService->void($sale);
        return ApiResponse::success('Sale Voided.');
    }
}
