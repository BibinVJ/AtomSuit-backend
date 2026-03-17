<?php

namespace App\Services;

use App\Models\GeneralLedgerEntry;
use App\Models\GeneralLedgerTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GeneralLedgerService
{
    /**
     * Post a new GL Transaction with multiple entries.
     *
     * @param  Model  $reference  The source model (Invoice, Payment, etc.)
     * @param  Carbon|string  $date
     * @param  array  $entries  Array of ['account_id', 'debit', 'credit', 'description'?, 'entity_type'?, 'entity_id'?, 'cost_center_id'?]
     */
    public function postTransaction(Model $reference, $date, string $description, array $entries): GeneralLedgerTransaction
    {
        return DB::transaction(function () use ($reference, $date, $description, $entries) {

            // 1. Create Header
            $transaction = GeneralLedgerTransaction::create([
                'reference_type' => $reference->getMorphClass(),
                'reference_id' => $reference->getKey(),
                'transaction_date' => $date,
                'description' => $description,
            ]);

            $totalDebit = 0;
            $totalCredit = 0;

            // 2. Create Entries
            foreach ($entries as $entry) {
                $debit = $entry['debit'] ?? 0;
                $credit = $entry['credit'] ?? 0;

                $totalDebit += $debit;
                $totalCredit += $credit;

                GeneralLedgerEntry::create([
                    'gl_transaction_id' => $transaction->id,
                    'account_id' => $entry['account_id'],
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $entry['description'] ?? null,
                    'entity_type' => $entry['entity_type'] ?? null,
                    'entity_id' => $entry['entity_id'] ?? null,
                    'cost_center_id' => $entry['cost_center_id'] ?? null,
                ]);
            }

            // 3. Validate Balance
            if (abs($totalDebit - $totalCredit) > 0.0001) { // Allowing small float precision error
                throw new \Exception("General Ledger Transaction is out of balance. Debit: $totalDebit, Credit: $totalCredit");
            }

            return $transaction;
        });
    }
}
