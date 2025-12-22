<?php

namespace App\Services;

use App\Repositories\ChartOfAccountRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccountService extends BaseService
{
    public function __construct(protected ChartOfAccountRepository $chartOfAccountRepository)
    {
        $this->repository = $chartOfAccountRepository;
    }

    public function getPaginated(array $filters = [])
    {
        return $this->repository->all(true, 15, $filters);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function find($id, $withTrashed = false)
    {
        if ($withTrashed) {
            return $this->repository->getModel()->withTrashed()->findOrFail($id);
        }

        return $this->repository->findOrFail($id);
    }

    public function update(Model $model, array $data)
    {
        return $this->repository->update($model, $data);
    }

    protected function validateForceDelete(Model $chartOfAccount): void
    {
        /** @var \App\Models\ChartOfAccount $chartOfAccount */
        // Check for transactions once Transaction module is ready
        // if ($chartOfAccount->transactions()->exists()) {
        //     throw new Exception('Account has transactions and cannot be permanently deleted.');
        // }

        if (\App\Models\Customer::where('sales_account_id', $chartOfAccount->id)
            ->orWhere('sales_discount_account_id', $chartOfAccount->id)
            ->orWhere('receivables_account_id', $chartOfAccount->id)
            ->orWhere('sales_return_account_id', $chartOfAccount->id)
            ->exists()) {
            throw new Exception('Cannot hard delete: Account is linked to one or more customers.');
        }

        if (\App\Models\Vendor::where('payables_account_id', $chartOfAccount->id)
            ->orWhere('purchase_account_id', $chartOfAccount->id)
            ->orWhere('purchase_discount_account_id', $chartOfAccount->id)
            ->orWhere('purchase_return_account_id', $chartOfAccount->id)
            ->exists()) {
            throw new Exception('Cannot hard delete: Account is linked to one or more vendors.');
        }
    }

    public function import(array $data)
    {
        // TODO: Implement Import Logic
        throw new Exception('Import logic not implemented yet.');
    }

    public function export()
    {
        // TODO: Implement Export Logic
        throw new Exception('Export logic not implemented yet.');
    }
}
