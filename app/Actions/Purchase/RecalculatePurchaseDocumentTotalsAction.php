<?php

namespace App\Actions\Purchase;

use App\Enums\TaxRateTypeEnum;
use Illuminate\Database\Eloquent\Model;

class RecalculatePurchaseDocumentTotalsAction
{
    /**
     * Systematically loops all line items of any Core Purchase Document,
     * securely calculates pure financial logic using JSON snapshotted limits,
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
            // 1. Resolve quantity dynamically since GRNs use accepted_quantity
            $qty = $item->quantity ?? ($item->accepted_quantity ?? 0);
            $price = $item->unit_price ?? 0;

            $lineSubTotal = (float) $qty * (float) $price;

            // 2. Evaluate Discount Math
            $lineDiscount = 0.0;
            if ($item->discount_type === 'percentage') {
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
                    if (($taxRateData['type'] ?? 'percentage') === TaxRateTypeEnum::PERCENTAGE->value) {
                        $lineTax += $afterDiscount * ($rateValue / 100);
                    } else {
                        // Fixed tax is considered a per-unit fee in typical ERP handling
                        $lineTax += $rateValue * $qty;
                    }
                }
            } elseif (is_array($item->tax_meta) && isset($item->tax_meta['rate'])) {
                // Fallback for old documents before the compound array migration
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
        $document->saveQuietly();
    }
}
