<?php

namespace App\Actions\Sales;

use App\Enums\DiscountType;
use App\Enums\TaxRateTypeEnum;
use App\Models\SalesInvoice;
use Illuminate\Database\Eloquent\Model;

class RecalculateSalesDocumentTotalsAction
{
    /**
     * Systematically loops all line items of any Core Sales Document,
     * securely calculates financial logic using JSON snapshotted limits,
     * and strictly forces the sums onto the document's cached database header.
     */
    public function execute(Model $document): void
    {
        $document->load('items');

        $subTotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $grandTotal = 0.0;

        foreach ($document->items as $item) {
            // 1. Resolve quantity dynamically
            // SO, SI: quantity
            // DN: dispatched_quantity
            // CN: returned_quantity
            $qty = $item->quantity ?? ($item->dispatched_quantity ?? ($item->returned_quantity ?? 0));
            $price = $item->unit_price ?? 0;

            $lineSubTotal = (float) $qty * (float) $price;

            // 2. Evaluate Discount Math
            $lineDiscount = 0.0;
            if ($item->discount_type === DiscountType::PERCENTAGE) {
                $lineDiscount = $lineSubTotal * (((float) ($item->discount_value ?? 0)) / 100);
            } else {
                $lineDiscount = (float) ($item->discount_value ?? 0);
            }
            $item->discount_amount = $lineDiscount;

            $afterDiscount = $lineSubTotal - $lineDiscount;

            // 3. Evaluate Snapshot Tax Block
            $lineTax = 0.0;
            if (is_array($item->tax_meta) && isset($item->tax_meta['rates'])) {
                foreach ($item->tax_meta['rates'] as $taxRateData) {
                    $rateValue = (float) $taxRateData['rate'];
                    if ($taxRateData['type'] === TaxRateTypeEnum::PERCENTAGE->value) {
                        $lineTax += $afterDiscount * ($rateValue / 100);
                    } else {
                        // Fixed tax is considered a per-unit fee
                        $lineTax += $rateValue * $qty;
                    }
                }
            } elseif (is_array($item->tax_meta) && isset($item->tax_meta['rate'])) {
                // Fallback
                $taxRate = (float) $item->tax_meta['rate'];
                $lineTax = $afterDiscount * ($taxRate / 100);
            }
            $item->tax_amount = $lineTax;

            // 4. Secure Line Caching
            $lineTotal = $afterDiscount + $lineTax;
            $item->sub_total = $lineSubTotal;
            $item->total_amount = $lineTotal;

            $item->saveQuietly();

            // Aggregate upward
            $subTotal += $lineSubTotal;
            $discountTotal += $lineDiscount;
            $taxTotal += $lineTax;
            $grandTotal += $lineTotal;
        }

        // 5. Force Header Caching
        $document->sub_total = $subTotal;
        $document->discount_total = $discountTotal;
        $document->tax_total = $taxTotal;
        $document->total_amount = $grandTotal;

        // For Invoices, update due_amount if it's a new or fully unpaid invoice
        if ($document instanceof SalesInvoice) {
            $document->due_amount = $document->total_amount - ($document->paid_amount ?? 0);
        }

        $document->saveQuietly();
    }
}
