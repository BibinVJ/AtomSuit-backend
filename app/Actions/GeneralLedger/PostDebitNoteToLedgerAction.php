<?php

namespace App\Actions\GeneralLedger;

use App\Models\DebitNote;
use App\Models\Vendor;
use App\Services\GeneralLedgerService;
use Exception;

class PostDebitNoteToLedgerAction
{
    public function __construct(protected GeneralLedgerService $glService) {}

    public function execute(DebitNote $debitNote, float $totalDebit): void
    {
        $vendor = $debitNote->vendor;

        if (! $vendor->payables_account_id || ! $vendor->purchase_return_account_id) {
            throw new Exception('Vendor is missing Payables or Purchase Return account mappings.');
        }

        $glEntries = [
            [
                'account_id' => $vendor->payables_account_id,
                'debit' => $totalDebit,
                'credit' => 0,
                'description' => "Debit Note #{$debitNote->debit_note_number} applied to AP",
                'entity_type' => Vendor::class,
                'entity_id' => $vendor->id,
                'cost_center_id' => $debitNote->cost_center_id,
            ],
            [
                'account_id' => $vendor->purchase_return_account_id,
                'debit' => 0,
                'credit' => $totalDebit,
                'description' => "Debit Note #{$debitNote->debit_note_number} Returns/Credits",
                'entity_type' => Vendor::class,
                'entity_id' => $vendor->id,
                'cost_center_id' => $debitNote->cost_center_id,
            ],
        ];

        $this->glService->postTransaction(
            $debitNote,
            $debitNote->date,
            "Vendor Credit/Debit Note #{$debitNote->debit_note_number}",
            $glEntries
        );
    }
}
