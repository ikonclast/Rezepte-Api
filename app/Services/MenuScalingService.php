<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Recipe;

class MenuScalingService
{
    public function buildDetailed(Menu $menu, ?float $targetGuests = null, array $recipeOverrides = []): array
    {
        // Fallback: gespeicherter guest_count (oder 4 als Minimaldefault)
        $targetGuests = $targetGuests ?? (float)($menu->guest_count ?? 4);

        // Eager Loading für Performance
        $menu->load([
            'recipes.ingredients',
            'recipes.children.ingredients',
            'recipes.children.children',
        ]);

        return [
            'id'          => $menu->id,
            'title'       => $menu->title,
            'guest_count' => $targetGuests,
            'recipes'     => $menu->recipes->map(function (Recipe $r) use ($targetGuests, $recipeOverrides) {

                $targetForThisRecipe = isset($recipeOverrides[$r->id])
                    ? (float)$recipeOverrides[$r->id]
                    : (float)$targetGuests;

                // Rekursiv expandieren (Komposition) -> flat + components
                $tree = $this->expandRecipeTree($r, $targetForThisRecipe, []);

                return [
                    'id'                => $r->id,
                    'title'             => $r->title,
                    'base_portions'     => (int)($r->portions ?? 4),
                    'target_guests'     => $targetForThisRecipe,
                    'ingredients_total' => $this->formatFlat($tree['flat']),
                    'components'        => $tree['components'],
                ];
            })->values(),
        ];
    }

    /**
     * Liefert:
     * - 'flat': array key "ingredientId|unit" => quantity  (flache Summe dieses (Teil-)Rezepts inkl. Kinder)
     * - 'components': Array mit Elementen für jedes Kind:
     *       [
     *         'id','title','target_guests','factor',
     *         'ingredients_total' => [...],
     *         'components' => [... weitere Kinder ...]
     *       ]
     */
    protected function expandRecipeTree(Recipe $recipe, float $targetGuests, array $visited): array
    {
        if (isset($visited[$recipe->id])) {
            throw new \RuntimeException("Recipe cycle detected at #{$recipe->id}");
        }
        $visited[$recipe->id] = true;

        $flat = [];

        $basePortions = max(1, (int)$recipe->portions);
        $factor       = $targetGuests / $basePortions;


        foreach ($recipe->ingredients as $ing) {
            $key = $ing->id . '|' . ($ing->pivot->unit ?? '');
            $qty = (float)$ing->pivot->quantity * $factor;
            $flat[$key] = ($flat[$key] ?? 0) + $qty;
        }


        $components = [];
        foreach ($recipe->children as $child) {

            $childGuests = $targetGuests * (float)$child->pivot->child_portion_factor;

            $childTree = $this->expandRecipeTree($child, $childGuests, $visited);

            foreach ($childTree['flat'] as $k => $v) {
                $flat[$k] = ($flat[$k] ?? 0) + $v;
            }

            $components[] = [
                'id'             => $child->id,
                'title'          => $child->title,
                'target_guests'  => $childGuests,
                'factor'         => $child->pivot->child_portion_factor,
                'ingredients_total' => $this->formatFlat($childTree['flat']),
                'components'        => $childTree['components'],
            ];
        }

        unset($visited[$recipe->id]);
        return ['flat' => $flat, 'components' => $components];
    }

    /** Flat map -> Array [{name, quantity_total, unit}] */
    protected function formatFlat(array $flat): array
    {
        if (empty($flat)) return [];

        // Gruppierung: ingredientId => [unit => qty]
        $grouped = [];
        foreach ($flat as $key => $qty) {
            [$id, $unit] = explode('|', $key, 2);
            $grouped[$id][$unit] = ($grouped[$id][$unit] ?? 0) + $qty;
        }

        $ids         = array_map('intval', array_keys($grouped));
        $ingredients = Ingredient::whereIn('id', $ids)->get()->keyBy('id');

        $result = [];
        foreach ($grouped as $id => $units) {
            $name = $ingredients[(int)$id]->name ?? "Ingredient #$id";
            foreach ($units as $unit => $qty) {
                $result[] = [
                    'name'           => $name,
                    'quantity_total' => $this->roundSmart($qty, $unit),
                    'unit'           => $unit,
                ];
            }
        }
        return $result;
    }

    protected function roundSmart(float $value, string $unit): float
    {
        if (mb_strtolower($unit) === 'stück') {
            return round($value * 2) / 2; // 0.5-Schritte
        }
        return round($value, 2);
    }
}
