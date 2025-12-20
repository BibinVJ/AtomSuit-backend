<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ChangePlanRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Services\StripeSubscriptionService;
use Symfony\Component\HttpFoundation\Response;

class TenantSubscriptionController extends Controller
{
    public function __construct(
        protected StripeSubscriptionService $subscriptionService
    ) {}

    public function current()
    {
        $tenant = tenant();
        $subscription = $tenant->subscription('default');

        if (! $subscription) {
            return ApiResponse::error('No active subscription found', Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::success(
            'Current subscription fetched successfully',
            SubscriptionResource::make($subscription->load(['items.price', 'tenant', 'plan']))
        );
    }

    public function changePlan(ChangePlanRequest $request)
    {
        $tenant = tenant();
        $subscription = $tenant->subscription('default');

        if (! $subscription) {
            return ApiResponse::error('No active subscription to upgrade', Response::HTTP_BAD_REQUEST);
        }

        try {
            $plan = tenancy()->central(function () use ($request) {
                return Plan::findOrFail($request->validated('plan_id'));
            });

            $updatedSubscription = $this->subscriptionService->changePlan($subscription, $plan);

            return ApiResponse::success(
                'Subscription upgraded successfully',
                SubscriptionResource::make($updatedSubscription->load(['items.price', 'tenant', 'plan']))
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to upgrade subscription: '.$e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancel()
    {
        $tenant = tenant();
        $subscription = $tenant->subscription('default');

        if (! $subscription) {
            return ApiResponse::error('No active subscription to cancel', Response::HTTP_BAD_REQUEST);
        }

        try {
            $subscription->cancelAt(now()->addDays(30));

            return ApiResponse::success(
                'Subscription will be cancelled at the end of current billing period',
                SubscriptionResource::make($subscription->load(['items.price', 'tenant', 'plan']))
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to cancel subscription: '.$e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
