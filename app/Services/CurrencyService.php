<?php

namespace App\Services;

use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use Illuminate\Database\Eloquent\Model;

class CurrencyService extends BaseService
{
    public function __construct(protected CurrencyRepository $currencyRepository)
    {
        $this->repository = $currencyRepository;
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(Currency $currency, array $data)
    {
        return $this->repository->update($currency, $data);
    }

    public function delete(Model $currency, bool $force = false)
    {
        /** @var \App\Models\Currency $currency */
        $defaultCurrencyId = (int) setting('currency_id');
        if ($currency->id === $defaultCurrencyId) {
            throw new \Exception('The system default currency cannot be deleted.');
        }

        return parent::delete($currency, $force);
    }

    protected function validateForceDelete(Model $currency): void
    {
        /** @var \App\Models\Currency $currency */
        if ($currency->customers()->exists()) {
            throw new \Exception('Cannot hard delete: Currency is assigned to customers.');
        }

        if ($currency->vendors()->exists()) {
            throw new \Exception('Cannot hard delete: Currency is assigned to vendors.');
        }

        if ($currency->baseExchangeRates()->exists() || $currency->targetExchangeRates()->exists()) {
            throw new \Exception('Cannot hard delete: Currency has related exchange rates.');
        }
    }
}
