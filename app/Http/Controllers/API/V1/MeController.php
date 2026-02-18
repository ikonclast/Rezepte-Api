<?php

namespace App\Http\Controllers\APi\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Models\Recipe;
use App\Models\Menu;
use App\Models\Ingredient;

class MeController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        $isAdmin = ($user->role ?? null) === 'admin';

        
        $ownedOrAll = function ($query, string $table) use ($isAdmin, $user) {
            if ($isAdmin) {
                return $query;
            }
            
            if (Schema::hasColumn($table, 'user_id')) {
                return $query->where('user_id', $user->id);
            }
            return $query;
        };

        
        $recipesCount = $ownedOrAll(Recipe::query(), (new Recipe)->getTable())->count();
        $menusCount   = $ownedOrAll(Menu::query(), (new Menu)->getTable())->count();

        
        $ingredientsBase = Ingredient::query();
        $ingredientsCount = $ownedOrAll($ingredientsBase, (new Ingredient)->getTable())->count();

        // --- Recent items (Top 5) ---
        $recentRecipes = $ownedOrAll(Recipe::query(), (new Recipe)->getTable())
            ->select('id', 'title', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $recentMenus = $ownedOrAll(Menu::query(), (new Menu)->getTable())
            ->select('id', 'title', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $recentIngredients = $ownedOrAll(Ingredient::query(), (new Ingredient)->getTable())
            ->select('id', 'name', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        
        $lastLoginAt = $user->last_login_at ?? null;

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name ?? null,
                'email' => $user->email ?? null,
                'role'  => $user->role ?? null,
            ],
            'counts' => [
                'recipes'     => $recipesCount,
                'menus'       => $menusCount,
                'ingredients' => $ingredientsCount,
            ],
            'recent' => [
                'recipes'     => $recentRecipes,
                'menus'       => $recentMenus,
                'ingredients' => $recentIngredients,
            ],
            'last_login_at' => $lastLoginAt, 
        ]);
    }
}
