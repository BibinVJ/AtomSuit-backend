<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Repositories\SubscriptionRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{

    public function __construct(
        protected SubscriptionRepository $subscriptionRepository
    ) {
        $this->middleware('permission:' . PermissionsEnum::VIEW_SUBSCRIPTION->value)->only(['index', 'show', 'destroy']);
    }

    /**
     * Display a listing of subscriptions.
     * Shows all subscriptions for central context, or tenant's own subscription for tenant context.
     */
    public function index(Request $request)
    {
        // Central context - show all subscriptions with pagination and filters
        $filters = $request->only(['status', 'plan_id', 'tenant_id', 'search', 'is_canceled', 'from', 'to', 'sort_by', 'sort_direction']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $subscriptions = $this->subscriptionRepository->all($paginate, $perPage, $filters, ['tenant', 'plan']);

        if ($paginate) {
            $paginated = SubscriptionResource::paginated($subscriptions);

            return ApiResponse::success(
                'Subscriptions fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Subscriptions fetched successfully.',
            SubscriptionResource::collection($subscriptions),
            Response::HTTP_OK,
            ['total' => count($subscriptions)]
        );
    }

    /**
     * Display the specified subscription.
     */
    public function show(Subscription $subscription)
    {
        // Load relationships
        $subscription->load(['tenant', 'plan', 'subscriptionInvoices', 'items']);
        
        return ApiResponse::success(
            'Subscription details fetched successfully.',
            new SubscriptionResource($subscription)
        );
    }

    /**
     * Cancel the subscription (soft delete - sets ends_at).
     */
    public function destroy(Subscription $subscription)
    {
        
        // If it's a Stripe subscription, cancel it via Stripe
        if ($subscription->stripe_id && !str_starts_with($subscription->stripe_id, 'manual_')) {
            try {
                $subscription->cancel();
            } catch (\Exception $e) {
                return ApiResponse::error(
                    'Failed to cancel subscription: ' . $e->getMessage(),
                    [],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        } else {
            // Manual subscription - just mark as cancelled
            $subscription->update([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ]);
        }
        
        return ApiResponse::success(
            'Subscription cancelled successfully.',
            new SubscriptionResource($subscription->fresh())
        );
    }
}
