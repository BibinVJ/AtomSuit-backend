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

    /**
     * Reverses an existing GL Transaction by creating an inverse entry.
     */
    public function reverseTransaction(Model $reference, $date, string $description): ?GeneralLedgerTransaction
    {
        $originalTx = GeneralLedgerTransaction::where('reference_type', $reference->getMorphClass())
            ->where('reference_id', $reference->getKey())
            ->first();

        if (! $originalTx) {
            return null;
        }

        $entries = [];
        $originalEntries = GeneralLedgerEntry::where('gl_transaction_id', $originalTx->id)->get();

        foreach ($originalEntries as $entry) {
            $entries[] = [
                'account_id' => $entry->account_id,
                'debit' => $entry->credit, // Swap credit to debit
                'credit' => $entry->debit, // Swap debit to credit
                'description' => $description.' (Revr: '.$entry->description.')',
                'entity_type' => $entry->entity_type,
                'entity_id' => $entry->entity_id,
                'cost_center_id' => $entry->cost_center_id,
            ];
        }

        return $this->postTransaction($reference, $date, $description, $entries);
    }
}
