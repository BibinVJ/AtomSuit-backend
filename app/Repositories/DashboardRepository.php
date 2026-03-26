<?php

namespace App\Repositories;

use App\DataTransferObjects\DashboardChartPointDTO;
use App\DataTransferObjects\DashboardCustomerDTO;
use App\DataTransferObjects\DashboardExpiringItemDTO;
use App\DataTransferObjects\DashboardStockItemDTO;
use App\DataTransferObjects\DashboardTopItemDTO;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\GoodsReceivedNote;
use App\Models\Item;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardRepository
{
    public function getTotalSalesAmount(): float
    {
        return SalesInvoice::with('items')->get()->sum(fn ($invoice) => $invoice->total_amount);
    }

    public function getTotalPurchaseAmount(): float
    {
        return PurchaseInvoice::with('items')->get()->sum(fn ($purchase) => $purchase->total_amount);
    }

    public function getTopSellingItems(int $limit = 5): Collection
    {
        return StockMovement::selectRaw('item_id, ABS(SUM(quantity)) as total_quantity')
            ->where('source_type', DeliveryNote::class)
            ->groupBy('item_id')
            ->with('item:id,sku,name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => new DashboardTopItemDTO(
                id: $row->item_id,
                sku: $row->item->sku ?? '',
                name: $row->item->name ?? '',
                total_quantity: (int) $row->total_quantity
            ));
    }

    public function getTopPurchasedItems(int $limit = 5): Collection
    {
        return StockMovement::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->where('source_type', GoodsReceivedNote::class)
            ->groupBy('item_id')
            ->with('item:id,sku,name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => new DashboardTopItemDTO(
                id: $row->item_id,
                sku: $row->item->sku ?? '',
                name: $row->item->name ?? '',
                total_quantity: (int) $row->total_quantity
            ));
    }

    public function getExpiringItems(): Collection
    {
        return Batch::with('item')
            ->where('expiry_date', '<', now()->addDays(30))
            ->get()
            ->map(function (Batch $batch) {
                /** @var \App\Models\Item $item */
                $item = $batch->item;

                return new DashboardExpiringItemDTO(
                    id: $item->id,
                    sku: $item->sku ?? '',
                    name: $item->name,
                    batch_number: $batch->batch_number,
                    expiry_date: $batch->expiry_date,
                    stock_remaining: $batch->stockOnHand()
                );
            });
    }

    public function getOutOfStockItems(): Collection
    {
        return StockMovement::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->havingRaw('total_quantity <= 0')
            ->with('item:id,sku,name')
            ->get()
            ->map(fn ($row) => new DashboardStockItemDTO(
                id: $row->item->id,
                sku: $row->item->sku ?? '',
                name: $row->item->name ?? '',
                stock_remaining: $row->total_quantity
            ));
    }

    public function getLowStockItems(): Collection
    {
        return StockMovement::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->havingRaw('total_quantity <= '.setting('inventory_low_stock_threshold', 30))
            ->with('item:id,sku,name')
            ->get()
            ->map(fn ($row) => new DashboardStockItemDTO(
                id: $row->item->id,
                sku: $row->item->sku ?? '',
                name: $row->item->name ?? '',
                stock_remaining: $row->total_quantity
            ));
    }

    public function getDeadStockItems(int $days = 90): Collection
    {
        $cutoff = now()->subDays($days);

        return Item::whereDoesntHave('stockMovements', function ($query) use ($cutoff) {
            $query->where('source_type', DeliveryNote::class)
                ->where('created_at', '>=', $cutoff);
        })
            ->with('stockMovements')
            ->get()
            ->map(fn ($item) => new DashboardStockItemDTO(
                id: $item->id,
                sku: $item->sku,
                name: $item->name,
                stock_remaining: $item->stockMovements->sum('quantity'),
            ))
            ->filter(fn ($dto) => $dto->stock_remaining > 0);
    }

    public function getBestCustomers(int $limit = 5): Collection
    {
        return Customer::with('invoices.items')
            ->get()
            ->map(fn (Customer $customer) => new DashboardCustomerDTO(
                id: $customer->id,
                name: $customer->name,
                email: $customer->email ?? null,
                phone: $customer->phone ?? null,
                total_spent: $customer->totalSpent()
            ))
            ->sortByDesc(fn ($dto) => $dto->total_spent)
            ->take($limit)
            ->filter(fn ($dto) => $dto->total_spent > 0)
            ->values();
    }

    public function getSalesChartData(): array
    {
        return SalesInvoice::with('items')
            ->orderBy('invoice_date')
            ->get()
            ->groupBy(fn ($invoice) => $invoice->invoice_date->toDateString())
            ->map(fn ($invoices, $date) => new DashboardChartPointDTO(
                date: Carbon::parse($date),
                total: $invoices->sum(fn ($invoice) => $invoice->total_amount)
            ))
            ->values()
            ->toArray();
    }

    public function getPurchaseChartData(): array
    {
        return PurchaseInvoice::with('items')
            ->orderBy('posting_date')
            ->get()
            ->groupBy(fn ($purchase) => $purchase->posting_date->toDateString())
            ->map(fn ($purchases, $date) => new DashboardChartPointDTO(
                date: Carbon::parse($date),
                total: $purchases->sum(fn ($purchase) => $purchase->total_amount)
            ))
            ->values()
            ->toArray();
    }
}
