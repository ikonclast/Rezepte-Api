<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Http\Resources\RecipeListResource;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RecipeScalingService;

class RecipeController extends Controller
{
    /**
     * Liste der Rezepte (Admin = alle, User = eigene).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Recipe::query()->with('owner:id,username');

        if (! $user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $recipes = $query->latest()->paginate(10);

        return RecipeListResource::collection($recipes);
    }

    /**
     * Neues Rezept anlegen.
     */
    public function store(StoreRecipeRequest $request)
    {
        $this->authorize('create', Recipe::class);
        $user = $request->user();
        $data = $request->validated();

        $recipe = DB::transaction(function () use ($data, $user) {
            // Hauptrezept anlegen
            $recipe = Recipe::create([
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'portions'    => $data['portions'],
                'owner_id'    => $user->id,
            ]);

            // Zutaten synchronisieren
            $pivot = [];
            foreach ($data['ingredients'] as $item) {
                $pivot[$item['ingredient_id']] = [
                    'quantity' => $item['quantity'],
                    'unit'     => $item['unit'],
                ];
            }
            $recipe->ingredients()->sync($pivot);

            // Schritte anlegen
            $steps = collect($data['steps'])
                ->sortBy('step')
                ->map(fn($s) => [
                    'recipe_id'   => $recipe->id,
                    'step'        => $s['step'],
                    'instruction' => $s['instruction'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])->values()->all();

            RecipeStep::insert($steps);

            return $recipe->load(['owner', 'ingredients', 'steps']);
        });

        return (new RecipeResource($recipe))
            ->response()
            ->setStatusCode(201)
            ->header('Location', url("/api/v1/recipes/{$recipe->id}"));
    }

    /**
     * Einzelnes Rezept anzeigen.
     */
    public function show(Request $request, Recipe $recipe, RecipeScalingService $scaler)
    {

        $this->authorize('view', $recipe);
        // Prüfen, ob ein Skalierungsparameter übergeben wurde
        if ($request->has('pax')) {
            $targetGuests = (float) $request->query('pax');

            $scaled = $scaler->buildDetailed($recipe, $targetGuests);
            return response()->json($scaled);
        }



        return new RecipeResource(
            $recipe->load(['owner', 'ingredients', 'steps'])
        );
    }

    /**
     * Bestehendes Rezept aktualisieren.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);
        $data = $request->validated();

        $recipe = DB::transaction(function () use ($recipe, $data) {
            $recipe->update([
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'portions'    => $data['portions'],
            ]);

            $pivot = [];
            foreach ($data['ingredients'] as $item) {
                $pivot[$item['ingredient_id']] = [
                    'quantity' => $item['quantity'],
                    'unit'     => $item['unit'],
                ];
            }
            $recipe->ingredients()->sync($pivot);

            $recipe->steps()->delete();
            $steps = collect($data['steps'])
                ->sortBy('step')
                ->map(fn($s) => [
                    'recipe_id'   => $recipe->id,
                    'step'        => $s['step'],
                    'instruction' => $s['instruction'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])->values()->all();

            RecipeStep::insert($steps);

            return $recipe->load(['owner', 'ingredients', 'steps']);
        });

        return new RecipeResource($recipe);
    }

    /**
     * Rezept löschen.
     */
    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        $recipe->delete();

        return response()->noContent();
    }
}
