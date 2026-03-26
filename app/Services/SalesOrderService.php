<?php

namespace App\Services;

use App\Repositories\SalesOrderRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class SalesOrderService extends BaseService
{
    public function __construct(protected SalesOrderRepository $salesOrderRepository)
    {
        $this->repository = $salesOrderRepository;
    }

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\SalesOrder $model */
        // Orders can be deleted if draft or confirmed but no deliveries/invoices (strict check)
        if ($model->deliveryNotes()->exists() || $model->salesInvoices()->exists()) {
            throw new Exception('Sales Order has associated documents and cannot be deleted.');
        }

        return parent::delete($model, $force);
    }
}
