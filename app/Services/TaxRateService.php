<?php

namespace App\Services;

use App\Repositories\TaxRateRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class TaxRateService extends BaseService
{
    public function __construct(protected TaxRateRepository $taxRateRepository)
    {
        $this->repository = $taxRateRepository;
    }

    protected function validateForceDelete(Model $taxRate): void
    {
        /** @var \App\Models\TaxRate $taxRate */
        if ($taxRate->taxGroups()->exists()) {
            throw new Exception('Cannot hard delete: Tax Rate belongs to Tax Groups.');
        }
    }
}
