<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\PriceListExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\PriceListRequest;
use App\Http\Resources\PriceListResource;
use App\Models\PriceList;
use App\Repositories\PriceListRepository;
use App\Services\PriceListService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class PriceListController extends Controller
{
    public function __construct(
        protected PriceListRepository $priceListRepository,
        protected PriceListService $priceListService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_PRICE_LIST->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_PRICE_LIST->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_PRICE_LIST->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_PRICE_LIST->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type', 'currency_id', 'sort_by', 'sort_direction', 'trashed', 'from', 'to']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $priceLists = $this->priceListRepository->all($paginate, $perPage, $filters, [
            'currency' => fn ($q) => $q->withTrashed(),
        ]);

        $result = PriceListResource::collectionWithMeta($priceLists, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Price lists fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function show(PriceList $priceList)
    {
        $priceList->load('currency');

        return ApiResponse::success('Price list fetched successfully.', PriceListResource::make($priceList));
    }

    public function store(PriceListRequest $request)
    {
        $priceList = $this->priceListService->create($request->validated());
        $priceList->load('currency');

        return ApiResponse::success('Price list created successfully.', PriceListResource::make($priceList), Response::HTTP_CREATED);
    }

    public function update(PriceListRequest $request, PriceList $priceList)
    {
        $updatedPriceList = $this->priceListService->update($priceList, $request->validated());
        $updatedPriceList->load('currency');

        return ApiResponse::success('Price list updated successfully.', PriceListResource::make($updatedPriceList));
    }

    public function destroy(Request $request, PriceList $priceList)
    {
        $this->priceListService->delete($priceList, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Price list permanently deleted.' : 'Price list deleted successfully.');
    }

    public function export()
    {
        return Excel::download(new PriceListExport, 'price_lists_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }

    public function restore(PriceList $priceList)
    {
        $restored = $this->priceListService->restore($priceList);

        return ApiResponse::success('Price list restored successfully.', PriceListResource::make($restored));
    }
}
