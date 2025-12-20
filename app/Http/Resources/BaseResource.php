<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    public static function paginated(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => static::collection($paginator), // The actual records
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];
    }

    /**
     * Helper to return collection with metadata, supporting both standard pagination
     * and custom range-based results.
     */
    public static function collectionWithMeta(mixed $resource, array $customMeta = []): array
    {
        if ($resource instanceof LengthAwarePaginator) {
            return self::paginated($resource);
        }

        if (is_array($resource) && isset($resource['data'])) {
            return [
                'data' => static::collection($resource['data']),
                'meta' => array_merge([
                    'total' => $resource['total'] ?? count($resource['data']),
                ], $customMeta)
            ];
        }

        return [
            'data' => static::collection($resource),
            'meta' => array_merge([
                'total' => count($resource),
            ], $customMeta)
        ];
    }

    protected static function formatPaginationLinks(LengthAwarePaginator $paginator): array
    {
        $links = [];

        $links[] = [
            'url' => $paginator->previousPageUrl(),
            'label' => '&laquo; Previous',
            'active' => false,
        ];

        for ($page = 1; $page <= $paginator->lastPage(); $page++) {
            $links[] = [
                'url' => $paginator->url($page),
                'label' => (string) $page,
                'active' => $paginator->currentPage() == $page,
            ];
        }

        $links[] = [
            'url' => $paginator->nextPageUrl(),
            'label' => 'Next &raquo;',
            'active' => false,
        ];

        return $links;
    }
}
