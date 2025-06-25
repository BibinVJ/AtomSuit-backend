<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Services\PurchaseService;
use App\Repositories\PurchaseRepository;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService,
        protected PurchaseRepository $purchaseRepo
    ) {}

    public function index(Request $request)
    {
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $purchases = $this->purchaseRepo->all($paginate, $perPage, with: [
            'items.batch',
            'items.item',
            'vendor',
        ]);

        return ApiResponse::success(
            'Purchases fetched successfully.',
            $paginate ? PurchaseResource::paginated($purchases) : PurchaseResource::collection($purchases)
        );
    }


    public function store(StorePurchaseRequest $request)
    {
        $purchase = $this->purchaseService->create($request->validated());
        return ApiResponse::success('Purchase created', PurchaseResource::make($purchase));
    }

}
