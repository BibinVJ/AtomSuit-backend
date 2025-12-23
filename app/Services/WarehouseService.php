<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Repositories\WarehouseRepository;

class WarehouseService extends BaseService
{
    public function __construct(WarehouseRepository $warehouseRepository)
    {
        $this->repository = $warehouseRepository;
    }

    public function create(array $data): Warehouse
    {
        return $this->repository->create($data);
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return $this->repository->update($warehouse, $data);
    }
}
