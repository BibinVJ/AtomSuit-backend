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
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('per_page', 15);

        $batches = $this->batchRepository->all($paginate, $perPage, $filters);

        $result = BatchResource::collectionWithMeta($batches, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Batches fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }
}
