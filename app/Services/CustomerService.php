<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Exception;

class CustomerService
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    public function delete(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            throw new Exception('Customer has active transactions.');
        }

        $this->customerRepository->delete($customer);
    }
}
