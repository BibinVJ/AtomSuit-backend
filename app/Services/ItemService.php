<?php

namespace App\Services;

use App\Repositories\ItemRepository;
use Exception;

class ItemService extends BaseService
{
    public function __construct(protected ItemRepository $itemRepository)
    {
        $this->repository = $itemRepository;
    }

    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $item = $this->repository->create($data);

            if (! empty($data['prices'])) {
                foreach ($data['prices'] as $price) {
                    if (! empty($price['is_deleted'])) {
                        continue;
                    }

                    $item->itemPrices()->create([
                        'price_list_id' => $price['price_list_id'],
                        'price' => $price['price'],
                        'min_quantity' => $price['min_quantity'] ?? 1,
                    ]);
                }
            }

            return $item->load('itemPrices');
        });
    }

    public function update(\Illuminate\Database\Eloquent\Model $item, array $data): \Illuminate\Database\Eloquent\Model
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($item, $data) {
            $this->repository->update($item, $data);

            if (isset($data['prices'])) {
                foreach ($data['prices'] as $priceData) {
                    if (! empty($priceData['id'])) {
                        if (! empty($priceData['is_deleted'])) {
                            $item->itemPrices()->where('id', $priceData['id'])->delete();
                        } else {
                            $item->itemPrices()->where('id', $priceData['id'])->update([
                                'price_list_id' => $priceData['price_list_id'],
                                'price' => $priceData['price'],
                                'min_quantity' => $priceData['min_quantity'] ?? 1,
                            ]);
                        }
                    } else {
                        if (empty($priceData['is_deleted'])) {
                            $item->itemPrices()->create([
                                'price_list_id' => $priceData['price_list_id'],
                                'price' => $priceData['price'],
                                'min_quantity' => $priceData['min_quantity'] ?? 1,
                            ]);
                        }
                    }
                }
            }

            return $item->refresh()->load('itemPrices');
        });
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $item): void
    {
        /** @var \App\Models\Item $item */
        if ($item->batches()->exists()) {
            throw new Exception('Cannot hard delete: Item has related batches.');
        }

        if ($item->saleItems()->exists()) {
            throw new Exception('Cannot hard delete: Item has related sales.');
        }

        if ($item->stockMovements()->exists()) {
            throw new Exception('Cannot hard delete: Item has related stock movements.');
        }
    }
}
