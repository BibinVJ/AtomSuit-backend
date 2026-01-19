<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\ItemPriceExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\ItemPriceRequest;
use App\Http\Resources\ItemPriceResource;
use App\Models\ItemPrice;
use App\Repositories\ItemPriceRepository;
use App\Services\ItemPriceService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ItemPriceController extends Controller
{
    public function __construct(
        protected ItemPriceRepository $itemPriceRepository,
        protected ItemPriceService $itemPriceService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_ITEM_PRICE->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_ITEM_PRICE->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_ITEM_PRICE->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_ITEM_PRICE->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'price_list_id', 'item_id', 'sort_by', 'sort_direction', 'trashed', 'from', 'to']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $itemPrices = $this->itemPriceRepository->all($paginate, $perPage, $filters, ['item', 'priceList']);

        $result = ItemPriceResource::collectionWithMeta($itemPrices, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Item prices fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function show(ItemPrice $itemPrice)
    {
        $itemPrice->load(['item', 'priceList']);

        return ApiResponse::success('Item price fetched successfully.', ItemPriceResource::make($itemPrice));
    }

    public function store(ItemPriceRequest $request)
    {
        $itemPrice = $this->itemPriceService->create($request->validated());
        $itemPrice->load(['item', 'priceList']);

        return ApiResponse::success('Item price created successfully.', ItemPriceResource::make($itemPrice), Response::HTTP_CREATED);
    }

    public function update(ItemPriceRequest $request, ItemPrice $itemPrice)
    {
        $updated = $this->itemPriceService->update($itemPrice, $request->validated());
        $updated->load(['item', 'priceList']);

        return ApiResponse::success('Item price updated successfully.', ItemPriceResource::make($updated));
    }

    public function destroy(Request $request, ItemPrice $itemPrice)
    {
        $this->itemPriceService->delete($itemPrice, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Item price permanently deleted.' : 'Item price deleted successfully.');
    }

    public function export()
    {
        return Excel::download(new ItemPriceExport, 'item_prices_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }

    public function restore(ItemPrice $itemPrice)
    {
        $restored = $this->itemPriceService->restore($itemPrice);

        return ApiResponse::success('Item price restored successfully.', ItemPriceResource::make($restored));
    }
}
