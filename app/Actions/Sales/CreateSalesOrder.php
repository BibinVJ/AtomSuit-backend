<?php

namespace App\Actions\Sales;

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\TaxGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateSalesOrder
{
    public function __construct(protected RecalculateSalesDocumentTotalsAction $calculator) {}

    public function handle(array $data, ?User $creator = null): SalesOrder
    {
        return DB::transaction(function () use ($data, $creator) {
            $customer = Customer::find($data['customer_id']);
            $customerMeta = $customer ? [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'billing_address_line_1' => $customer->billing_address_line_1,
                'billing_address_line_2' => $customer->billing_address_line_2,
                'billing_city' => $customer->billing_city,
                'billing_state' => $customer->billing_state,
                'billing_country' => $customer->billing_country,
                'billing_zip_code' => $customer->billing_zip_code,
                'shipping_address_line_1' => $customer->shipping_address_line_1,
                'shipping_address_line_2' => $customer->shipping_address_line_2,
                'shipping_city' => $customer->shipping_city,
                'shipping_state' => $customer->shipping_state,
                'shipping_country' => $customer->shipping_country,
                'shipping_zip_code' => $customer->shipping_zip_code,
            ] : null;

            $so = SalesOrder::create([
                'customer_id' => $data['customer_id'],
                'customer_meta' => $customerMeta,
                'order_number' => $data['order_number'],
                'reference_number' => $data['reference_number'] ?? null,
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => SalesOrderStatus::CONFIRMED,
                'notes' => $data['notes'] ?? null,
                'cost_center_id' => $data['cost_center_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // Preload Master Data
            $itemIds = array_column($data['items'], 'item_id');
            $taxIds = array_column($data['items'], 'tax_group_id');
            $itemsDb = Item::with(['category', 'unit'])->whereIn('id', $itemIds)->get()->keyBy('id');
            $taxesDb = TaxGroup::with('taxRates')->whereIn('id', array_filter($taxIds))->get()->keyBy('id');

            foreach ($data['items'] as $itemData) {
                $itemModel = $itemsDb->get($itemData['item_id']);
                $taxModel = isset($itemData['tax_group_id']) ? $taxesDb->get($itemData['tax_group_id']) : null;

                $itemMeta = $itemModel ? [
                    'name' => $itemModel->name,
                    'sku' => $itemModel->sku,
                    'category' => $itemModel->category?->name,
                    'unit' => $itemModel->unit?->name,
                ] : null;

                $taxMeta = null;
                if ($taxModel) {
                    $rates = [];
                    foreach ($taxModel->taxRates as $tr) {
                        $rates[] = [
                            'id' => $tr->id,
                            'name' => $tr->name,
                            'rate' => (float) $tr->rate,
                            'type' => $tr->type->value,
                        ];
                    }
                    $taxMeta = [
                        'id' => $taxModel->id,
                        'name' => $taxModel->name,
                        'rates' => $rates,
                    ];
                }

                $so->items()->create([
                    'item_id' => $itemData['item_id'],
                    'item_meta' => $itemMeta,
                    'tax_meta' => $taxMeta,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_type' => $itemData['discount_type'] ?? null,
                    'discount_value' => $itemData['discount_value'] ?? 0,
                    'tax_group_id' => $itemData['tax_group_id'] ?? null,
                ]);
            }

            // Secure Math
            $this->calculator->execute($so);

            return $so;
        });
    }
}
