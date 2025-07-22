<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerService $customerService
    ) {
        $this->middleware("permission:" . PermissionsEnum::VIEW_CUSTOMER->value)->only(['index']);
        $this->middleware("permission:" . PermissionsEnum::CREATE_CUSTOMER->value)->only(['store']);
        $this->middleware("permission:" . PermissionsEnum::UPDATE_CUSTOMER->value)->only(['update']);
        $this->middleware("permission:" . PermissionsEnum::DELETE_CUSTOMER->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'sort_by', 'sort_direction']);
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $customers = $this->customerRepository->all($paginate, $perPage, $filters);

        return ApiResponse::success(
            'Customers fetched successfully.',
            $paginate ? CustomerResource::paginated($customers) : CustomerResource::collection($customers)
        );
    }


    public function store(CustomerRequest $request)
    {
        $customer = $this->customerRepository->create($request->validated());
        return ApiResponse::success('Customer created successfully.', CustomerResource::make($customer));
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $updatedCustomer = $this->customerRepository->update($customer, $request->validated());
        return ApiResponse::success('Customer updated successfully.', CustomerResource::make($updatedCustomer));
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->ensureCustomerIsDeletable($customer);
        $this->customerRepository->delete($customer);
        return ApiResponse::success('Customer deleted successfully.');
    }
}
