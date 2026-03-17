<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\ItemExport;
use App\Exports\ItemSampleExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemResource;
use App\Imports\ItemImport;
use App\Models\Item;
use App\Repositories\ItemRepository;
use App\Services\ItemService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function __construct(
        protected ItemRepository $itemRepository,
        protected ItemService $itemService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_ITEM->value)->only(['index', 'export']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_ITEM->value)->only(['store', 'import']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_ITEM->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_ITEM->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'unit_id', 'type', 'from', 'to', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $items = $this->itemRepository->all($paginate, $perPage, $filters, [
            'category' => fn ($q) => $q->withTrashed(),
            'unit' => fn ($q) => $q->withTrashed(),
            'stockMovements',
            'salesAccount' => fn ($q) => $q->withTrashed(),
            'cogsAccount' => fn ($q) => $q->withTrashed(),
            'inventoryAccount' => fn ($q) => $q->withTrashed(),
            'inventoryAdjustmentAccount' => fn ($q) => $q->withTrashed(),
            'taxGroup' => fn ($q) => $q->withTrashed()->with('taxRates'),
            'itemPrices.priceList' => fn ($q) => $q->withTrashed(),
        ]);

        $result = ItemResource::collectionWithMeta($items, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Items fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function show(Item $item)
    {
        $item = $this->itemRepository->find($item->id, with: [
            'category' => fn ($q) => $q->withTrashed(),
            'unit' => fn ($q) => $q->withTrashed(),
            'stockMovements',
            'batches',
            'salesAccount',
            'cogsAccount',
            'inventoryAccount',
            'inventoryAdjustmentAccount',
            'taxGroup' => fn ($q) => $q->withTrashed()->with('taxRates'),
            'itemPrices.priceList',
        ]);

        return ApiResponse::success('Item fetched successfully.', ItemResource::make($item));
    }

    public function store(ItemRequest $request)
    {
        $item = $this->itemService->create($request->validated());

        return ApiResponse::success('Item created successfully.', ItemResource::make($item));
    }

    public function update(ItemRequest $request, Item $item)
    {
        $updatedItem = $this->itemService->update($item, $request->validated());

        return ApiResponse::success('Item updated successfully.', ItemResource::make($updatedItem));
    }

    public function destroy(Request $request, Item $item)
    {
        $this->itemService->delete($item, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Item permanently deleted.' : 'Item deleted successfully.');
    }

    public function restore(Item $item)
    {
        $item = $this->itemService->restore($item);

        return ApiResponse::success('Item restored successfully.', ItemResource::make($item));
    }

    public function export()
    {
        return Excel::download(new ItemExport, 'items_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }

    public function import(ImportRequest $request)
    {
        Excel::import(new ItemImport, $request->file('file'));

        return ApiResponse::success('Items imported successfully.');
    }

    public function downloadSample()
    {
        return Excel::download(new ItemSampleExport, 'sample_items.xlsx');
    }
}
