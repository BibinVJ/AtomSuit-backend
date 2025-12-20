<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\PlanService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    public function __construct(protected PlanService $planService) {}

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Expired User',
                'price' => 0,
                'interval' => 'lifetime',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration_in_days' => null,
                'is_expired_user_plan' => true,
                'features' => [
                    ['key' => 'device_limit', 'value' => '0', 'type' => 'integer', 'name' => 'Devices', 'desc' => 'Number of devices', 'order' => 1],
                    ['key' => 'storage_gb', 'value' => '0', 'type' => 'integer', 'name' => 'Storage (GB)', 'desc' => 'Storage space in GB', 'order' => 2],
                    ['key' => 'api_access', 'value' => 'false', 'type' => 'boolean', 'name' => 'API Access', 'desc' => 'REST API access', 'order' => 3],
                ],
            ],
            [
                'name' => 'Trial Plan',
                'price' => 0,
                'interval' => 'day',
                'interval_count' => 14,
                'is_trial_plan' => true,
                'trial_duration_in_days' => 14,
                'is_expired_user_plan' => false,
                'features' => [
                    ['key' => 'device_limit', 'value' => '1', 'type' => 'integer', 'name' => 'Devices', 'desc' => 'Number of devices', 'order' => 1],
                    ['key' => 'storage_gb', 'value' => '10', 'type' => 'integer', 'name' => 'Storage (GB)', 'desc' => 'Storage space in GB', 'order' => 2],
                    ['key' => 'api_access', 'value' => 'false', 'type' => 'boolean', 'name' => 'API Access', 'desc' => 'REST API access', 'order' => 3],
                ],
            ],
            [
                'name' => 'Standard Monthly',
                'price' => 100,
                'interval' => 'month',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration_in_days' => null,
                'is_expired_user_plan' => false,
                'features' => [
                    ['key' => 'device_limit', 'value' => '3', 'type' => 'integer', 'name' => 'Devices', 'desc' => 'Number of devices', 'order' => 1],
                    ['key' => 'storage_gb', 'value' => '100', 'type' => 'integer', 'name' => 'Storage (GB)', 'desc' => 'Storage space in GB', 'order' => 2],
                    ['key' => 'api_access', 'value' => 'true', 'type' => 'boolean', 'name' => 'API Access', 'desc' => 'REST API access', 'order' => 3],
                ],
            ],
            [
                'name' => 'Standard Yearly',
                'price' => 1000,
                'interval' => 'year',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration_in_days' => null,
                'is_expired_user_plan' => false,
                'features' => [
                    ['key' => 'device_limit', 'value' => '3', 'type' => 'integer', 'name' => 'Devices', 'desc' => 'Number of devices', 'order' => 1],
                    ['key' => 'storage_gb', 'value' => '100', 'type' => 'integer', 'name' => 'Storage (GB)', 'desc' => 'Storage space in GB', 'order' => 2],
                    ['key' => 'api_access', 'value' => 'true', 'type' => 'boolean', 'name' => 'API Access', 'desc' => 'REST API access', 'order' => 3],
                ],
            ],
            [
                'name' => 'Lifetime',
                'price' => 3000,
                'interval' => 'lifetime',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration_in_days' => null,
                'is_expired_user_plan' => false,
                'features' => [
                    ['key' => 'device_limit', 'value' => '-1', 'type' => 'integer', 'name' => 'Devices', 'desc' => 'Unlimited devices', 'order' => 1],
                    ['key' => 'storage_gb', 'value' => '-1', 'type' => 'integer', 'name' => 'Storage (GB)', 'desc' => 'Unlimited storage', 'order' => 2],
                    ['key' => 'api_access', 'value' => 'true', 'type' => 'boolean', 'name' => 'API Access', 'desc' => 'Full REST API access', 'order' => 3],
                ],
            ],
        ];

        $secret = config('services.stripe.secret');
        if (! $secret) {
            Log::info('PlanSeeder: STRIPE_SECRET missing; skipping Stripe product/price creation.');
        }

        foreach ($plans as $payload) {
            // Extract features before creating plan
            $features = $payload['features'];
            unset($payload['features']);

            /** @var Plan $plan */
            $plan = Plan::updateOrCreate(['name' => $payload['name']], $payload);

            // Prepare interval value (handle Enum cast)
            $intervalValue = $plan->interval->value;

            // Link to Stripe for paid recurring plans (not lifetime/free/trial)
            $isRecurring = $intervalValue !== 'lifetime' && (float) $plan->price > 0;

            if ($secret && $isRecurring && ! $plan->stripe_product_id) {
                try {
                    // Use PlanService to find or create Stripe product and price
                    $slug = Str::slug($plan->name);
                    $lookupKey = "plan_{$slug}_{$intervalValue}_{$plan->interval_count}_{$plan->price}";

                    $stripeProduct = $this->planService->findOrCreateStripeProduct($plan->name);
                    $stripePrice = $this->planService->findOrCreateStripePrice(
                        $stripeProduct->id,
                        (float) $plan->price,
                        $lookupKey,
                        false,
                        $intervalValue,
                        $plan->interval_count
                    );

                    $plan->stripe_product_id = $stripeProduct->id;
                    $plan->stripe_price_id = $stripePrice->id;
                    $plan->save();

                    Log::info('PlanSeeder: Synced plan with Stripe', [
                        'plan' => $plan->name,
                        'product_id' => $stripeProduct->id,
                        'price_id' => $stripePrice->id,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('PlanSeeder: Stripe sync failed for plan '.$plan->name.' - '.$e->getMessage());
                }
            }

            // Add features to plan
            foreach ($features as $feature) {
                PlanFeature::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature_key' => $feature['key'],
                    ],
                    [
                        'feature_type' => $feature['type'],
                        'feature_value' => $feature['value'],
                        'display_name' => $feature['name'],
                        'description' => $feature['desc'],
                        'display_order' => $feature['order'],
                    ]
                );
            }
        }

        Log::info('PlanSeeder: Plans and features seeded successfully');
    }
}
