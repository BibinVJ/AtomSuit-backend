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
        $filters = $request->only(['search', 'from', 'to', 'sort_by', 'sort_direction']);
        $paginate = ! ($request->boolean('unpaginated') || ($request->has('from') && $request->has('to')));
        $perPage = $request->integer('perPage', 15);

        $domains = $this->domainRepository->all($paginate, $perPage, $filters, ['tenant']);

        $result = DomainResource::collectionWithMeta($domains, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Domains fetched successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }
}
