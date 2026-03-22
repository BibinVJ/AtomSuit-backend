<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CreatePurchaseInvoice;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\Purchase\PurchaseInvoiceRequest;
use App\Http\Resources\PurchaseInvoiceResource;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Repositories\PurchaseInvoiceRepository;
use App\Services\PurchaseInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PurchaseInvoiceController extends Controller
{
    public function __construct(
        protected PurchaseInvoiceRepository $piRepository,
        protected PurchaseInvoiceService $piService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_PURCHASE_INVOICE->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_PURCHASE_INVOICE->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_PURCHASE_INVOICE->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_PURCHASE_INVOICE->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'from', 'to', 'sort_by', 'sort_direction',
            'status', 'vendor_id', 'grn_id', 'date_from', 'date_to',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $pis = $this->piRepository->all($paginate, $perPage, $filters, [
            'vendor' => fn ($q) => $q->withTrashed(),
            'grn',
            'costCenter',
            'warehouse',
        ]);

        $result = PurchaseInvoiceResource::collectionWithMeta($pis, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Purchase Invoices fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(PurchaseInvoiceRequest $request, CreatePurchaseInvoice $action): JsonResponse
    {
        $data = $request->validated();

        $grn = null;
        if (! empty($data['grn_id'])) {
            $grn = GoodsReceivedNote::findOrFail($data['grn_id']);
        }

        $po = null;
        if (! empty($data['purchase_order_id'])) {
            $po = PurchaseOrder::findOrFail($data['purchase_order_id']);
        }

        // Action handles direct PI vs GRN-backed PI vs PO-backed PI
        $pi = $action->handle($grn, $po, $data, $request->user());

        return ApiResponse::success('Purchase Invoice created successfully.', PurchaseInvoiceResource::make($pi->load(['items', 'vendor', 'grn', 'purchaseOrder'])), Response::HTTP_CREATED);
    }

    public function show(PurchaseInvoice $purchaseInvoice): JsonResponse
    {
        return ApiResponse::success(
            'Purchase Invoice fetched successfully.',
            PurchaseInvoiceResource::make($purchaseInvoice->load(['items.item', 'vendor', 'grn', 'costCenter', 'warehouse']))
        );
    }

    public function destroy(Request $request, PurchaseInvoice $purchaseInvoice): JsonResponse
    {
        try {
            $this->piService->delete($purchaseInvoice);

            return ApiResponse::success('Purchase Invoice voided successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    public function restore(PurchaseInvoice $purchaseInvoice): JsonResponse
    {
        $pi = $this->piService->restore($purchaseInvoice);

        return ApiResponse::success('Purchase Invoice restored successfully.', PurchaseInvoiceResource::make($pi));
    }

    public function nextInvoiceNumber(): JsonResponse
    {
        $nextId = PurchaseInvoice::max('id') + 1;
        $prefix = 'PI-'.now()->format('Ym').'-';
        $invoiceNumber = $prefix.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

        while (PurchaseInvoice::where('invoice_number', $invoiceNumber)->exists()) {
            $nextId++;
            $invoiceNumber = $prefix.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
        }

        return ApiResponse::success('Next Invoice number retrieved.', ['invoice_number' => $invoiceNumber]);
    }
}
