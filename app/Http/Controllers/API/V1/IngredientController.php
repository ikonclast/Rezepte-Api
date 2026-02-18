<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use App\Models\IngredientInRecipe;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IngredientController extends Controller
{
    public function __construct()
    {
        // Admin darf bearbeiten/löschen, jeder darf lesen und anlegen
        $this->middleware('auth:sanctum');
        $this->middleware('admin')->only(['update', 'destroy']);
    }

    /**
     * GET /api/ingredients
     * Liste aller Zutaten (optional: Filter nach user_id für Admins)
     */
    public function index(Request $request)
    {
        $query = Ingredient::query()->orderBy('name');

        if ($request->filled('user_id') && $request->user()->isAdmin()) {
            $query->where('created_by', $request->integer('user_id'));
        }

        $ingredients = $query->paginate(50);

        return IngredientResource::collection($ingredients);
    }

    /**
     * POST /api/ingredients
     * Neue Zutat anlegen
     */
    public function store(StoreIngredientRequest $request)
    {
        $data = $request->validated();
        $ingredient = Ingredient::create([
            'name'       => $data['name'],
            'unit_type'  => $data['unit_type'],
            'created_by' => $request->user()->id,
        ]);

        return (new IngredientResource($ingredient))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * GET /api/ingredients/{id}
     */
    public function show(Ingredient $ingredient)
    {
        return new IngredientResource($ingredient);
    }

    /**
     * PUT /api/ingredients/{id}
     * Nur für Admins erlaubt
     */
    public function update(UpdateIngredientRequest $request, Ingredient $ingredient)
    {
        $data = $request->validated();
        $ingredient->update([
            'name'      => $data['name'],
            'unit_type' => $data['unit_type'],
        ]);

        return new IngredientResource($ingredient);
    }

    /**
     * DELETE /api/ingredients/{id}
     * Löscht nur, wenn die Zutat nicht mehr in Rezepten genutzt wird.
     */
    public function destroy(Ingredient $ingredient)
    {
        $inUse = IngredientInRecipe::where('ingredient_id', $ingredient->id)->exists();

        if ($inUse) {
            return response()->json([
                'error'   => 'conflict',
                'message' => 'Die Zutat wird noch in Rezepten verwendet.',
            ], Response::HTTP_CONFLICT);
        }
        $ingredient->delete();

        return response()->noContent();
    }
}
