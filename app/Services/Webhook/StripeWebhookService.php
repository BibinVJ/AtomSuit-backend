<?php

namespace App\Services\Webhook;

use App\Models\SubscriptionInvoice;
use App\Models\Tenant;
use App\Services\StripeSubscriptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StripeWebhookService
{
    public function __construct(
        protected StripeSubscriptionService $subscriptionService
    ) {
    }

    /**
     * Handle checkout session completed event.
     *
     * @param array $session
     * @return void
     */
    public function handleCheckoutSessionCompleted(array $session): void
    {
        Log::info('Stripe Checkout Session Completed', [
            'session_id' => $session['id'],
            'customer' => $session['customer'] ?? null,
            'subscription' => $session['subscription'] ?? null,
        ]);

        $tenantId = $session['client_reference_id'] ?? $session['metadata']['tenant_id'] ?? null;
        $customerId = $session['customer'] ?? null;
        $subscriptionId = $session['subscription'] ?? null;

        if (!$tenantId) {
            Log::warning('Stripe webhook: No tenant_id in checkout session', ['session_id' => $session['id']]);
            return;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            Log::warning('Stripe webhook: Tenant not found', ['tenant_id' => $tenantId]);
            return;
        }

        // Update tenant with Stripe customer ID
        if ($customerId && !$tenant->stripe_id) {
            $tenant->update(['stripe_id' => $customerId]);
        }

        // Store customer ID in tenant data as well for compatibility
        if ($customerId) {
            $data = $tenant->data ?? [];
            $data['stripe_customer_id'] = $customerId;
            $tenant->update(['data' => $data]);
        }

        // If subscription ID is available, fetch and sync it
        if ($subscriptionId) {
            try {
                $stripeSubscription = $this->subscriptionService->retrieveSubscription($subscriptionId);
                $this->subscriptionService->syncSubscription($tenant, $stripeSubscription);
            } catch (\Exception $e) {
                Log::error('Failed to retrieve subscription after checkout', [
                    'subscription_id' => $subscriptionId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle subscription created event.
     *
     * @param array $subscription
     * @param Tenant|null $tenant
     * @return void
     */
    public function handleSubscriptionCreated(array $subscription, ?Tenant $tenant): void
    {
        if (!$tenant) {
            return;
        }

        Log::info('Stripe Subscription Created', [
            'subscription_id' => $subscription['id'],
            'customer' => $subscription['customer'],
            'status' => $subscription['status'],
        ]);

        $this->subscriptionService->syncSubscription($tenant, $subscription);
    }

    /**
     * Handle subscription updated event.
     *
     * @param array $subscription
     * @param Tenant|null $tenant
     * @return void
     */
    public function handleSubscriptionUpdated(array $subscription, ?Tenant $tenant): void
    {
        if (!$tenant) {
            return;
        }

        Log::info('Stripe Subscription Updated', [
            'subscription_id' => $subscription['id'],
            'customer' => $subscription['customer'],
            'status' => $subscription['status'],
        ]);

        $this->subscriptionService->syncSubscription($tenant, $subscription);
    }

    /**
     * Handle subscription deleted event.
     *
     * @param array $subscription
     * @param Tenant|null $tenant
     * @return void
     */
    public function handleSubscriptionDeleted(array $subscription, ?Tenant $tenant): void
    {
        Log::info('Stripe Subscription Deleted', [
            'subscription_id' => $subscription['id'],
            'customer' => $subscription['customer'],
        ]);

        // Cashier's parent webhook controller will handle the deletion
        // No additional logic needed here
    }

    /**
     * Handle invoice payment succeeded event.
     *
     * @param array $invoice
     * @param Tenant|null $tenant
     * @return void
     */
    public function handleInvoicePaymentSucceeded(array $invoice, ?Tenant $tenant): void
    {
        Log::info('Stripe Invoice Payment Succeeded', [
            'invoice_id' => $invoice['id'],
            'customer' => $invoice['customer'],
            'amount_paid' => $invoice['amount_paid'],
        ]);

        if (!$tenant) {
            return;
        }

        $stripeSubscriptionId = $invoice['subscription'] ?? null;
        if (!$stripeSubscriptionId) {
            return;
        }

        $subscription = $tenant->subscriptions()
            ->where('stripe_id', $stripeSubscriptionId)
            ->first();

        if (!$subscription) {
            return;
        }

        // Create invoice record
        SubscriptionInvoice::create([
            'subscription_id' => $subscription->id,
            'amount' => $invoice['amount_paid'] / 100, // Convert from cents
            'currency' => strtoupper($invoice['currency']),
            'payment_status' => 'paid',
            'transaction_id' => $invoice['id'],
            'invoice_date' => Carbon::createFromTimestamp($invoice['created']),
            'metadata' => [
                'invoice_pdf' => $invoice['invoice_pdf'] ?? null,
                'hosted_invoice_url' => $invoice['hosted_invoice_url'] ?? null,
                'payment_intent' => $invoice['payment_intent'] ?? null,
            ],
        ]);
    }

    /**
     * Handle invoice payment failed event.
     *
     * @param array $invoice
     * @param Tenant|null $tenant
     * @return void
     */
    public function handleInvoicePaymentFailed(array $invoice, ?Tenant $tenant): void
    {
        Log::warning('Stripe Invoice Payment Failed', [
            'invoice_id' => $invoice['id'],
            'customer' => $invoice['customer'],
            'amount_due' => $invoice['amount_due'],
        ]);

        if (!$tenant) {
            return;
        }

        $stripeSubscriptionId = $invoice['subscription'] ?? null;
        if (!$stripeSubscriptionId) {
            return;
        }

        $subscription = $tenant->subscriptions()
            ->where('stripe_id', $stripeSubscriptionId)
            ->first();

        if (!$subscription) {
            return;
        }

        // Create invoice record with failed status
        SubscriptionInvoice::create([
            'subscription_id' => $subscription->id,
            'amount' => $invoice['amount_due'] / 100,
            'currency' => strtoupper($invoice['currency']),
            'payment_status' => 'failed',
            'transaction_id' => $invoice['id'],
            'invoice_date' => Carbon::createFromTimestamp($invoice['created']),
            'metadata' => [
                'attempt_count' => $invoice['attempt_count'] ?? 0,
                'next_payment_attempt' => $invoice['next_payment_attempt'] ?? null,
            ],
        ]);
    }

    /**
     * Get tenant by Stripe customer ID.
     *
     * @param string $customerId
     * @return Tenant|null
     */
    public function getTenantByCustomerId(string $customerId): ?Tenant
    {
        // Try stripe_id column first
        $tenant = Tenant::where('stripe_id', $customerId)->first();

        // If not found, try data column
        if (!$tenant) {
            $tenant = Tenant::where('data->stripe_customer_id', $customerId)->first();
        }

        if (!$tenant) {
            Log::warning('Tenant not found for customer', ['customer_id' => $customerId]);
        }

        return $tenant;
    }
}
