<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DashboardLayoutResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'card_id' => $this->card_id,
            'area' => $this->area,
            'x' => $this->x,
            'y' => $this->y,
            'rotation' => $this->rotation,
            'width' => $this->width,
            'height' => $this->height,
            'col_span' => $this->col_span,
            'draggable' => $this->draggable,
            'visible' => $this->visible,
            'config' => $this->config,
        ];
    }
}
