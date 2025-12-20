<?php

namespace App\Http\Controllers;

use App\Services\Webhook\StripeWebhookService;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class StripeWebhookController extends CashierController
{
    public function __construct(
        protected StripeWebhookService $webhookService
    ) {
        parent::__construct();
    }

    public function handleCheckoutSessionCompleted(array $payload)
    {
        $this->webhookService->handleCheckoutSessionCompleted($payload['data']['object']);

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionCreated(array $payload)
    {
        $subscription = $payload['data']['object'];
        $tenant = $this->webhookService->getTenantByCustomerId($subscription['customer']);

        $this->webhookService->handleSubscriptionCreated($subscription, $tenant);

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionUpdated(array $payload)
    {
        $subscription = $payload['data']['object'];
        $tenant = $this->webhookService->getTenantByCustomerId($subscription['customer']);

        $this->webhookService->handleSubscriptionUpdated($subscription, $tenant);

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        $subscription = $payload['data']['object'];
        $tenant = $this->webhookService->getTenantByCustomerId($subscription['customer']);

        $this->webhookService->handleSubscriptionDeleted($subscription, $tenant);

        return parent::handleCustomerSubscriptionDeleted($payload);
    }

    public function handleInvoicePaymentSucceeded(array $payload)
    {
        $invoice = $payload['data']['object'];
        $tenant = $this->webhookService->getTenantByCustomerId($invoice['customer']);

        $this->webhookService->handleInvoicePaymentSucceeded($invoice, $tenant);

        return $this->successMethod();
    }

    public function handleInvoicePaymentFailed(array $payload)
    {
        $invoice = $payload['data']['object'];
        $tenant = $this->webhookService->getTenantByCustomerId($invoice['customer']);

        $this->webhookService->handleInvoicePaymentFailed($invoice, $tenant);

        return $this->successMethod();
    }

    /**
     * Override Cashier's getUserByStripeId to use Tenant model.
     */
    protected function getUserByStripeId($stripeId)
    {
        return $this->webhookService->getTenantByCustomerId($stripeId);
    }
}
