<?php

namespace App\Services;

use App\Repositories\VendorRepository;

class VendorService extends BaseService
{
    public function __construct(protected VendorRepository $vendorRepository)
    {
        $this->repository = $vendorRepository;
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $vendor): void
    {
        /** @var \App\Models\Vendor $vendor */
        if ($vendor->purchases()->exists()) {
            throw new \Exception('Vendor has active transactions and cannot be hard deleted.');
        }
    }
}
