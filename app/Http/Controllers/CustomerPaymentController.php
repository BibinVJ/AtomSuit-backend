<?php

namespace App\Http\Controllers;

use App\Actions\Sales\CreateCustomerPayment;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\Sales\CustomerPaymentRequest;
use App\Http\Resources\CustomerPaymentResource;
use App\Models\CustomerPayment;
use App\Repositories\CustomerPaymentRepository;
use App\Services\CustomerPaymentService;
use App\Services\DocumentSequenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerPaymentController extends Controller
{
    public function __construct(
        protected CustomerPaymentRepository $paymentRepository,
        protected CustomerPaymentService $paymentService,
        protected DocumentSequenceService $sequenceService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_CUSTOMER_PAYMENT->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_CUSTOMER_PAYMENT->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_CUSTOMER_PAYMENT->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'from', 'to', 'sort_by', 'sort_direction',
            'status', 'customer_id', 'account_id', 'date_from', 'date_to',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $payments = $this->paymentRepository->all($paginate, $perPage, $filters, [
            'customer' => fn ($q) => $q->withTrashed(),
            'account',
        ]);

        $result = CustomerPaymentResource::collectionWithMeta($payments, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Customer Payments fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(CustomerPaymentRequest $request, CreateCustomerPayment $action): JsonResponse
    {
        $payment = $action->handle($request->validated(), $request->user());

        return ApiResponse::success('Customer Payment recorded successfully.', CustomerPaymentResource::make($payment->load(['customer', 'allocations.salesInvoice'])), Response::HTTP_CREATED);
    }

    public function show(CustomerPayment $customerPayment): JsonResponse
    {
        return ApiResponse::success(
            'Customer Payment fetched successfully.',
            CustomerPaymentResource::make($customerPayment->load(['customer', 'account', 'allocations.salesInvoice', 'costCenter']))
        );
    }

    public function destroy(Request $request, CustomerPayment $customerPayment): JsonResponse
    {
        try {
            $this->paymentService->delete($customerPayment);

            return ApiResponse::success('Customer Payment voided successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    public function nextPaymentNumber(): JsonResponse
    {
        $paymentNumber = $this->sequenceService->generateNext('customer_payment');

        return ApiResponse::success('Next Customer Payment number retrieved.', ['payment_number' => $paymentNumber]);
    }
}
