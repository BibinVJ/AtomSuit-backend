<?php

namespace App\Repositories;

use App\Models\AccountType;
use App\Repositories\Traits\HasCrudRepository;

class AccountTypeRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new AccountType;
    }
}
