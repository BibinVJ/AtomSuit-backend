<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Exception;

class CustomerService extends BaseService
{
    public function __construct(protected CustomerRepository $customerRepository) {
        $this->repository = $customerRepository;
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $customer): void
    {
        /** @var \App\Models\Customer $customer */
        if ($customer->sales()->exists()) {
            throw new Exception('Customer has active transactions and cannot be hard deleted.');
        }
    }
}
