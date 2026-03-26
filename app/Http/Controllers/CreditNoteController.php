<?php

namespace App\Http\Controllers;

use App\Actions\Sales\CreateCreditNote;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\Sales\CreditNoteRequest;
use App\Http\Resources\CreditNoteResource;
use App\Models\CreditNote;
use App\Models\SalesInvoice;
use App\Repositories\CreditNoteRepository;
use App\Services\CreditNoteService;
use App\Services\DocumentSequenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CreditNoteController extends Controller
{
    public function __construct(
        protected CreditNoteRepository $creditNoteRepository,
        protected CreditNoteService $creditNoteService,
        protected DocumentSequenceService $sequenceService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_CREDIT_NOTE->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_CREDIT_NOTE->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_CREDIT_NOTE->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'from', 'to', 'sort_by', 'sort_direction',
            'status', 'customer_id', 'invoice_id', 'date_from', 'date_to',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $notes = $this->creditNoteRepository->all($paginate, $perPage, $filters, [
            'customer' => fn ($q) => $q->withTrashed(),
            'salesInvoice',
            'costCenter',
        ]);

        $result = CreditNoteResource::collectionWithMeta($notes, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Credit Notes fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(CreditNoteRequest $request, CreateCreditNote $action): JsonResponse
    {
        $data = $request->validated();
        $invoice = ! empty($data['sales_invoice_id']) ? SalesInvoice::find($data['sales_invoice_id']) : null;

        $cn = $action->handle($invoice, $data, $request->user());

        return ApiResponse::success('Credit Note created successfully.', CreditNoteResource::make($cn->load(['items', 'customer', 'salesInvoice'])), Response::HTTP_CREATED);
    }

    public function show(CreditNote $creditNote): JsonResponse
    {
        return ApiResponse::success(
            'Credit Note fetched successfully.',
            CreditNoteResource::make($creditNote->load(['items.item', 'customer', 'salesInvoice', 'costCenter', 'warehouse']))
        );
    }

    public function destroy(Request $request, CreditNote $creditNote): JsonResponse
    {
        try {
            $this->creditNoteService->delete($creditNote);

            return ApiResponse::success('Credit Note voided successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    public function nextCreditNoteNumber(): JsonResponse
    {
        $cnNumber = $this->sequenceService->generateNext('credit_note');

        return ApiResponse::success('Next Credit Note number retrieved.', ['credit_note_number' => $cnNumber]);
    }
}
