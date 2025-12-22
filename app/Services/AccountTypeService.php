<?php

namespace App\Services;

use App\Repositories\AccountTypeRepository;

class AccountTypeService extends BaseService
{
    public function __construct(protected AccountTypeRepository $accountTypeRepository)
    {
        $this->repository = $accountTypeRepository;
    }

    public function all()
    {
        return $this->repository->all();
    }
}
