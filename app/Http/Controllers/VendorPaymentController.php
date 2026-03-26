<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\CreateVendorPayment;
use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\Purchase\VendorPaymentRequest;
use App\Http\Resources\VendorPaymentResource;
use App\Models\VendorPayment;
use App\Repositories\VendorPaymentRepository;
use App\Services\DocumentSequenceService;
use App\Services\VendorPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorPaymentController extends Controller
{
    public function __construct(
        protected VendorPaymentRepository $paymentRepository,
        protected VendorPaymentService $paymentService,
        protected DocumentSequenceService $sequenceService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_VENDOR_PAYMENT->value)->only(['index', 'show']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_VENDOR_PAYMENT->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_VENDOR_PAYMENT->value)->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'status', 'vendor_id', 'date_from', 'date_to',
            'from', 'to', 'sort_by', 'sort_direction',
        ]);

        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $payments = $this->paymentRepository->all($paginate, $perPage, $filters, [
            'vendor' => fn ($q) => $q->withTrashed(),
            'account',
        ]);

        $result = VendorPaymentResource::collectionWithMeta($payments, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Vendor Payments fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(VendorPaymentRequest $request, CreateVendorPayment $action): JsonResponse
    {
        $payment = $action->handle($request->validated(), $request->user());

        return ApiResponse::success(
            'Vendor Payment created successfully.',
            VendorPaymentResource::make($payment->load(['vendor', 'account', 'allocations.purchaseInvoice'])),
            Response::HTTP_CREATED
        );
    }

    public function show(VendorPayment $vendorPayment): JsonResponse
    {
        return ApiResponse::success(
            'Vendor Payment fetched successfully.',
            VendorPaymentResource::make($vendorPayment->load(['vendor', 'account', 'allocations.purchaseInvoice']))
        );
    }

    public function destroy(Request $request, VendorPayment $vendorPayment): JsonResponse
    {
        try {
            $this->paymentService->delete($vendorPayment);

            return ApiResponse::success('Vendor Payment voided successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    public function nextPaymentNumber(): JsonResponse
    {
        $paymentNumber = $this->sequenceService->generateNext('vendor_payment');

        return ApiResponse::success('Next Vendor Payment number retrieved.', ['payment_number' => $paymentNumber]);
    }
}
