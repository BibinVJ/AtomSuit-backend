<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Repositories\SaleRepository;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService,
        protected SaleRepository $saleRepo
    ) {}

    public function index(Request $request)
    {
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $sales = $this->saleRepo->all($paginate, $perPage, with: [
            'items.batch',
            'items.item',
            'customer',
        ]);

        return ApiResponse::success(
            'Sales fetched successfully.',
            $paginate ? SaleResource::paginated($sales) : SaleResource::collection($sales)
        );
    }


    public function store(StoreSaleRequest $request)
    {
        $sale = $this->saleService->create($request->validated());
        return ApiResponse::success('Sale created', SaleResource::make($sale));
    }

}
