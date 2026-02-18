<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\Menu;


class SearchController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q'      => 'required|string|max:100',
            'limit'  => 'nullable|integer|min:1|max:50', // pro Sektion
            'order'  => 'nullable|in:recent,alpha',
        ]);

        $q      = $data['q'];
        $limit  = $data['limit'] ?? 10;
        $order  = $data['order'] ?? 'recent';

        // Rezepte
        $recipes = Recipe::query()
            ->with('owner')
            ->search($q)
            ->when($order === 'alpha', fn($x) => $x->orderBy('title'))
            ->when($order === 'recent', fn($x) => $x->orderBy('updated_at', 'desc'))
            ->limit($limit)
            ->get(['id', 'title', 'updated_at']);

        // Zutaten
        $ingredients = Ingredient::query()
            ->search($q)
            ->when($order === 'alpha', fn($x) => $x->orderBy('name'))
            ->when($order === 'recent', fn($x) => $x->orderBy('updated_at', 'desc'))
            ->limit($limit)
            ->get(['id', 'name', 'updated_at']);

        // Menüs
        $menus = Menu::query()
            ->withCount('recipes')
            ->search($q)
            ->when($order === 'alpha', fn($x) => $x->orderBy('title'))
            ->when($order === 'recent', fn($x) => $x->orderBy('updated_at', 'desc'))
            ->limit($limit)
            ->get(['id', 'title', 'updated_at']);

        return response()->json([
            'query'       => $q,
            'limit'       => $limit,
            'order'       => $order,
            'results'     => [
                'recipes'     => $recipes,
                'ingredients' => $ingredients,
                'menus'       => $menus,
            ],
        ]);
    }
}
