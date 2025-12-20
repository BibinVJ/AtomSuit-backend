<?php

namespace App\Services;

use App\Models\Vendor;
use App\Repositories\VendorRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VendorService
{
    public function __construct(protected VendorRepository $vendorRepository) {}

    public function delete(Vendor $vendor, bool $force = false)
    {
        if ($force) {
            if ($vendor->purchases()->exists()) {
                throw new \Exception('Vendor has active transactions and cannot be hard deleted.');
            }
            return $this->vendorRepository->forceDelete($vendor);
        }

        return $this->vendorRepository->delete($vendor);
    }

    public function restore(int $id): Vendor
    {
        $vendor = Vendor::onlyTrashed()->findOrFail($id);
        $this->vendorRepository->restore($vendor);

        return $vendor;
    }
}
