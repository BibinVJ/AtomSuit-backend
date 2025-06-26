<?php

namespace App\Services;

use App\Models\Batch;
use App\Repositories\BatchRepository;
use Exception;

class BatchService
{
    public function __construct(protected BatchRepository $repo) {}

    public function create(array $data): Batch
    {
        return $this->repo->create($data);
    }

    public function findOrCreate(array $data): Batch
    {
        return $this->repo->firstOrCreate([
            'item_id' => $data['item_id'],
            'batch_no' => $data['batch_no'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'manufacture_date' => $data['manufacture_date'] ?? null,
        ], [
            'cost_price' => $data['unit_cost'],
        ]);
    }
}
