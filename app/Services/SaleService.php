<?php

namespace App\Services;

use App\Actions\Sales\CreateSaleAction;
use App\Actions\Sales\UpdateSaleAction;
use App\Actions\Sales\VoidSaleAction;
use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use App\Models\Sale;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SaleService
{
    public function __construct(
        protected CreateSaleAction $createSale,
        protected UpdateSaleAction $updateSale,
        protected VoidSaleAction $voidSale,
        protected StockMovementService $stockMovementService
    ) {}


    public function create(array $data): Sale
    {
        // additional checks if sales can be added

        return $this->createSale->execute($data);
    }

    public function update(Sale $sale, array $data): Sale
    {
        if ($sale->status === TransactionStatus::VOIDED) {
            throw new ConflictHttpException("Sale can't be edited because it has been voided.");
        }

        if ($sale->payment_status === PaymentStatus::PAID) {
            throw new ConflictHttpException("Sale can't be edited because it has been paid.");
        }

        return $this->updateSale->execute($sale, $data);
    }

    public function void(Sale $sale): Sale
    {
        if ($sale->payment_status === PaymentStatus::PAID) {
            throw new ConflictHttpException("Sale can't be voided because it has been paid.");
        }

        return $this->voidSale->execute($sale);
    }
}
