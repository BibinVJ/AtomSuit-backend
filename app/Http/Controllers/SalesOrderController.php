<?php

namespace App\Http\Controllers;

use App\Actions\Sales\CreateSalesOrder;
use App\Actions\Sales\UpdateSalesOrder;
use App\Actions\Sales\UpdateSalesOrderStatus;
use App\Enums\PermissionsEnum;
use App\Enums\SalesOrderStatus;
use App\Helpers\ApiResponse;
use App\Http\Requests\Sales\SalesOrderRequest;
use App\Http\Requests\Sales\UpdateSalesOrderStatusRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use App\Repositories\SalesOrderRepository;
use App\Services\DocumentSequenceService;
use App\Services\SalesOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SalesOrderController extends Controller
{
    public function __construct(
        protected SalesOrderRepository $salesOrderRepository,
        protected SalesOrderService $salesOrderService,
        protected DocumentSequenceService $sequenceService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_SALES_ORDER->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_SALES_ORDER->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_SALES_ORDER->value)->only(['update', 'updateStatus', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_SALES_ORDER->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'from', 'to', 'sort_by', 'sort_direction',
            'status', 'customer_id', 'date_from', 'date_to',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $salesOrders = $this->salesOrderRepository->all($paginate, $perPage, $filters, [
            'customer' => fn ($q) => $q->withTrashed(),
            'costCenter',
            'warehouse',
        ]);

        $result = SalesOrderResource::collectionWithMeta($salesOrders, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Sales Orders fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(SalesOrderRequest $request, CreateSalesOrder $action): JsonResponse
    {
        $so = $action->handle($request->validated(), $request->user());

        return ApiResponse::success('Sales Order created successfully.', SalesOrderResource::make($so->load(['items', 'customer'])), Response::HTTP_CREATED);
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        return ApiResponse::success(
            'Sales Order fetched successfully.',
            SalesOrderResource::make($salesOrder->load(['items.item', 'customer', 'costCenter', 'warehouse', 'deliveryNotes', 'salesInvoices']))
        );
    }

    public function update(SalesOrderRequest $request, SalesOrder $salesOrder, UpdateSalesOrder $action): JsonResponse
    {
        $so = $action->handle($salesOrder, $request->validated(), $request->user());

        return ApiResponse::success('Sales Order updated successfully.', SalesOrderResource::make($so->load(['items', 'customer'])));
    }

    public function destroy(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        try {
            $this->salesOrderService->delete($salesOrder, $request->boolean('force'));

            return ApiResponse::success($request->boolean('force') ? 'Sales Order permanently deleted.' : 'Sales Order deleted successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    public function restore(SalesOrder $salesOrder): JsonResponse
    {
        $so = $this->salesOrderService->restore($salesOrder);

        return ApiResponse::success('Sales Order restored successfully.', SalesOrderResource::make($so));
    }

    public function updateStatus(UpdateSalesOrderStatusRequest $request, SalesOrder $salesOrder, UpdateSalesOrderStatus $action): JsonResponse
    {
        $status = SalesOrderStatus::from($request->input('status'));

        $so = $action->handle($salesOrder, $status, $request->user());

        return ApiResponse::success('Sales Order status updated successfully.', SalesOrderResource::make($so));
    }

    public function nextOrderNumber(): JsonResponse
    {
        $orderNumber = $this->sequenceService->generateNext('sales_order');

        return ApiResponse::success('Next order number retrieved.', ['order_number' => $orderNumber]);
    }
}
