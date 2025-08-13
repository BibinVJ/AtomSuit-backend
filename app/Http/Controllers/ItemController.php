<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Repositories\ItemRepository;
use App\Services\ItemService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function __construct(
        protected ItemRepository $itemRepository,
        protected ItemService $itemService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_ITEM->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_ITEM->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_ITEM->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_ITEM->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'sort_by', 'sort_direction']);
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $items = $this->itemRepository->all($paginate, $perPage, $filters, ['category', 'unit', 'stockMovements']);

        if ($paginate) {
            $paginated = ItemResource::paginated($items);

            return ApiResponse::success(
                'Items fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Items fetched successfully.',
            ItemResource::collection($items),
            Response::HTTP_OK,
            ['total' => count($items)]
        );
    }

    public function show(Item $item)
    {
        $item = $this->itemRepository->find($item->id, with: [
            'category',
            'unit',
            'stockMovements',
            'batches',
        ]);

        return ApiResponse::success('Item fetched successfully.', ItemResource::make($item));
    }

    public function store(ItemRequest $request)
    {
        $item = $this->itemRepository->create($request->validated());

        return ApiResponse::success('Item created successfully.', ItemResource::make($item));
    }

    public function update(ItemRequest $request, Item $item)
    {
        $updatedItem = $this->itemRepository->update($item, $request->validated());

        return ApiResponse::success('Item updated successfully.', ItemResource::make($updatedItem));
    }

    public function destroy(Item $item)
    {
        $this->itemService->delete($item);

        return ApiResponse::success('Item deleted successfully.');
    }
}
