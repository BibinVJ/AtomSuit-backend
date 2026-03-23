<?php

namespace App\Actions\Purchase;

use App\Enums\PurchaseInvoiceStatus;
use App\Models\PurchaseInvoice;
use App\Models\VendorPayment;

class AllocateVendorPaymentAction
{
    public function applyAllocations(VendorPayment $payment): void
    {
        $invoiceIds = $payment->allocations->pluck('purchase_invoice_id')->unique()->filter();

        foreach ($invoiceIds as $invoiceId) {
            $invoice = PurchaseInvoice::with('items', 'vendorPaymentAllocations.vendorPayment')->find($invoiceId);
            if (! $invoice) {
                continue;
            }

            $this->recalculateInvoiceStatus($invoice);
        }
    }

    public function recalculateInvoiceStatus(PurchaseInvoice $invoice): void
    {
        if ($invoice->status === PurchaseInvoiceStatus::VOIDED) {
            return;
        }

        // Evaluate total invoice amount (Atom Suit standard dynamic calculation)
        $invoiceTotal = $invoice->items ? $invoice->items->sum('total_amount') : 0;
        if ($invoiceTotal <= 0) {
            return;
        }

        // Evaluate total paid amount from all POSTED allocations
        $paidTotal = $invoice->vendorPaymentAllocations()
            ->whereHas('vendorPayment', function ($q) {
                $q->where('status', 'POSTED');
            })
            ->sum('allocated_amount');

        // Note: Due to floating point math, we round for comparison
        $invoiceTotalRound = round((float) $invoiceTotal, 2);
        $paidTotalRound = round((float) $paidTotal, 2);

        if ($paidTotalRound >= $invoiceTotalRound) {
            $invoice->update(['status' => PurchaseInvoiceStatus::PAID]);
        } elseif ($paidTotalRound > 0) {
            $invoice->update(['status' => PurchaseInvoiceStatus::PARTIALLY_PAID]);
        } else {
            $invoice->update(['status' => PurchaseInvoiceStatus::POSTED]);
        }
    }
}
