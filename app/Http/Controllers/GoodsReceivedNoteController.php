<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CreateGoodsReceivedNote;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\Purchase\GoodsReceivedNoteRequest;
use App\Http\Resources\GoodsReceivedNoteResource;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Repositories\GoodsReceivedNoteRepository;
use App\Services\GoodsReceivedNoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GoodsReceivedNoteController extends Controller
{
    public function __construct(
        protected GoodsReceivedNoteRepository $grnRepository,
        protected GoodsReceivedNoteService $grnService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_GRN->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_GRN->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_GRN->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_GRN->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'from', 'to', 'sort_by', 'sort_direction',
            'status', 'vendor_id', 'purchase_order_id', 'date_from', 'date_to',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $grns = $this->grnRepository->all($paginate, $perPage, $filters, [
            'vendor' => fn ($q) => $q->withTrashed(),
            'purchaseOrder',
            'costCenter',
            'warehouse',
        ]);

        $result = GoodsReceivedNoteResource::collectionWithMeta($grns, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Goods Received Notes fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(GoodsReceivedNoteRequest $request, CreateGoodsReceivedNote $action): JsonResponse
    {
        $data = $request->validated();

        $po = null;
        if (! empty($data['purchase_order_id'])) {
            $po = PurchaseOrder::findOrFail($data['purchase_order_id']);
        }

        $grn = $action->handle($po, $data, $request->user());

        return ApiResponse::success('Goods Received Note created successfully.', GoodsReceivedNoteResource::make($grn->load(['items', 'vendor', 'purchaseOrder'])), Response::HTTP_CREATED);
    }

    public function show(GoodsReceivedNote $goodsReceivedNote): JsonResponse
    {
        return ApiResponse::success(
            'Goods Received Note fetched successfully.',
            GoodsReceivedNoteResource::make($goodsReceivedNote->load(['items.item', 'vendor', 'purchaseOrder', 'costCenter', 'warehouse']))
        );
    }

    public function destroy(Request $request, GoodsReceivedNote $goodsReceivedNote): JsonResponse
    {
        try {
            $this->grnService->delete($goodsReceivedNote);

            return ApiResponse::success('Goods Received Note voided successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    public function nextGrnNumber(): JsonResponse
    {
        $nextId = GoodsReceivedNote::max('id') + 1;
        $prefix = 'GRN-'.now()->format('Ym').'-';
        $grnNumber = $prefix.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

        while (GoodsReceivedNote::where('grn_number', $grnNumber)->exists()) {
            $nextId++;
            $grnNumber = $prefix.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
        }

        return ApiResponse::success('Next GRN number retrieved.', ['grn_number' => $grnNumber]);
    }
}
