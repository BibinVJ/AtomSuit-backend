<?php

namespace App\Services;

use App\Repositories\AccountGroupRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class AccountGroupService extends BaseService
{
    public function __construct(protected AccountGroupRepository $accountGroupRepository)
    {
        $this->repository = $accountGroupRepository;
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

    protected function validateForceDelete(Model $accountGroup): void
    {
        /** @var \App\Models\AccountGroup $accountGroup */
        if ($accountGroup->children()->exists()) {
            throw new Exception('Account Group has sub-groups and cannot be permanently deleted.');
        }

        if ($accountGroup->chartOfAccounts()->exists()) {
            throw new Exception('Account Group has assigned accounts and cannot be permanently deleted.');
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
