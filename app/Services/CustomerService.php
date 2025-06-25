<?php

namespace App\Services;

use App\Models\Customer;
use Exception;

class CustomerService
{
    public function ensureCustomerIsDeletable(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            throw new Exception('Customer has active transactions.');
        }
    }
}
