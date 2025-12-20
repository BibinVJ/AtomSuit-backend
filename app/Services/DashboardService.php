<?php

namespace App\Services;

use App\DataTransferObjects\DashboardMetricsDTO;
use App\Models\DashboardCard;
use App\Models\DashboardLayout;
use App\Repositories\CustomerRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\ItemRepository;
use App\Services\TenantService;
use Illuminate\Support\Facades\Auth;

class DashboardService extends ContextAwareService
{
    public function __construct(
        protected DashboardRepository $dashboardRepo,
        protected CustomerRepository $customerRepository,
        protected ItemRepository $itemRepository,
        protected TenantService $tenantService
    ) {}

    public function getMetrics(): DashboardMetricsDTO|array
    {
        // Check if central context (superadmin)
        if ($this->isCentralContext()) {
            return $this->getCentralMetrics();
        }

        // Tenant context - return tenant-specific metrics
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

    /**
     * Get dashboard metrics for central/superadmin.
     */
    protected function getCentralMetrics(): array
    {
        $stats = $this->tenantService->getStats();

        return [
            'tenant_overview' => $stats['overview'],
            'plan_distribution' => $stats['plans'],
            'revenue' => $stats['revenue'],
            'growth' => $stats['growth'],
        ];
    }

    public function getLayouts()
    {
        $user = Auth::user();
        $existingLayouts = DashboardLayout::with('card')
            ->where('user_id', $user->id)
            ->get();

        // If user has no layouts, initialize from available cards based on permissions
        if ($existingLayouts->isEmpty()) {
            $this->initializeUserLayouts($user);
            $existingLayouts = DashboardLayout::with('card')
                ->where('user_id', $user->id)
                ->get();
        }

        return $existingLayouts ?? collect();
    }

    /**
     * Initialize dashboard layouts for user based on card definitions and permissions.
     */
    protected function initializeUserLayouts($user): void
    {
        $cards = DashboardCard::orderBy('default_order')
            ->get();

        foreach ($cards as $card) {
            // Check if user has permission for this card
            if ($card->permission && !$user->can($card->permission)) {
                continue;
            }

            DashboardLayout::create([
                'user_id' => $user->id,
                'dashboard_card_id' => $card->id,
                'x' => $card->default_x ?? 0,
                'y' => $card->default_y ?? $card->default_order,
                'width' => $card->default_width,
                'height' => $card->default_height,
                'visible' => true,
                'draggable' => true,
                'config' => $card->default_config,
            ]);
        }
    }

    /**
     * Get all available dashboard cards for the authenticated user based on permissions.
     */
    public function getAvailableCards()
    {
        // Central context doesn't have cards
        if ($this->isCentralContext()) {
            return collect();
        }

        $user = Auth::user();
        
        return DashboardCard::orderBy('default_order')
            ->get()
            ->filter(function ($card) use ($user) {
                // If card has permission requirement, check it
                return !$card->permission || $user->can($card->permission);
            })
            ->values();
    }

    public function updateLayout(array $data)
    {
        // Central context (superadmin) doesn't have dashboard layouts
        if ($this->isCentralContext()) {
            return collect(); // Return empty collection
        }

        foreach ($data['layouts'] as $layout) {
            DashboardLayout::updateOrCreate(
                ['user_id' => Auth::id(), 'dashboard_card_id' => $layout['dashboard_card_id']],
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
