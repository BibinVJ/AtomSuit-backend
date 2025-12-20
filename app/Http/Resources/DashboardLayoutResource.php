<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\DashboardLayout
 */
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
            'dashboard_card_id' => $this->dashboard_card_id,
            'slug' => $this->card?->slug,
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
            
            // Include card metadata for frontend rendering
            'component' => $this->card?->component,
            'title' => $this->card?->title,
            'default_width' => $this->card?->default_width,
            'default_height' => $this->card?->default_height,
            'default_x' => $this->card?->default_x,
            'default_y' => $this->card?->default_y,
        ];
    }
}
