<?php

namespace App\Http\Controllers;

use App\Actions\Sales\CreateSalesInvoice;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\Sales\SalesInvoiceRequest;
use App\Http\Resources\SalesInvoiceResource;
use App\Models\DeliveryNote;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Repositories\SalesInvoiceRepository;
use App\Services\DocumentSequenceService;
use App\Services\SalesInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SalesInvoiceController extends Controller
{
    public function __construct(
        protected SalesInvoiceRepository $salesInvoiceRepository,
        protected SalesInvoiceService $salesInvoiceService,
        protected DocumentSequenceService $sequenceService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_SALES_INVOICE->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_SALES_INVOICE->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_SALES_INVOICE->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'from', 'to', 'sort_by', 'sort_direction',
            'status', 'customer_id', 'dn_id', 'date_from', 'date_to',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $invoices = $this->salesInvoiceRepository->all($paginate, $perPage, $filters, [
            'customer' => fn ($q) => $q->withTrashed(),
            'deliveryNote',
            'costCenter',
        ]);

        $result = SalesInvoiceResource::collectionWithMeta($invoices, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Sales Invoices fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(SalesInvoiceRequest $request, CreateSalesInvoice $action): JsonResponse
    {
        $data = $request->validated();
        $dn = ! empty($data['delivery_note_id']) ? DeliveryNote::find($data['delivery_note_id']) : null;
        $so = ! empty($data['sales_order_id']) ? SalesOrder::find($data['sales_order_id']) : null;

        $invoice = $action->handle($dn, $so, $data, $request->user());

        return ApiResponse::success('Sales Invoice created and posted successfully.', SalesInvoiceResource::make($invoice->load(['items', 'customer', 'deliveryNote', 'salesOrder'])), Response::HTTP_CREATED);
    }

    public function show(SalesInvoice $salesInvoice): JsonResponse
    {
        return ApiResponse::success(
            'Sales Invoice fetched successfully.',
            SalesInvoiceResource::make($salesInvoice->load(['items.item', 'customer', 'salesOrder', 'deliveryNote', 'costCenter']))
        );
    }

    public function nextInvoiceNumber(): JsonResponse
    {
        $invoiceNumber = $this->sequenceService->generateNext('sales_invoice');

        return ApiResponse::success('Next Invoice number retrieved.', ['invoice_number' => $invoiceNumber]);
    }

    public function destroy(Request $request, SalesInvoice $salesInvoice): JsonResponse
    {
        try {
            $this->salesInvoiceService->delete($salesInvoice, $request->boolean('force'));

            return ApiResponse::success($request->boolean('force') ? 'Sales Invoice permanently deleted.' : 'Sales Invoice voided successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }
}
