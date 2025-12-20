<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Repositories\PlanRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlanService
{
    public function __construct(protected PlanRepository $planRepository) {}

    /**
     * Find or create Stripe product by name.
     *
     * @param string $name
     * @param string|null $description
     * @return \Stripe\Product
     */
    public function findOrCreateStripeProduct(string $name, ?string $description = null): \Stripe\Product
    {
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        // Search for existing product by name
        $existingProducts = $stripe->products->search([
            'query' => "name:'{$name}'",
        ]);

        if ($existingProducts->count() > 0) {
            Log::info('Found existing Stripe product', ['product_id' => $existingProducts->data[0]->id]);
            return $existingProducts->data[0];
        }

        // Create new product - only include description if not empty
        $productData = ['name' => $name];
        
        if (!empty($description)) {
            $productData['description'] = $description;
        }
        
        $stripeProduct = $stripe->products->create($productData);
        
        Log::info('Created new Stripe product', ['product_id' => $stripeProduct->id]);
        return $stripeProduct;
    }

    /**
     * Find or create Stripe price by lookup key.
     *
     * @param string $productId
     * @param float $price
     * @param string $lookupKey
     * @param bool $isTrialPlan
     * @param string $interval
     * @param int $intervalCount
     * @return \Stripe\Price
     */
    public function findOrCreateStripePrice(
        string $productId,
        float $price,
        string $lookupKey,
        bool $isTrialPlan = false,
        string $interval = 'month',
        int $intervalCount = 1
    ): \Stripe\Price {
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        // Search for existing price by lookup key
        $existingPrices = $stripe->prices->all([
            'lookup_keys' => [$lookupKey],
        ]);

        if (count($existingPrices->data) > 0) {
            Log::info('Found existing Stripe price', ['price_id' => $existingPrices->data[0]->id]);
            return $existingPrices->data[0];
        }

        // Create new price
        $priceData = [
            'product' => $productId,
            'unit_amount' => (int)($price * 100), // Convert to cents
            'currency' => 'usd',
            'lookup_key' => $lookupKey,
        ];

        if (!$isTrialPlan) {
            $priceData['recurring'] = [
                'interval' => $interval,
                'interval_count' => $intervalCount,
            ];
        }

        $stripePrice = $stripe->prices->create($priceData);
        Log::info('Created new Stripe price', ['price_id' => $stripePrice->id]);
        
        return $stripePrice;
    }

    /**
     * Create a plan and sync with Stripe.
     *
     * @param array $data
     * @return Plan
     */
    public function create(array $data): Plan
    {
        try {
            return DB::transaction(function () use ($data) {
                // Extract features before creating plan
                $features = $data['features'] ?? [];
                unset($data['features']);

                // Generate slug and lookup key
                $slug = Str::slug($data['name']);
                $lookupKey = "plan_{$slug}_{$data['interval']}_{$data['interval_count']}_{$data['price']}";

                // Find or create Stripe product
                $stripeProduct = $this->findOrCreateStripeProduct(
                    $data['name'],
                    $data['description'] ?? null
                );

                // Find or create Stripe price
                $stripePrice = $this->findOrCreateStripePrice(
                    $stripeProduct->id,
                    $data['price'],
                    $lookupKey,
                    $data['is_trial_plan'] ?? false,
                    $data['interval'] ?? 'month',
                    $data['interval_count'] ?? 1
                );

                // Create plan in database
                $data['stripe_product_id'] = $stripeProduct->id;
                $data['stripe_price_id'] = $stripePrice->id;
                
                $plan = $this->planRepository->create($data);

                // Create features
                $this->syncFeatures($plan, $features);

                return $plan->load('features');
            });
        } catch (\Exception $e) {
            Log::error('Failed to create plan with Stripe sync', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw new Exception('Failed to sync plan with Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Update a plan and sync with Stripe.
     *
     * @param Plan $plan
     * @param array $data
     * @return Plan
     */
    public function update(Plan $plan, array $data): Plan
    {
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        
        try {
            return DB::transaction(function () use ($plan, $data, $stripe) {
                // Extract features before updating plan
                $features = $data['features'] ?? null;
                unset($data['features']);

                // Update Stripe product if name changed
                if (isset($data['name']) && $data['name'] !== $plan->name && $plan->stripe_product_id) {
                    $stripe->products->update($plan->stripe_product_id, [
                        'name' => $data['name'],
                    ]);
                    Log::info('Updated Stripe product', ['product_id' => $plan->stripe_product_id]);
                }

                // If price or interval changed, create new price (Stripe prices are immutable)
                $priceChanged = isset($data['price']) && $data['price'] != $plan->price;
                $intervalChanged = isset($data['interval']) && $data['interval'] != $plan->interval;
                $intervalCountChanged = isset($data['interval_count']) && $data['interval_count'] != $plan->interval_count;

                if (($priceChanged || $intervalChanged || $intervalCountChanged) && $plan->stripe_product_id) {
                    $slug = Str::slug($data['name'] ?? $plan->name);
                    $price = $data['price'] ?? $plan->price;
                    $interval = $data['interval'] ?? $plan->interval;
                    $intervalCount = $data['interval_count'] ?? $plan->interval_count;
                    // Add timestamp to make lookup key unique for price updates
                    $timestamp = time();
                    $lookupKey = "plan_{$slug}_{$interval}_{$intervalCount}_{$price}_{$timestamp}";

                    $priceData = [
                        'product' => $plan->stripe_product_id,
                        'unit_amount' => $price * 100,
                        'currency' => 'usd',
                        'lookup_key' => $lookupKey,
                        'metadata' => ['plan_slug' => $slug, 'plan_id' => $plan->id],
                    ];

                    if (!($data['is_trial_plan'] ?? $plan->is_trial_plan)) {
                        $priceData['recurring'] = [
                            'interval' => $interval,
                            'interval_count' => $intervalCount,
                        ];
                    }

                    $stripePrice = $stripe->prices->create($priceData);
                    $data['stripe_price_id'] = $stripePrice->id;
                    
                    Log::info('Created new Stripe price for updated plan', ['price_id' => $stripePrice->id]);
                }

                $updatedPlan = $this->planRepository->update($plan, $data);

                // Update features if provided
                if ($features !== null) {
                    $this->syncFeatures($updatedPlan, $features);
                }

                return $updatedPlan->load('features');
            });
        } catch (\Exception $e) {
            Log::error('Failed to update plan with Stripe sync', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to sync plan update with Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete plan by marking as inactive.
     * Does not actually delete the plan from database.
     *
     * @param Plan $plan
     * @return Plan
     */
    public function delete(Plan $plan): Plan
    {
        // Soft delete the plan
        $plan->delete();
        return $plan;
    }

    /**
     * Sync features for a plan.
     * Features array format:
     * [
     *   ['key' => 'device_limit', 'value' => '3', 'type' => 'integer', 'display_name' => 'Devices', ...],
     *   ['key' => 'storage_gb', 'value' => '100', 'type' => 'integer', ...],
     * ]
     * Or update existing by id:
     * [
     *   ['id' => 1, 'value' => '5'],
     *   ['key' => 'new_feature', 'value' => 'true', ...],
     * ]
     *
     * @param Plan $plan
     * @param array $features
     * @return void
     */
    protected function syncFeatures(Plan $plan, array $features): void
    {
        $processedIds = [];

        foreach ($features as $featureData) {
            // Update existing feature by ID
            if (isset($featureData['id'])) {
                $feature = PlanFeature::where('plan_id', $plan->id)
                    ->where('id', $featureData['id'])
                    ->first();
                
                if ($feature) {
                    $feature->update($featureData);
                    $processedIds[] = $feature->id;
                }
            }
            // Create or update by feature_key
            elseif (isset($featureData['key'])) {
                $feature = PlanFeature::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature_key' => $featureData['key'],
                    ],
                    [
                        'feature_type' => $featureData['type'] ?? 'string',
                        'feature_value' => $featureData['value'],
                        'display_name' => $featureData['display_name'] ?? $featureData['key'],
                        'description' => $featureData['description'] ?? null,
                        'display_order' => $featureData['display_order'] ?? 0,
                    ]
                );
                $processedIds[] = $feature->id;
            }
        }

        // Remove features that weren't in the update (if any IDs were processed)
        // Only delete if features array was provided and processed
        if (!empty($processedIds)) {
            PlanFeature::where('plan_id', $plan->id)
                ->whereNotIn('id', $processedIds)
                ->delete();
        }
    }
}
