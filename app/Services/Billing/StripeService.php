<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StripeService
{
    protected StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    public function ensureCustomer(Tenant $tenant): string
    {
        $customerId = $tenant->data['stripe_customer_id'] ?? $tenant->stripe_customer_id ?? null;
        if ($customerId) {
            return $customerId;
        }

        $customer = $this->client->customers->create([
            'email' => $tenant->email,
            'name' => $tenant->name,
            'metadata' => [
                'tenant_id' => $tenant->id,
            ],
        ]);

        // Save to tenant data (non-persisted attributes may not exist), persist to data json or dedicated column
        $tenant->update(['data' => array_merge($tenant->data ?? [], ['stripe_customer_id' => $customer->id])]);

        return $customer->id;
    }

    public function createCheckoutSession(Tenant $tenant, Plan $plan, string $successUrl, string $cancelUrl)
    {
        if (empty($plan->stripe_price_id)) {
            throw new UnprocessableEntityHttpException('Plan is not linked to Stripe.');
        }

        $customerId = $this->ensureCustomer($tenant);

        return $this->client->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [[
                'price' => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $tenant->id,
            'metadata' => [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
            ],
        ]);
    }

    public function handleWebhook(\Illuminate\Http\Request $request): void
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if ($secret) {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } else {
            $event = json_decode($payload);
        }

        $type = $event->type ?? $event['type'] ?? null;
        $data = $event->data->object ?? $event['data']['object'] ?? null;

        switch ($type) {
            case 'checkout.session.completed':
                $this->onCheckoutCompleted($data);
                break;
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $this->onSubscriptionChanged($data);
                break;
            case 'invoice.paid':
            case 'invoice.payment_failed':
                // Optionally store invoices
                break;
            default:
                // ignore
                break;
        }
    }

    protected function onCheckoutCompleted($session): void
    {
        $tenantId = $session->client_reference_id ?? null;
        $customerId = $session->customer ?? null;
        if (!$tenantId || !$customerId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenant->update(['data' => array_merge($tenant->data ?? [], ['stripe_customer_id' => $customerId])]);
    }

    protected function onSubscriptionChanged($subscription): void
    {
        $customerId = $subscription->customer ?? null;
        if (!$customerId) return;

        $tenant = Tenant::where('data->stripe_customer_id', $customerId)->first();
        if (!$tenant) return;

        $stripeSubId = $subscription->id;
        $status = $subscription->status; // trialing, active, past_due, canceled, unpaid
        $currentPeriodEnd = isset($subscription->current_period_end) ? \Carbon\Carbon::createFromTimestamp($subscription->current_period_end) : null;
        $cancelAtPeriodEnd = (bool)($subscription->cancel_at_period_end ?? false);

        // Find plan by price id if present
        $priceId = $subscription->items->data[0]->price->id ?? null;
        $plan = $priceId ? Plan::where('stripe_price_id', $priceId)->first() : null;

        // Deactivate existing subscriptions (mark as ended)
        $tenant->subscriptions()->whereNotIn('stripe_status', ['canceled'])->update(['ends_at' => now(), 'stripe_status' => 'canceled']);

        // Upsert subscription row
        Subscription::updateOrCreate(
            ['tenant_id' => $tenant->id, 'gateway_subscription_id' => $stripeSubId],
            [
                'plan_id' => $plan?->id,
                'start_date' => now(),
                'end_date' => null,
                'trial_ends_at' => $status === 'trialing' ? $currentPeriodEnd : null,
                'stripe_status' => $status,
                'payment_gateway' => 'stripe',
                'gateway_subscription_id' => $stripeSubId,
                'renewal_type' => 'auto',
            ]
        );
    }
}