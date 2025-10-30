<?php

namespace Database\Seeders;

use App\Enums\TenantStatusEnum;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionItem;

class DefaultTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = Plan::where('name', 'Lifetime')->first();
        
        if (!$plan) {
            $this->command->error('Lifetime plan not found. Please run PlanSeeder first.');
            return;
        }

        if (!Tenant::where('email', 'company@example.com')->exists()) {
            $tenant = Tenant::create([
                'name' => 'company',
                'email' => 'company@example.com',
                'password' => Hash::make('Example@123'),
                'status' => TenantStatusEnum::ACTIVE->value,
                'trial_ends_at' => null,
                'grace_period_ends_at' => null,
                'email_verified_at' => now(),
                'plan_id' => $plan->id,
                'domain_name' => 'company',
                'load_sample_data' => true,
            ]);

            // Map plan to a Cashier subscription for this seeded tenant (lifetime)
            // Only create if not already present
            if (!Subscription::where('user_id', $tenant->id)->exists()) {
                $subscription = new Subscription();
                $subscription->user_id = $tenant->id;
                $subscription->name = 'default';
                $subscription->stripe_id = 'seed_lifetime_' . $tenant->id;
                $subscription->stripe_status = 'active';
                $subscription->stripe_price = $plan->stripe_price_id; // may be null for lifetime
                $subscription->quantity = 1;
                $subscription->trial_ends_at = null;
                $subscription->ends_at = null;
                $subscription->save();

                // Create subscription item mapping to plan's Stripe price/product when available
                $item = new SubscriptionItem();
                $item->subscription_id = $subscription->id;
                $item->stripe_id = 'seed_item_' . $tenant->id;
                $item->stripe_product = $plan->stripe_product_id ?? ('lifetime_product_' . $tenant->id);
                $item->stripe_price = $plan->stripe_price_id ?? ('lifetime_price_' . $tenant->id);
                $item->quantity = 1;
                $item->save();
            }
        }
    }
}
