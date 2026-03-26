<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CreateDebitNote;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\Purchase\DebitNoteRequest;
use App\Http\Resources\DebitNoteResource;
use App\Models\DebitNote;
use App\Repositories\DebitNoteRepository;
use App\Services\DebitNoteService;
use App\Services\DocumentSequenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// Fallback
use Symfony\Component\HttpFoundation\Response;

class DebitNoteController extends Controller
{
    public function __construct(
        protected DebitNoteRepository $debitNoteRepository,
        protected DebitNoteService $debitNoteService,
        protected DocumentSequenceService $sequenceService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_DEBIT_NOTE->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_DEBIT_NOTE->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_DEBIT_NOTE->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'status', 'vendor_id', 'date_from', 'date_to',
            'from', 'to', 'sort_by', 'sort_direction',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $debitNotes = $this->debitNoteRepository->all($paginate, $perPage, $filters, [
            'vendor' => fn ($q) => $q->withTrashed(),
            'purchaseInvoice',
            'costCenter',
            'warehouse',
        ]);

        $result = DebitNoteResource::collectionWithMeta($debitNotes, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Debit Notes fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(DebitNoteRequest $request, CreateDebitNote $action): JsonResponse
    {
        $dn = $action->handle($request->validated(), $request->user());

        return ApiResponse::success(
            'Debit Note created successfully.',
            DebitNoteResource::make($dn->load(['items.item', 'vendor', 'purchaseInvoice'])),
            Response::HTTP_CREATED
        );
    }

    public function show(DebitNote $debitNote): JsonResponse
    {
        return ApiResponse::success(
            'Debit Note fetched successfully.',
            DebitNoteResource::make($debitNote->load(['items.item', 'vendor', 'purchaseInvoice', 'costCenter', 'warehouse']))
        );
    }

    public function destroy(Request $request, DebitNote $debitNote): JsonResponse
    {
        try {
            $this->debitNoteService->delete($debitNote);

            return ApiResponse::success('Debit Note voided successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    public function nextDebitNoteNumber(): JsonResponse
    {
        $dnNumber = $this->sequenceService->generateNext('debit_note');

        return ApiResponse::success('Next Debit Note number retrieved.', ['debit_note_number' => $dnNumber]);
    }
}
