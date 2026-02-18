<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Recipe;

class RecipeScalingService
{
    /**
     * Skaliert ein Rezept auf die angegebene Personenanzahl.
     * Gibt eine strukturierte Antwort mit Zutaten & optionalen Kindern zurück.
     */
    public function buildDetailed(Recipe $recipe, float $targetGuests): array
    {
        $recipe->load(['ingredients', 'children.ingredients']);

        $basePortions = max(1, (int)$recipe->portions);
        $factor       = $targetGuests / $basePortions;

        // Zutaten skalieren
        $ingredients = $recipe->ingredients->map(function ($ingredient) use ($factor) {
            return [
                'id'       => $ingredient->id,
                'name'     => $ingredient->name,
                'quantity' => $this->roundSmart($ingredient->pivot->quantity * $factor, $ingredient->pivot->unit),
                'unit'     => $ingredient->pivot->unit,
            ];
        });

        // Kinderrezepte (wenn vorhanden)
        $children = $recipe->children->map(function ($child) use ($targetGuests) {
            $childGuests = $targetGuests * (float)$child->pivot->child_portion_factor;
            return [
                'id'          => $child->id,
                'title'       => $child->title,
                'targetGuests' => $childGuests,
            ];
        });

        return [
            'id'          => $recipe->id,
            'title'       => $recipe->title,
            'description' => $recipe->description,
            'basePortions' => $basePortions,
            'targetGuests' => $targetGuests,
            'factor'      => $factor,
            'ingredients' => $ingredients,
            'children'    => $children,
        ];
    }

    protected function roundSmart(float $value, string $unit): float
    {
        if (mb_strtolower($unit) === 'stück') {
            return round($value * 2) / 2; // halbe Schritte
        }
        return round($value, 2);
    }
}
