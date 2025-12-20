<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\ExchangeRateExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\ExchangeRateRequest;
use App\Http\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;
use App\Repositories\ExchangeRateRepository;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ExchangeRateController extends Controller
{
    public function __construct(
        protected ExchangeRateRepository $exchangeRateRepository,
        protected ExchangeRateService $exchangeRateService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_EXCHANGE_RATE->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_EXCHANGE_RATE->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_EXCHANGE_RATE->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_EXCHANGE_RATE->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'base_currency_id', 'target_currency_id', 'from', 'to', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        // For Exchange Rates, we always eager load currencies
        $exchangeRates = $this->exchangeRateRepository->all($paginate, $perPage, $filters, ['baseCurrency', 'targetCurrency']);

        $result = ExchangeRateResource::collectionWithMeta($exchangeRates, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Exchange rates fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(ExchangeRateRequest $request)
    {
        $exchangeRate = $this->exchangeRateRepository->create($request->validated());

        return ApiResponse::success('Exchange rate created successfully.', ExchangeRateResource::make($exchangeRate));
    }

    public function show(ExchangeRate $exchangeRate)
    {
        $exchangeRate->load(['baseCurrency', 'targetCurrency']);

        return ApiResponse::success('Exchange rate fetched successfully.', ExchangeRateResource::make($exchangeRate));
    }

    public function update(ExchangeRateRequest $request, ExchangeRate $exchangeRate)
    {
        $updatedExchangeRate = $this->exchangeRateRepository->update($exchangeRate, $request->validated());

        return ApiResponse::success('Exchange rate updated successfully.', ExchangeRateResource::make($updatedExchangeRate));
    }

    public function destroy(Request $request, ExchangeRate $exchangeRate)
    {
        $this->exchangeRateService->delete($exchangeRate, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Exchange rate permanently deleted.' : 'Exchange rate deleted successfully.');
    }

    public function restore(ExchangeRate $exchangeRate)
    {
        $exchangeRate = $this->exchangeRateService->restore($exchangeRate);

        return ApiResponse::success('Exchange rate restored successfully.', ExchangeRateResource::make($exchangeRate));
    }

    public function export()
    {
        return Excel::download(new ExchangeRateExport, 'exchange_rates_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }
}
