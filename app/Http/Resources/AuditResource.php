<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AuditResource extends BaseResource
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
            'log_name' => $this->log_name,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'causer_type' => $this->causer_type,
            'causer_id' => $this->causer_id,
            'properties' => $this->properties,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'event' => $this->event,
            'causer' => $this->causer ? [
                'name' => $this->causer->name,
            ] : null,
            'subject' => $this->subject ? [
                'name' => $this->subject->name ?? null,
                'id' => $this->subject->id ?? null,
            ] : null,
        ];
    }
}
