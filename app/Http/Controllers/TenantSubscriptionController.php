<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\PlanResource;
use App\Http\Resources\SubscriptionResource;
use App\Services\StripeSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (!$subscription) {
            return ApiResponse::error('No active subscription found', Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::success(
            'Current subscription fetched successfully',
            SubscriptionResource::make($subscription->load('items.price'))
        );
    }

    public function changePlan(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenant = tenant();
        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            return ApiResponse::error('No active subscription to upgrade', Response::HTTP_BAD_REQUEST);
        }

        try {
            $plan = tenancy()->central(function () use ($request) {
                return \App\Models\Plan::findOrFail($request->plan_id);
            });

            $updatedSubscription = $this->subscriptionService->changePlan($subscription, $plan);

            return ApiResponse::success(
                'Subscription upgraded successfully',
                SubscriptionResource::make($updatedSubscription->load('items.price'))
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to upgrade subscription: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancel()
    {
        $tenant = tenant();
        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            return ApiResponse::error('No active subscription to cancel', Response::HTTP_BAD_REQUEST);
        }

        try {
            $subscription->cancelAt(now()->addDays(30));

            return ApiResponse::success(
                'Subscription will be cancelled at the end of current billing period',
                SubscriptionResource::make($subscription->load('items.price'))
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to cancel subscription: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}