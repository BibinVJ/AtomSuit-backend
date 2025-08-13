<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Resources\BatchResource;
use App\Repositories\BatchRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BatchController extends Controller
{
    public function __construct(
        protected BatchRepository $batchRepository
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_BATCH->value)->only(['index']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_by', 'sort_direction']);
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('per_page', 15);

        $batches = $this->batchRepository->all($paginate, $perPage, $filters);

        if ($paginate) {
            $paginated = BatchResource::paginated($batches);

            return ApiResponse::success(
                'Batches fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Batches fetched successfully.',
            BatchResource::collection($batches),
            Response::HTTP_OK,
            ['total' => count($batches)]
        );
    }
}
