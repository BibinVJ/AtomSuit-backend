<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Repositories\PurchaseOrderRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderService extends BaseService
{
    public function __construct(protected PurchaseOrderRepository $purchaseOrderRepository)
    {
        $this->repository = $purchaseOrderRepository;
    }

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\PurchaseOrder $model */
        if ($model->status !== PurchaseOrderStatus::DRAFT) {
            throw new Exception('Only Draft orders can be deleted.');
        }

        return parent::delete($model, $force);
    }

    protected function validateForceDelete(Model $model): void
    {
        if ($model->status !== PurchaseOrderStatus::DRAFT) {
            throw new Exception('Only Draft orders can be deleted.');
        }

        /** @var \App\Models\PurchaseOrder $model */
        if ($model->goodsReceivedNotes()->exists()) {
            throw new Exception('Purchase Order has associated Goods Received Notes and cannot be permanently deleted.');
        }

        if ($model->purchaseInvoices()->exists()) {
            throw new Exception('Purchase Order has associated Invoices and cannot be permanently deleted.');
        }
    }
}
