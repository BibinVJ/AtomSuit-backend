<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Resources\BatchResource;
use App\Repositories\BatchRepository;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function __construct(
        protected BatchRepository $batchRepository
    ) {
        $this->middleware("permission:" . PermissionsEnum::VIEW_BATCH->value)->only(['index']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_by', 'sort_direction']);
        $paginate = !$request->boolean('unpaginated');
        $perPage = $request->integer('per_page', 15);

        $batches = $this->batchRepository->all($paginate, $perPage, $filters);

        return ApiResponse::success(
            'Batches fetched successfully',
            $paginate ? BatchResource::paginated($batches) : BatchResource::collection($batches)
        );
    }
}
