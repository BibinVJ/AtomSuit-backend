<?php

namespace App\Services;

use App\Repositories\TaxGroupRepository;
use Exception;

class TaxGroupService extends BaseService
{
    public function __construct(protected TaxGroupRepository $taxGroupRepository)
    {
        $this->repository = $taxGroupRepository;
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $taxGroup): void
    {
        /** @var \App\Models\TaxGroup $taxGroup */
        if ($taxGroup->items()->exists()) {
            throw new Exception('Cannot hard delete: Tax Group is assigned to Items.');
        }

        if ($taxGroup->customers()->exists()) {
            throw new Exception('Cannot hard delete: Tax Group is assigned to Customers.');
        }

        if ($taxGroup->vendors()->exists()) {
            throw new Exception('Cannot hard delete: Tax Group is assigned to Vendors.');
        }
    }
}
