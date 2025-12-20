<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\CurrencyExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\CurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class CurrencyController extends Controller
{
    public function __construct(
        protected CurrencyRepository $currencyRepository,
        protected CurrencyService $currencyService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_CURRENCY->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_CURRENCY->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_CURRENCY->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_CURRENCY->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $currencies = $this->currencyRepository->all($paginate, $perPage, $filters);

        $result = CurrencyResource::collectionWithMeta($currencies, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Currencies fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(CurrencyRequest $request)
    {
        $currency = $this->currencyService->create($request->validated());

        return ApiResponse::success('Currency created successfully.', CurrencyResource::make($currency));
    }

    public function show(Currency $currency)
    {
        return ApiResponse::success('Currency fetched successfully.', CurrencyResource::make($currency));
    }

    public function update(CurrencyRequest $request, Currency $currency)
    {
        $updatedCurrency = $this->currencyService->update($currency, $request->validated());

        return ApiResponse::success('Currency updated successfully.', CurrencyResource::make($updatedCurrency));
    }

    public function destroy(Request $request, Currency $currency)
    {
        $this->currencyService->delete($currency, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Currency permanently deleted.' : 'Currency deleted successfully.');
    }

    public function restore(Currency $currency)
    {
        $currency = $this->currencyService->restore($currency);

        return ApiResponse::success('Currency restored successfully.', CurrencyResource::make($currency));
    }

    public function export()
    {
        return Excel::download(new CurrencyExport, 'currencies_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }
}
