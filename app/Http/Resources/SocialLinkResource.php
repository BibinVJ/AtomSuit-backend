<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class SocialLinkResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'platform' => $this->platform,
            'url' => $this->url,
        ];
    }
}
