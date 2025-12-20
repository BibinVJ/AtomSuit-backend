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
        if (! empty($data['is_default'])) {
            $this->handleDefaultCurrency();
        }

        return $this->repository->create($data);
    }

    public function update(Currency $currency, array $data)
    {
        if (isset($data['is_default'])) {
            if ($data['is_default']) {
                $this->handleDefaultCurrency();
            } elseif ($currency->is_default) {
                // If it was default and being made non-default, ensure another default exists or throw error.
                if ($this->isOnlyDefaultCurrency($currency)) {
                    throw new \Exception('At least one default currency is required.');
                }
            }
        }

        return $this->repository->update($currency, $data);
    }

    public function delete(Model $currency, bool $force = false)
    {
        /** @var \App\Models\Currency $currency */
        if ($currency->is_default) {
            throw new \Exception('Default currency cannot be deleted.');
        }

        return parent::delete($currency, $force);
    }

    protected function handleDefaultCurrency(): void
    {
        $this->repository->getModel()->where('is_default', true)->update(['is_default' => false]);
    }

    protected function isOnlyDefaultCurrency(Currency $currency): bool
    {
        return $this->repository->getModel()->where('is_default', true)->where('id', '!=', $currency->id)->count() === 0;
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
