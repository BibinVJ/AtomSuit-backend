<?php

namespace App\Repositories;

use App\DataTransferObjects\DashboardCustomerDTO;
use App\DataTransferObjects\DashboardChartPointDTO;
use App\DataTransferObjects\DashboardExpiringItemDTO;
use App\DataTransferObjects\DashboardStockItemDTO;
use App\DataTransferObjects\DashboardTopItemDTO;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardRepository
{
    public function getTotalSalesAmount(): float
    {
        return Sale::with('items')->get()->sum(fn($sale) => $sale->total);
    }

    public function getTotalPurchaseAmount(): float
    {
        return Purchase::with('items')->get()->sum(fn($purchase) => $purchase->total);
    }

    public function getTopSellingItems(int $limit = 5): Collection
    {
        return StockMovement::selectRaw('item_id, ABS(SUM(quantity)) as total_quantity')
            ->where('source_type', Sale::class) // TODO: Will become DeliveryNote later
            ->groupBy('item_id')
            ->with('item:id,sku,name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn($row) => new DashboardTopItemDTO(
                id: $row->item_id,
                sku: $row->item->sku ?? '',
                name: $row->item->name ?? '',
                total_quantity: (int) $row->total_quantity
            ));
    }

    public function getTopPurchasedItems(int $limit = 5): Collection
    {
        return StockMovement::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->where('source_type', Purchase::class) // TODO: Change to GoodsReceivedNote::class, when using proper structure later
            ->groupBy('item_id')
            ->with('item:id,sku,name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn($row) => new DashboardTopItemDTO(
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
            // ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->get()
            ->map(fn($batch) => new DashboardExpiringItemDTO(
                id: $batch->item->id,
                sku: $batch->item->sku ?? '',
                name: $batch->item->name,
                batch_number: $batch->batch_number,
                expiry_date: $batch->expiry_date,
                stock_remaining: $batch->stockOnHand()
            ));
    }

    public function getOutOfStockItems(): Collection
    {
        return StockMovement::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->havingRaw('total_quantity <= 0')
            ->with('item:id,sku,name')
            ->get()
            ->map(fn($row) => new DashboardStockItemDTO(
                id: $row->item_id,
                sku: $row->item->sku ?? '',
                name: $row->item->name ?? '',
                stock_remaining: $row->total_quantity
            ));
    }

    public function getLowStockItems(): Collection
    {
        return StockMovement::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->havingRaw('total_quantity <= ' . 30) // TODO: later take this from settings
            ->with('item:id,sku,name')
            ->get()
            ->map(fn($row) => new DashboardStockItemDTO(
                id: $row->item_id,
                sku: $row->item->sku ?? '',
                name: $row->item->name ?? '',
                stock_remaining: $row->total_quantity
            ));
    }

    public function getDeadStockItems(int $days = 90): Collection
    {
        $cutoff = now()->subDays($days);

        return Item::whereDoesntHave('stockMovements', function ($query) use ($cutoff) {
                $query->where('source_type', Sale::class) // TODO: Later: change to DeliveryNote::class
                    ->where('created_at', '>=', $cutoff);
            })
            ->with('stockMovements')
            ->get()
            ->map(fn($item) => new DashboardStockItemDTO(
                id: $item->id,
                sku: $item->sku,
                name: $item->name,
                stock_remaining: $item->stockMovements->sum('quantity'),
            ))
            ->filter(fn($dto) => $dto->stock_remaining > 0);
    }

    public function getBestCustomers(int $limit = 5): Collection
    {
        return Customer::with('sales.items')
            ->get()
            ->map(fn($customer) => new DashboardCustomerDTO(
                id: $customer->id,
                name: $customer->name,
                email: $customer->email ?? null,
                phone: $customer->phone ?? null,
                total_spent: $customer->totalSpent()
            ))
            ->sortByDesc(fn($dto) => $dto->total_spent)
            ->take($limit)
            ->filter(fn($dto) => $dto->total_spent > 0)
            ->values();
    }

    public function getSalesChartData(): array
    {
        return Sale::with('items')
            ->orderBy('sale_date')
            ->limit(30)
            ->get()
            ->groupBy(fn($sale) => $sale->sale_date->toDateString())
            ->map(fn($sales, $date) => new DashboardChartPointDTO(
                date: Carbon::parse($date),
                total: $sales->sum(fn($sale) => $sale->total)
            ))
            ->values()
            ->toArray();
    }

    public function getPurchaseChartData(): array
    {
        return Purchase::with('items')
            ->orderBy('purchase_date')
            ->limit(30)
            ->get()
            ->groupBy(fn($purchase) => $purchase->purchase_date->toDateString())
            ->map(fn($purchases, $date) => new DashboardChartPointDTO(
                date: \Carbon\Carbon::parse($date),
                total: $purchases->sum(fn($purchase) => $purchase->total)
            ))
            ->values()
            ->toArray();
    }
}
