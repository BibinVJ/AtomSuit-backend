<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\VendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Repositories\VendorRepository;
use App\Services\VendorService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Exports\VendorExport;
use App\Imports\VendorImport;
use Maatwebsite\Excel\Facades\Excel;

class VendorController extends Controller
{

    public function __construct(
        protected VendorRepository $vendorRepository,
        protected VendorService $vendorService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_VENDOR->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_VENDOR->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_VENDOR->value)->only(['update', 'restore']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_VENDOR->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction', 'trashed']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $vendors = $this->vendorRepository->all($paginate, $perPage, $filters);

        $result = VendorResource::collectionWithMeta($vendors, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Vendors fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    public function store(VendorRequest $request)
    {
        $vendor = $this->vendorRepository->create($request->validated());

        return ApiResponse::success('Vendor created successfully.', VendorResource::make($vendor));
    }

    public function update(VendorRequest $request, Vendor $vendor)
    {
        $updatedVendor = $this->vendorRepository->update($vendor, $request->validated());

        return ApiResponse::success('Vendor updated successfully.', VendorResource::make($updatedVendor));
    }

    public function destroy(Request $request, int $id)
    {
        $vendor = Vendor::withTrashed()->findOrFail($id);
        
        $this->vendorService->delete($vendor, $request->boolean('force'));
        return ApiResponse::success($request->boolean('force') ? 'Vendor permanently deleted.' : 'Vendor deleted successfully.');
    }

    public function restore(int $id)
    {
        $vendor = $this->vendorService->restore($id);

        return ApiResponse::success('Vendor restored successfully.', VendorResource::make($vendor));
    }

    public function export()
    {
        return Excel::download(new VendorExport, 'vendors_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    public function import(ImportRequest $request)
    {
        Excel::import(new VendorImport, $request->file('file'));

        return ApiResponse::success('Vendors imported successfully.');
    }

    public function downloadSample()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function collection()
            {
                return collect([
                    [
                        'Jane Doe',
                        'jane@example.com',
                        '9876543210',
                        '456 Elm St, Metropolis',
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
        }, 'sample_vendors.xlsx');
    }
}
