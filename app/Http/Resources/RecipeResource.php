<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'portions'    => $this->portions,
            'owner_id'    => $this->owner_id,
            'owner_name'  => $this->whenLoaded('owner', fn() => $this->owner->username ?? null),

            // Zutaten (mit Pivot-Daten)
            'ingredients' => $this->whenLoaded('ingredients', function () {
                return $this->ingredients->map(fn($ingredient) => [
                    'id'       => $ingredient->id,
                    'name'     => $ingredient->name,
                    'quantity' => (float) $ingredient->pivot->quantity,
                    'unit'     => $ingredient->pivot->unit,
                ]);
            }),

            // Arbeitsschritte
            'steps' => $this->whenLoaded('steps', function () {
                return $this->steps->sortBy('step')->map(fn($step) => [
                    'step'        => $step->step,
                    'instruction' => $step->instruction,
                ])->values();
            }),

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
