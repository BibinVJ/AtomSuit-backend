<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Exception;

class CustomerService
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    public function delete(Customer $customer, bool $force = false)
    {
        if ($force) {
            if ($customer->sales()->exists()) {
                throw new Exception('Customer has active transactions and cannot be hard deleted.');
            }
            return $this->customerRepository->forceDelete($customer);
        }

        return $this->customerRepository->delete($customer);
    }

    public function restore(int $id): Customer
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $this->customerRepository->restore($customer);

        return $customer;
    }
}
