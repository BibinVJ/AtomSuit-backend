<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CreatePurchaseOrder;
use App\Actions\Purchase\UpdatePurchaseOrder;
use App\Actions\Purchase\UpdatePurchaseOrderStatus;
use App\Enums\PermissionsEnum;
use App\Enums\PurchaseOrderStatus;
use App\Helpers\ApiResponse;
use App\Http\Requests\Purchase\PurchaseOrderRequest;
use App\Http\Requests\Purchase\UpdatePurchaseOrderStatusRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Repositories\PurchaseOrderRepository;
use App\Services\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseOrderRepository $purchaseOrderRepository,
        protected PurchaseOrderService $purchaseOrderService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_PURCHASE_ORDER->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_PURCHASE_ORDER->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_PURCHASE_ORDER->value)->only(['update', 'confirm', 'updateStatus', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_PURCHASE_ORDER->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'from', 'to', 'sort_by', 'sort_direction',
            'status', 'vendor_id', 'date_from', 'date_to',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $purchaseOrders = $this->purchaseOrderRepository->all($paginate, $perPage, $filters, [
            'vendor' => fn ($q) => $q->withTrashed(),
            'costCenter',
            'warehouse',
        ]);

        $result = PurchaseOrderResource::collectionWithMeta($purchaseOrders, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Purchase Orders fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(PurchaseOrderRequest $request, CreatePurchaseOrder $action): JsonResponse
    {
        $po = $action->handle($request->validated(), $request->user());

        return ApiResponse::success('Purchase Order created successfully.', PurchaseOrderResource::make($po->load(['items', 'vendor'])), Response::HTTP_CREATED);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return ApiResponse::success(
            'Purchase Order fetched successfully.',
            PurchaseOrderResource::make($purchaseOrder->load(['items.item', 'vendor', 'costCenter', 'warehouse', 'goodsReceivedNotes']))
        );
    }

    public function update(PurchaseOrderRequest $request, PurchaseOrder $purchaseOrder, UpdatePurchaseOrder $action): JsonResponse
    {
        $po = $action->handle($purchaseOrder, $request->validated(), $request->user());

        return ApiResponse::success('Purchase Order updated successfully.', PurchaseOrderResource::make($po->load(['items', 'vendor'])));
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        try {
            $this->purchaseOrderService->delete($purchaseOrder, $request->boolean('force'));

            return ApiResponse::success($request->boolean('force') ? 'Purchase Order permanently deleted.' : 'Purchase Order deleted successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    public function restore(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $po = $this->purchaseOrderService->restore($purchaseOrder);

        return ApiResponse::success('Purchase Order restored successfully.', PurchaseOrderResource::make($po));
    }

    public function updateStatus(UpdatePurchaseOrderStatusRequest $request, PurchaseOrder $purchaseOrder, UpdatePurchaseOrderStatus $action): JsonResponse
    {
        $status = PurchaseOrderStatus::from($request->input('status'));

        $po = $action->handle($purchaseOrder, $status, $request->user());

        return ApiResponse::success('Purchase Order status updated successfully.', PurchaseOrderResource::make($po));
    }

    public function nextOrderNumber(): JsonResponse
    {
        // Simple logic: PO-{LatestID + 1} or user provided logic.
        // For now, let's find the max order_number or just id.
        // A common pattern is PO-YYYYMM-XXXX

        $latest = PurchaseOrder::latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $orderNumber = 'PO-'.str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);

        // Ensure uniqueness just in case (optional, but good practice)
        while (PurchaseOrder::where('order_number', $orderNumber)->exists()) {
            $nextId++;
            $orderNumber = 'PO-'.str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
        }

        return ApiResponse::success('Next order number retrieved.', ['order_number' => $orderNumber]);
    }
}
