<?php

namespace App\Http\Controllers;

use App\Actions\Sales\CreateDeliveryNote;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\Sales\DeliveryNoteRequest;
use App\Http\Resources\DeliveryNoteResource;
use App\Models\DeliveryNote;
use App\Models\SalesOrder;
use App\Repositories\DeliveryNoteRepository;
use App\Services\DeliveryNoteService;
use App\Services\DocumentSequenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeliveryNoteController extends Controller
{
    public function __construct(
        protected DeliveryNoteRepository $deliveryNoteRepository,
        protected DeliveryNoteService $deliveryNoteService,
        protected DocumentSequenceService $sequenceService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_DELIVERY_NOTE->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_DELIVERY_NOTE->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_DELIVERY_NOTE->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'from', 'to', 'sort_by', 'sort_direction',
            'status', 'customer_id', 'sales_order_id', 'date_from', 'date_to',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $notes = $this->deliveryNoteRepository->all($paginate, $perPage, $filters, [
            'customer' => fn ($q) => $q->withTrashed(),
            'salesOrder',
            'costCenter',
            'warehouse',
        ]);

        $result = DeliveryNoteResource::collectionWithMeta($notes, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Delivery Notes fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(DeliveryNoteRequest $request, CreateDeliveryNote $action): JsonResponse
    {
        $data = $request->validated();
        $so = ! empty($data['sales_order_id']) ? SalesOrder::find($data['sales_order_id']) : null;
        $dn = $action->handle($so, $data, $request->user());

        return ApiResponse::success('Delivery Note created successfully.', DeliveryNoteResource::make($dn->load(['items', 'customer', 'salesOrder'])), Response::HTTP_CREATED);
    }

    public function show(DeliveryNote $deliveryNote): JsonResponse
    {
        return ApiResponse::success(
            'Delivery Note fetched successfully.',
            DeliveryNoteResource::make($deliveryNote->load(['items.item', 'customer', 'salesOrder', 'costCenter', 'warehouse']))
        );
    }

    public function destroy(Request $request, DeliveryNote $deliveryNote): JsonResponse
    {
        try {
            $this->deliveryNoteService->delete($deliveryNote);

            return ApiResponse::success('Delivery Note voided successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    public function nextDnNumber(): JsonResponse
    {
        $dnNumber = $this->sequenceService->generateNext('delivery_note');

        return ApiResponse::success('Next DN number retrieved.', ['dn_number' => $dnNumber]);
    }
}
