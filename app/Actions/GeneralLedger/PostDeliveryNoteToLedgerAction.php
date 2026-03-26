<?php

namespace App\Actions\GeneralLedger;

use App\Models\DeliveryNote;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Services\GeneralLedgerService;

class PostDeliveryNoteToLedgerAction
{
    public function __construct(
        protected GeneralLedgerService $glService
    ) {}

    public function handle(DeliveryNote $dn): void
    {
        $movements = StockMovement::where('source_type', DeliveryNote::class)
            ->where('source_id', $dn->id)
            ->with(['item.category'])
            ->get();

        if ($movements->isEmpty()) {
            return;
        }

        $glEntries = [];
        $totalCogsByAccount = [];
        $totalInventoryByAccount = [];

        foreach ($movements as $movement) {
            $item = $movement->item;
            $cost = abs($movement->quantity) * $movement->standard_cost;

            if ($cost <= 0) {
                continue;
            }

            $cogsAccountId = $item->cogs_account_id
                ?? $item->category->cogs_account_id
                ?? Setting::getValue('default_cogs_account');

            $inventoryAccountId = $item->inventory_account_id
                ?? $item->category->inventory_account_id
                ?? Setting::getValue('default_inventory_account');

            if (! $cogsAccountId || ! $inventoryAccountId) {
                // If accounts are not set, we might want to log or skip.
                // In a strict ERP, we should probably throw an exception.
                continue;
            }

            // Aggregate by account
            $totalCogsByAccount[$cogsAccountId] = ($totalCogsByAccount[$cogsAccountId] ?? 0) + $cost;
            $totalInventoryByAccount[$inventoryAccountId] = ($totalInventoryByAccount[$inventoryAccountId] ?? 0) + $cost;
        }

        // 1. DEBIT entries (COGS)
        foreach ($totalCogsByAccount as $accountId => $amount) {
            $glEntries[] = [
                'account_id' => $accountId,
                'debit' => $amount,
                'credit' => 0,
                'description' => "COGS for Delivery Note #{$dn->dn_number}",
                'cost_center_id' => $dn->cost_center_id,
            ];
        }

        // 2. CREDIT entries (Inventory)
        foreach ($totalInventoryByAccount as $accountId => $amount) {
            $glEntries[] = [
                'account_id' => $accountId,
                'debit' => 0,
                'credit' => $amount,
                'description' => "Inventory Issue for Delivery Note #{$dn->dn_number}",
            ];
        }

        if (empty($glEntries)) {
            return;
        }

        $this->glService->postTransaction(
            $dn,
            $dn->dispatch_date,
            "Delivery Note #{$dn->dn_number} Ledger Posting",
            $glEntries
        );
    }
}
