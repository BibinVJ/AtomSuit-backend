<?php

namespace App\Services;

use App\Models\PriceList;
use App\Repositories\PriceListRepository;
use Illuminate\Database\Eloquent\Model;

class PriceListService extends BaseService
{
    public function __construct(
        protected readonly PriceListRepository $priceListRepository
    ) {
        $this->repository = $priceListRepository;
    }

    public function create(array $data): PriceList
    {
        return PriceList::create($data);
    }

    public function update(PriceList $priceList, array $data): PriceList
    {
        $priceList->update($data);

        return $priceList;
    }

    protected function validateForceDelete(Model $model): void
    {
        // Check for Item Prices
        if ($model->itemPrices()->exists()) {
            throw new \Exception('Cannot delete price list because it has associated item prices.');
        }

        // Check for Customers
        if ($model->customers()->exists()) {
            throw new \Exception('Cannot delete price list because it is assigned to customers.');
        }

        // Check for Vendors
        if ($model->vendors()->exists()) {
            throw new \Exception('Cannot delete price list because it is assigned to vendors.');
        }
    }
}
