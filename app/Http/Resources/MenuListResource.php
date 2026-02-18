<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'guest_count'  => $this->guest_count,
            'recipe_count' => (int) ($this->recipes_count ?? $this->recipes()->count()),
        ];
    }
}
