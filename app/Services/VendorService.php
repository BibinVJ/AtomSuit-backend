<?php

namespace App\Services;

use App\Models\Vendor;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VendorService
{
    public function ensureVendorIsDeletable(Vendor $vendor)
    {
        if ($vendor->purchases()->exists()) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'Vendor has active transactions.');
        }
    }
}
