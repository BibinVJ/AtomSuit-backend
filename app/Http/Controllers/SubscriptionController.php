<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Plan;
use App\Models\Tenant;
use Laravel\Cashier\Cashier;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{

    /**
     * Create a Stripe Checkout session for a tenant and plan.
     */
    public function createCheckout(Request $request)
    {
        $data = $request->validate([
            'tenant_id' => 'required|string|exists:tenants,id',
            'plan_id' => 'required|integer|exists:plans,id',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ]);

        $tenant = Tenant::findOrFail($data['tenant_id']);
        $plan = Plan::findOrFail($data['plan_id']);

        // Use Cashier's Stripe client to create Checkout Session; Webhooks will sync subscription
        $client = Cashier::stripe();
        if (empty($plan->stripe_price_id)) {
            return ApiResponse::error('Plan is not linked to Stripe price.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Ensure Stripe customer exists
        if (! $tenant->stripe_id) {
            $stripeCustomer = $client->customers->create([
                'email' => $tenant->email,
                'name' => $tenant->name,
                'metadata' => ['tenant_id' => $tenant->id],
            ]);
            $tenant->stripe_id = $stripeCustomer->id;
            $tenant->save();
        }

        $session = $client->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $tenant->stripe_id,
            'line_items' => [[
                'price' => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url' => ($data['success_url'] ?? config('services.stripe.success_url')) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $data['cancel_url'] ?? config('services.stripe.cancel_url'),
            'client_reference_id' => $tenant->id,
        ]);

        return ApiResponse::success('Checkout session created.', [
            'checkout_url' => $session->url,
            'session_id' => $session->id,
        ], Response::HTTP_CREATED);
    }

}
