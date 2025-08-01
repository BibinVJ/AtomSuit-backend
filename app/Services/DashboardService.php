<?php

namespace App\Services;

use App\DataTransferObjects\DashboardMetricsDTO;
use App\Models\DashboardLayout;
use App\Repositories\CustomerRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\ItemRepository;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function __construct(
        protected DashboardRepository $dashboardRepo,
        protected CustomerRepository $customerRepository,
        protected ItemRepository $itemRepository
    ) {}

    public function getMetrics(): DashboardMetricsDTO
    {
        return new DashboardMetricsDTO(
            total_sales_amount: $this->dashboardRepo->getTotalSalesAmount(),
            total_purchase_amount: $this->dashboardRepo->getTotalPurchaseAmount(),
            total_customers: $this->customerRepository->all()->count(),
            total_items: $this->itemRepository->all()->count(),
            top_selling_items: $this->dashboardRepo->getTopSellingItems(),
            top_purchased_items: $this->dashboardRepo->getTopPurchasedItems(),
            expiring_items: $this->dashboardRepo->getExpiringItems(),
            out_of_stock_items: $this->dashboardRepo->getOutOfStockItems(),
            low_stock_items: $this->dashboardRepo->getLowStockItems(),
            dead_stock_items: $this->dashboardRepo->getDeadStockItems(),
            best_customers: $this->dashboardRepo->getBestCustomers(),
            sales_chart_data: $this->dashboardRepo->getSalesChartData(),
            purchase_chart_data: $this->dashboardRepo->getPurchaseChartData()
        );
    }

    public function getLayouts()
    {
        return Auth::user()->dashboardLayouts;
    }

    public function updateLayout(array $data)
    {
        foreach ($data['layouts'] as $layout) {
            DashboardLayout::updateOrCreate(
                ['user_id' => Auth::id(), 'card_id' => $layout['card_id']],
                [
                    'area' => $layout['area'] ?? null,
                    'x' => $layout['x'] ?? null,
                    'y' => $layout['y'] ?? null,
                    'rotation' => $layout['rotation'] ?? 0,
                    'width' => $layout['width'] ?? null,
                    'height' => $layout['height'] ?? null,
                    'col_span' => $layout['col_span'] ?? 0,
                    'draggable' => $layout['draggable'] ?? true,
                    'visible' => $layout['visible'],
                    'config' => $layout['config'] ?? null,
                ]
            );
        }

        return $this->getLayouts();
    }
}
