<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Repositories\PurchaseRepository;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService,
        protected PurchaseRepository $purchaseRepo
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_PURCHASE->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_PURCHASE->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_PURCHASE->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_PURCHASE->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'sort_by', 'sort_direction']);
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $purchases = $this->purchaseRepo->all($paginate, $perPage, $filters, [
            'items.batch',
            'items.item',
            'vendor',
        ]);

        return ApiResponse::success(
            'Purchases fetched successfully.',
            $paginate ? PurchaseResource::paginated($purchases) : PurchaseResource::collection($purchases)
        );
    }

    public function show(Purchase $purchase)
    {
        $purchase = $this->purchaseRepo->find($purchase->id, with: [
            'items.batch',
            'items.item',
            'vendor',
        ]);

        return ApiResponse::success('Purchase fetched successfully.', PurchaseResource::make($purchase));
    }

    public function getNextInvoiceNumber()
    {
        $invoiceNumber = $this->purchaseService->getNextInvoiceNumber();

        return ApiResponse::success('Invoice Number fetched.', ['invoice_number' => $invoiceNumber]);
    }

    // todo: mak ethe batch optional and if batch not given generate one in the backend or have vendor_batch_number and system_batch_number
    public function store(StorePurchaseRequest $request)
    {
        $purchase = $this->purchaseService->create($request->validated());

        return ApiResponse::success('Purchase created', PurchaseResource::make($purchase));
    }

    public function update(StorePurchaseRequest $request, Purchase $purchase)
    {
        $updatedPurchase = $this->purchaseService->update($purchase, $request->validated());

        return ApiResponse::success('Purchase updated.', PurchaseResource::make($updatedPurchase));
    }

    public function destroy(Purchase $purchase)
    {
        $this->purchaseService->void($purchase);

        return ApiResponse::success('Purchase Voided.');
    }
}
