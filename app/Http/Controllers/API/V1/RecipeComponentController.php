<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeComponentController extends Controller
{
    public function store(Request $request, Recipe $recipe)
    {
        
        $data = $request->validate([
            'components'               => ['required', 'array', 'min:1'],
            'components.*.child_id'    => ['required', 'integer', 'exists:recipes,id', 'different:recipe.id'],
            'components.*.factor'      => ['required', 'numeric', 'gt:0'],
            'components.*.position'    => ['nullable', 'integer', 'min:1'],
            'components.*.role'        => ['nullable', 'string', 'max:50'],
        ]);

        // Zyklus-Schutz (einfacher Check): child darf nicht ancestor des recipe sein
        $attach = [];
        foreach (array_values($data['components']) as $i => $c) {
            $attach[$c['child_id']] = [
                'child_portion_factor' => $c['factor'],
                'position'             => $c['position'] ?? ($i + 1),
                'role'                 => $c['role'] ?? null,
            ];
        }
        $recipe->children()->sync($attach);

        return response()->json([
            'message' => 'Components updated.',
            'children' => $recipe->children()->get(),
        ], 200);
    }

    public function destroy(Request $request, Recipe $recipe, Recipe $child)
    {
        $recipe->children()->detach($child->id);
        return response()->noContent();
    }
}
