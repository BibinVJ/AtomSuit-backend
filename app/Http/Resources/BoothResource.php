<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BoothResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'unique_id'    => $this->unique_id,
            'name'         => $this->name,
            'description'  => $this->description,
            'image'        => $this->image,
            'size'         => $this->size,
            'price'        => $this->price,
            'status'       => $this->status->value,
            'is_active'    => $this->is_active,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }

    public static function paginated(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => self::collection($paginator),
            'current_page' => $paginator->currentPage(),
            'first_page_url' => $paginator->url(1),
            'from' => $paginator->firstItem(),
            'last_page' => $paginator->lastPage(),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'links' => self::formatPaginationLinks($paginator),
            'next_page_url' => $paginator->nextPageUrl(),
            'path' => $paginator->path(),
            'per_page' => $paginator->perPage(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
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
