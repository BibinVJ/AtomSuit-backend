<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeSubscriptionService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Cancel all active Stripe subscriptions for a tenant.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function cancelTenantSubscriptions(Tenant $tenant): void
    {
        $subscriptions = $tenant->subscriptions()
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->cancelSubscription($subscription);
        }
    }

    /**
     * Cancel a single Stripe subscription.
     *
     * @param Subscription $subscription
     * @return void
     */
    public function cancelSubscription(Subscription $subscription): void
    {
        // Skip manual subscriptions (offline payments)
        if (str_starts_with($subscription->stripe_id, 'manual_')) {
            Log::info('Skipping manual subscription cancellation', [
                'subscription_id' => $subscription->stripe_id,
            ]);
            return;
        }

        try {
            $this->stripe->subscriptions->cancel($subscription->stripe_id);

            Log::info('Stripe subscription cancelled', [
                'subscription_id' => $subscription->stripe_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cancel Stripe subscription', [
                'subscription_id' => $subscription->stripe_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync Stripe subscription data with local database.
     * Creates or updates subscription record and links it to the plan.
     *
     * @param Tenant $tenant
     * @param array|\Stripe\Subscription $stripeSubscription
     * @return Subscription
     */
    public function syncSubscription(Tenant $tenant, $stripeSubscription): Subscription
    {
        // Handle both array and object formats
        $subscriptionId = is_array($stripeSubscription) 
            ? $stripeSubscription['id'] 
            : $stripeSubscription->id;

        $status = is_array($stripeSubscription)
            ? $stripeSubscription['status']
            : $stripeSubscription->status;

        // Get items
        $items = is_array($stripeSubscription) 
            ? $stripeSubscription['items']['data'] 
            : $stripeSubscription->items->data;
        
        $firstItem = $items[0];
        $priceId = is_array($firstItem) 
            ? $firstItem['price']['id'] 
            : $firstItem->price->id;
        
        $quantity = is_array($firstItem)
            ? $firstItem['quantity']
            : $firstItem->quantity;

        // Find plan by stripe price ID
        $plan = Plan::where('stripe_price_id', $priceId)->first();

        // Get trial and end dates
        $trialEnd = is_array($stripeSubscription)
            ? ($stripeSubscription['trial_end'] ?? null)
            : $stripeSubscription->trial_end;
        
        $endedAt = is_array($stripeSubscription)
            ? ($stripeSubscription['ended_at'] ?? null)
            : $stripeSubscription->ended_at;

        // Create or update subscription
        $subscription = $tenant->subscriptions()->updateOrCreate(
            ['stripe_id' => $subscriptionId],
            [
                'name' => 'default',
                'type' => 'default',
                'stripe_status' => $status,
                'stripe_price' => $priceId,
                'quantity' => $quantity,
                'trial_ends_at' => $trialEnd ? Carbon::createFromTimestamp($trialEnd) : null,
                'ends_at' => $endedAt ? Carbon::createFromTimestamp($endedAt) : null,
                'plan_id' => $plan ? $plan->id : null,
            ]
        );

        // Update tenant's plan_id to reflect the paid plan
        if ($plan) {
            $tenant->update(['plan_id' => $plan->id]);
            
            Log::info('Subscription synced with plan', [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscriptionId,
                'plan_id' => $plan->id,
            ]);
        }

        return $subscription;
    }

    /**
     * Retrieve Stripe subscription by ID.
     *
     * @param string $subscriptionId
     * @return \Stripe\Subscription
     */
    public function retrieveSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->stripe->subscriptions->retrieve($subscriptionId);
    }
}
