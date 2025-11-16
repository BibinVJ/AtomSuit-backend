<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Models\DashboardCard;
use Illuminate\Database\Seeder;

class CentralDashboardCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cards = [
            [
                'card_id' => 'total-tenants',
                'title' => 'Total Tenants',
                'component' => 'TotalTenantsCard',
                'description' => 'Total number of tenants',
                'permission' => PermissionsEnum::VIEW_TENANT->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 0,
                'default_y' => 0,
                'default_order' => 1,
            ],
            [
                'card_id' => 'active-tenants',
                'title' => 'Active Tenants',
                'component' => 'ActiveTenantsCard',
                'description' => 'Number of active tenants',
                'permission' => PermissionsEnum::VIEW_TENANT->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 6,
                'default_y' => 0,
                'default_order' => 2,
            ],
            [
                'card_id' => 'paid-subscribers',
                'title' => 'Paid Subscribers',
                'component' => 'PaidSubscribersCard',
                'description' => 'Number of paid subscribers',
                'permission' => PermissionsEnum::VIEW_SUBSCRIPTION->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 12,
                'default_y' => 0,
                'default_order' => 3,
            ],
            [
                'card_id' => 'total-revenue',
                'title' => 'Total Revenue',
                'component' => 'TotalRevenueCard',
                'description' => 'Total revenue from subscriptions',
                'permission' => PermissionsEnum::VIEW_SUBSCRIPTION->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 18,
                'default_y' => 0,
                'default_order' => 4,
            ],
            [
                'card_id' => 'trial-tenants',
                'title' => 'Trial Tenants',
                'component' => 'TrialTenantsCard',
                'description' => 'Number of tenants on trial',
                'permission' => PermissionsEnum::VIEW_TENANT->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 0,
                'default_y' => 4,
                'default_order' => 5,
            ],
            [
                'card_id' => 'monthly-revenue',
                'title' => 'Monthly Revenue',
                'component' => 'MonthlyRevenueCard',
                'description' => 'Revenue for current month',
                'permission' => PermissionsEnum::VIEW_SUBSCRIPTION->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 6,
                'default_y' => 4,
                'default_order' => 6,
            ],
            [
                'card_id' => 'plan-distribution',
                'title' => 'Plan Distribution',
                'component' => 'PlanDistributionCard',
                'description' => 'Breakdown of tenants by plan',
                'permission' => PermissionsEnum::CREATE_PLAN->value,
                'default_width' => 12,
                'default_height' => 6,
                'default_x' => 12,
                'default_y' => 4,
                'default_order' => 7,
            ],
            [
                'card_id' => 'recent-registrations',
                'title' => 'Recent Registrations',
                'component' => 'RecentRegistrationsCard',
                'description' => 'New tenant registrations (last 30 days)',
                'permission' => PermissionsEnum::VIEW_TENANT->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 0,
                'default_y' => 10,
                'default_order' => 8,
            ],
            [
                'card_id' => 'conversion-rate',
                'title' => 'Conversion Rate',
                'component' => 'ConversionRateCard',
                'description' => 'Trial to paid conversion rate',
                'permission' => PermissionsEnum::VIEW_SUBSCRIPTION->value,
                'default_width' => 6,
                'default_height' => 4,
                'default_x' => 6,
                'default_y' => 10,
                'default_order' => 9,
            ],
            [
                'card_id' => 'tenant-overview',
                'title' => 'Tenant Overview',
                'component' => 'TenantOverviewCard',
                'description' => 'Comprehensive tenant statistics breakdown',
                'permission' => PermissionsEnum::VIEW_TENANT->value,
                'default_width' => 12,
                'default_height' => 6,
                'default_x' => 12,
                'default_y' => 10,
                'default_order' => 10,
            ],
            [
                'card_id' => 'revenue-overview',
                'title' => 'Revenue Overview',
                'component' => 'RevenueOverviewCard',
                'description' => 'Total and monthly revenue breakdown',
                'permission' => PermissionsEnum::VIEW_SUBSCRIPTION->value,
                'default_width' => 8,
                'default_height' => 5,
                'default_x' => 0,
                'default_y' => 16,
                'default_order' => 11,
            ],
            [
                'card_id' => 'growth-metrics',
                'title' => 'Growth Metrics',
                'component' => 'GrowthMetricsCard',
                'description' => 'Growth and conversion metrics',
                'permission' => PermissionsEnum::VIEW_TENANT->value,
                'default_width' => 8,
                'default_height' => 5,
                'default_x' => 8,
                'default_y' => 16,
                'default_order' => 12,
            ],
        ];

        foreach ($cards as $card) {
            DashboardCard::updateOrCreate(
                ['card_id' => $card['card_id']],
                $card
            );
        }
    }
}
