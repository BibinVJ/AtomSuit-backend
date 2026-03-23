<?php

namespace App\Services;

use App\Repositories\CostCenterRepository;

class CostCenterService extends BaseService
{
    public function __construct(protected CostCenterRepository $costCenterRepository)
    {
        $this->repository = $costCenterRepository;
    }

    protected function validateForceDelete(\Illuminate\Database\Eloquent\Model $costCenter): void
    {
        /** @var \App\Models\CostCenter $costCenter */

        // TODO: Add sales based validation later once implemented. andd add all the validation

        if ($costCenter->children()->exists()) {
            throw new \Exception('Cost Center has child cost centers and cannot be permanently deleted.');
        }

        if ($costCenter->purchaseOrders()->exists()) {
            throw new \Exception('Cost Center is used in Purchase Orders and cannot be permanently deleted.');
        }

        if ($costCenter->goodsReceivedNotes()->exists()) {
            throw new \Exception('Cost Center is used in Goods Received Notes and cannot be permanently deleted.');
        }

        if ($costCenter->purchaseInvoices()->exists()) {
            throw new \Exception('Cost Center is used in Purchase Invoices and cannot be permanently deleted.');
        }

        if ($costCenter->generalLedgerEntries()->exists()) {
            throw new \Exception('Cost Center is used in General Ledger Entries and cannot be permanently deleted.');
        }
    }
}
