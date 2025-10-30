<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class PlanSeeder extends Seeder
{
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
            ],
            [
                'name' => 'Trial Plan',
                'price' => 0,
                'interval' => 'day',
                'interval_count' => 14, // trial lasts 14 days
                'is_trial_plan' => true,
                'trial_duration_in_days' => 14,
                'is_expired_user_plan' => false,
            ],
            [
                'name' => 'Standard Monthly',
                'price' => 100,
                'interval' => 'month',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration_in_days' => null,
                'is_expired_user_plan' => false,
            ],
            [
                'name' => 'Standard Yearly',
                'price' => 1000,
                'interval' => 'year',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration_in_days' => null,
                'is_expired_user_plan' => false,
            ],
            [
                'name' => 'Lifetime',
                'price' => 3000,
                'interval' => 'lifetime',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration_in_days' => null,
                'is_expired_user_plan' => false,
            ],
        ];

        $client = null;
        $secret = env('STRIPE_SECRET');
        if ($secret) {
            try {
                $client = new StripeClient($secret);
            } catch (\Throwable $e) {
                Log::warning('PlanSeeder: Unable to initialize Stripe client: '.$e->getMessage());
            }
        } else {
            Log::info('PlanSeeder: STRIPE_SECRET missing; skipping Stripe product/price creation.');
        }

        foreach ($plans as $payload) {
            /** @var Plan $plan */
            $plan = Plan::updateOrCreate(['name' => $payload['name']], $payload);

            // Prepare interval value (handle Enum cast)
            $intervalValue = is_object($plan->interval) && property_exists($plan->interval, 'value')
                ? $plan->interval->value
                : (string) $plan->interval;

            // Link to Stripe for paid recurring plans (not lifetime/free/trial)
            $isRecurring = $intervalValue !== 'lifetime' && (float)$plan->price > 0;
            if ($client && $isRecurring) {
                try {
                    // Ensure Product
                    if (empty($plan->stripe_product_id)) {
                        $product = $client->products->create([
                            'name' => $plan->name,
                            'metadata' => [
                                'plan_id' => (string) $plan->id,
                            ],
                        ]);
                        $plan->stripe_product_id = $product->id;
                    }

                    // Ensure Price (create if missing)
                    if (empty($plan->stripe_price_id)) {
                        $amount = (int) round((float)$plan->price * 100);
                        $currency = strtolower((string) config('cashier.currency', 'usd'));
                        $interval = $intervalValue; // 'month', 'year', 'day', 'week'
                        $count = max(1, (int) $plan->interval_count);

                        $lookupKey = sprintf(
                            'plan_%s_%s_%d_%d',
                            Str::slug($plan->name),
                            $interval,
                            $count,
                            $amount
                        );

                        // Try reuse by lookup key if exists
                        $existing = null;
                        try {
                            $list = $client->prices->all(['lookup_keys' => [$lookupKey], 'limit' => 1]);
                            if (!empty($list->data)) {
                                $existing = $list->data[0];
                            }
                        } catch (\Throwable $ignored) {
                        }

                        if ($existing) {
                            $plan->stripe_price_id = $existing->id;
                        } else {
                            $price = $client->prices->create([
                                'unit_amount' => $amount,
                                'currency' => $currency,
                                'recurring' => [
                                    'interval' => $interval,
                                    'interval_count' => $count,
                                ],
                                'product' => $plan->stripe_product_id,
                                'lookup_key' => $lookupKey,
                                'metadata' => [
                                    'plan_id' => (string) $plan->id,
                                ],
                            ]);
                            $plan->stripe_price_id = $price->id;
                        }
                    }

                    $plan->save();
                } catch (\Throwable $e) {
                    Log::warning('PlanSeeder: Stripe sync failed for plan '.$plan->name.' - '.$e->getMessage());
                }
            }
        }
    }
}
