<?php

namespace App\Services;

use App\Repositories\ExchangeRateRepository;

class ExchangeRateService extends BaseService
{
    public function __construct(protected ExchangeRateRepository $exchangeRateRepository)
    {
        $this->repository = $exchangeRateRepository;
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $exchangeRate): void
    {
        // Add checks here if exchange rates are used in transactions or other modules.
        // For now, it's just a placeholder to follow the pattern or we can keep it empty if no relations yet.
        // But usually, exchange rates are linked to journals/invoices.
    }
}
