<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\VendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Repositories\VendorRepository;
use App\Services\VendorService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorController extends Controller
{
    public function __construct(
        protected VendorRepository $vendorRepository,
        protected VendorService $vendorService
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_VENDOR->value)->only(['index']);
        $this->middleware('permission:'.PermissionsEnum::CREATE_VENDOR->value)->only(['store']);
        $this->middleware('permission:'.PermissionsEnum::UPDATE_VENDOR->value)->only(['update']);
        $this->middleware('permission:'.PermissionsEnum::DELETE_VENDOR->value)->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['is_active', 'search', 'sort_by', 'sort_direction']);
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $vendors = $this->vendorRepository->all($paginate, $perPage, $filters);

        if ($paginate) {
            $paginated = VendorResource::paginated($vendors);

            return ApiResponse::success(
                'Vendors fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Vendors fetched successfully.',
            VendorResource::collection($vendors),
            Response::HTTP_OK,
            ['total' => count($vendors)]
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

    public function destroy(Vendor $vendor)
    {
        $this->vendorService->delete($vendor);

        return ApiResponse::success('Vendor deleted successfully.');
    }
}
