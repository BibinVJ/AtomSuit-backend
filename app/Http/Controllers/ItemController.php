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

class ItemController extends Controller
{
    public function __construct(
        protected ItemRepository $itemRepo,
        protected ItemService $itemService
    ) {
        $this->middleware("permission:" . PermissionsEnum::VIEW_ITEM->value)->only(['index']);
        $this->middleware("permission:" . PermissionsEnum::CREATE_ITEM->value)->only(['store']);
        $this->middleware("permission:" . PermissionsEnum::UPDATE_ITEM->value)->only(['update']);
        $this->middleware("permission:" . PermissionsEnum::DELETE_ITEM->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'sort_by', 'sort_direction']);
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $items = $this->itemRepo->all($paginate, $perPage, $filters, ['category', 'unit', 'stockMovements']);

        return ApiResponse::success(
            'Items fetched successfully.',
            $paginate ? ItemResource::paginated($items) : ItemResource::collection($items)
        );
    }

    public function store(ItemRequest $request)
    {
        $item = $this->itemRepo->create($request->validated());
        return ApiResponse::success('Item created successfully.', ItemResource::make($item));
    }

    public function update(ItemRequest $request, Item $item)
    {
        $updatedItem = $this->itemRepo->update($item, $request->validated());
        return ApiResponse::success('Item updated successfully.', ItemResource::make($updatedItem));
    }

    public function destroy(Item $item)
    {
        $this->itemService->ensureItemIsDeletable($item);
        $this->itemRepo->delete($item);
        return ApiResponse::success('Item deleted successfully.');
    }
}
