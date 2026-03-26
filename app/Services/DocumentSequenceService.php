<?php

namespace App\Services;

use App\Models\CreditNote;
use App\Models\CustomerPayment;
use App\Models\DebitNote;
use App\Models\DeliveryNote;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Models\VendorPayment;
use InvalidArgumentException;

class DocumentSequenceService
{
    /**
     * Map of document types to their model classes, columns, and default prefixes.
     */
    private const CONFIG = [
        'sales_order' => [SalesOrder::class, 'order_number', 'SO'],
        'purchase_order' => [PurchaseOrder::class, 'order_number', 'PO'],
        'grn' => [GoodsReceivedNote::class, 'grn_number', 'GRN'],
        'delivery_note' => [DeliveryNote::class, 'dn_number', 'DN'],
        'purchase_invoice' => [PurchaseInvoice::class, 'invoice_number', 'PI'],
        'sales_invoice' => [SalesInvoice::class, 'invoice_number', 'SI'],
        'debit_note' => [DebitNote::class, 'debit_note_number', 'DBN'],
        'credit_note' => [CreditNote::class, 'credit_note_number', 'CN'],
        'customer_payment' => [CustomerPayment::class, 'payment_number', 'CRCP'],
        'vendor_payment' => [VendorPayment::class, 'payment_number', 'VPAY'],
    ];

    /**
     * Generates the next sequential document number for the given type.
     *
     * @param  string  $type  The document type key from CONFIG.
     * @param  int  $padding  Zero-padding length for the sequence number.
     *
     * @throws InvalidArgumentException
     */
    public function generateNext(string $type, int $padding = 4): string
    {
        if (! isset(self::CONFIG[$type])) {
            throw new InvalidArgumentException("Invalid document type: {$type}");
        }

        [$modelClass, $column, $defaultPrefix] = self::CONFIG[$type];

        // 1. Resolve Prefix (from settings or fallback to default)
        $prefix = Setting::getValue("prefix_{$type}", $defaultPrefix);

        // 2. Format Date Part (YYYYMM)
        $datePart = now()->format('Ym');
        $fullPrefix = "{$prefix}-{$datePart}-";

        // 3. Find Next ID
        // Note: We use max('id') as a sequence base. For high-concurrency,
        // a dedicated sequence table or Redis counter would be preferred,
        // but this follows the established pattern in the Atom Suit codebase.
        $nextId = $modelClass::max('id') + 1;
        $number = $fullPrefix.str_pad((string) $nextId, $padding, '0', STR_PAD_LEFT);

        // 4. Ensure Uniqueness (Look-ahead)
        while ($modelClass::where($column, $number)->exists()) {
            $nextId++;
            $number = $fullPrefix.str_pad((string) $nextId, $padding, '0', STR_PAD_LEFT);
        }

        return $number;
    }
}
