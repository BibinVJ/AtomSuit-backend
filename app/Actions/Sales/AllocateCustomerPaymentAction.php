<?php

namespace App\Actions\Sales;

use App\Enums\CustomerPaymentStatus;
use App\Enums\SalesInvoiceStatus;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\SalesInvoice;

class AllocateCustomerPaymentAction
{
    /**
     * Applies payments to specific invoices, updating their paid and due amounts.
     */
    public function applyAllocations(CustomerPayment $payment): void
    {
        $invoiceIds = $payment->allocations->pluck('sales_invoice_id')->unique()->filter();

        foreach ($invoiceIds as $invoiceId) {
            $invoice = SalesInvoice::find($invoiceId);
            if ($invoice) {
                $this->recalculateInvoiceStatus($invoice);
            }
        }

        $payment->allocated_amount = $payment->allocations()->sum('allocated_amount');
        $payment->saveQuietly();
    }

    /**
     * Recalculates the paid amount, due amount and status of a Sales Invoice.
     */
    public function recalculateInvoiceStatus(SalesInvoice $invoice): void
    {
        if ($invoice->status === SalesInvoiceStatus::VOIDED) {
            return;
        }

        // Evaluate total paid amount from all POSTED allocations
        $paidTotal = CustomerPaymentAllocation::query()
            ->where('sales_invoice_id', $invoice->id)
            ->whereHas('customerPayment', function ($q) {
                $q->where('status', CustomerPaymentStatus::POSTED);
            })
            ->sum('allocated_amount');

        $invoiceTotal = (float) $invoice->total_amount;
        $paidTotal = (float) $paidTotal;

        $invoiceTotalRound = round($invoiceTotal, 2);
        $paidTotalRound = round($paidTotal, 2);

        $invoice->paid_amount = $paidTotal;
        $invoice->due_amount = max(0, $invoiceTotal - $paidTotal);

        if ($paidTotalRound >= $invoiceTotalRound && $invoiceTotalRound > 0) {
            $invoice->status = SalesInvoiceStatus::PAID;
        } elseif ($paidTotalRound > 0) {
            $invoice->status = SalesInvoiceStatus::PARTIALLY_PAID;
        } else {
            $invoice->status = SalesInvoiceStatus::POSTED;
        }

        $invoice->saveQuietly();
    }
}
