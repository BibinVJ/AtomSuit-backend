<?php

namespace App\Services;

use App\Models\Batch;
use App\Repositories\BatchRepository;

class BatchService extends BaseService
{
    public function __construct(protected BatchRepository $batchRepository)
    {
        $this->repository = $batchRepository;
    }

    public function create(array $data): Batch
    {
        /** @var Batch $batch */
        $batch = $this->batchRepository->create($data);

        return $batch;
    }

    public function findOrCreate(array $data): Batch
    {
        /** @var Batch $batch */
        $batch = $this->batchRepository->firstOrCreate(
            [
                'item_id' => $data['item_id'],
                'batch_number' => $data['batch_number'],
            ],
            [
                'expiry_date' => $data['expiry_date'] ?? null,
                'manufacture_date' => $data['manufacture_date'] ?? null,
                'cost_price' => $data['unit_cost'],
            ]
        );

        return $batch;
    }
}
