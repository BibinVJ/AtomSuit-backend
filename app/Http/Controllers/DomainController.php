<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Resources\DomainResource;
use App\Repositories\DomainRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DomainController extends Controller
{
    public function __construct(
        protected DomainRepository $domainRepository
    ) {
        $this->middleware('permission:'.PermissionsEnum::VIEW_DOMAIN->value)->only(['index']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_by', 'sort_direction']);
        $paginate = ! $request->boolean('unpaginated');
        $perPage = $request->integer('perPage', 15);

        $domains = $this->domainRepository->all($paginate, $perPage, $filters, ['tenant']);

        if ($paginate) {
            $paginated = DomainResource::paginated($domains);

            return ApiResponse::success(
                'Domains fetched successfully.',
                $paginated['data'],
                Response::HTTP_OK,
                $paginated['meta'],
                $paginated['links']
            );
        }

        return ApiResponse::success(
            'Domains fetched successfully.',
            DomainResource::collection($domains),
            Response::HTTP_OK,
            ['total' => count($domains)]
        );
    }
}
