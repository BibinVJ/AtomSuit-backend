<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Exports\CustomerExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CustomerResource;
use App\Imports\CustomerImport;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerService $customerService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_CUSTOMER->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_CUSTOMER->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_CUSTOMER->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_CUSTOMER->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $customers = $this->customerRepository->all($paginate, $perPage, $filters);

        $result = CustomerResource::collectionWithMeta($customers, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Customers fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
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

    public function destroy(Request $request, int $id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        $this->customerService->delete($customer, $request->boolean('force'));

        return ApiResponse::success($request->boolean('force') ? 'Customer permanently deleted.' : 'Customer deleted successfully.');
    }

    public function restore(int $id)
    {
        $customer = $this->customerService->restore($id);

        return ApiResponse::success('Customer restored successfully.', CustomerResource::make($customer));
    }

    public function export()
    {
        return Excel::download(new CustomerExport, 'customers_'.now()->format('Y-m-d_H-i-s').'.xlsx');
    }

    public function import(ImportRequest $request)
    {
        Excel::import(new CustomerImport, $request->file('file'));

        return ApiResponse::success('Customers imported successfully.');
    }

    public function downloadSample()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings
        {
            public function collection()
            {
                return collect([
                    [
                        'John Doe',
                        'john@example.com',
                        '1234567890',
                        '123 Main St, Springfield',
                        'active',
                    ],
                ]);
            }

            public function headings(): array
            {
                return [
                    'Name',
                    'Email',
                    'Phone',
                    'Address',
                    'Status',
                ];
            }
        }, 'sample_customers.xlsx');
    }
}
