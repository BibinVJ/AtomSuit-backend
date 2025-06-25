<?php

namespace App\Services;

use App\Models\Vendor;
use Exception;

class VendorService
{
    public function ensureVendorIsDeletable(Vendor $vendor)
    {
        if ($vendor->purchases()->exists()) {
            throw new Exception('Vendor has active transactions.');
        }
    }
}
