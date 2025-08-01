<?php

namespace App\Services;

use App\Models\Vendor;
use App\Repositories\VendorRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VendorService
{
    public function __construct(protected VendorRepository $vendorRepository) {}

    public function delete(Vendor $vendor)
    {
        if ($vendor->purchases()->exists()) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, 'Vendor has active transactions.');
        }

        $this->vendorRepository->delete($vendor);
    }
}
