<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Enums\PermissionsEnum;
use Illuminate\Http\Request;
use App\Http\Resources\AuditResource;
use App\Repositories\AuditRepository;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{

    public function __construct(
        protected AuditRepository $auditRepository
    ) {
        $this->middleware('permission:' . PermissionsEnum::VIEW_AUDIT->value)->only(['index', 'show']);
    }

    /**
     * Display a listing of the audit logs for the current tenant.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['log_name', 'event', 'causer_id', 'subject_type', 'search', 'from', 'to', 'sort_by', 'sort_direction']);
        $paginate = !($request->has('from') && $request->has('to'));
        $perPage = $request->integer('limit', 20);

        $activities = $this->auditRepository->all($paginate, $perPage, $filters, ['causer', 'subject']);

        $result = AuditResource::collectionWithMeta($activities, [
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ]);

        return ApiResponse::success(
            'Audit logs fetched successfully.',
            $result['data'],
            200,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }

    /**
     * Display the specified audit log detail.
     */
    public function show(int $activityId)
    {
        $activity = $this->auditRepository->find($activityId, ['causer', 'subject']);

        if (!$activity) {
            return ApiResponse::error('Audit log not found.', [], 404);
        }
        
        return ApiResponse::success('Audit log detail fetched successfully.', AuditResource::make($activity));
    }
}
