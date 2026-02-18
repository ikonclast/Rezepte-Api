<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',   
        'portions',
        'owner_id',
    ];

    /**
     * Der Benutzer, dem das Rezept gehört.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * Zutaten (viele-zu-viele über Pivot ingredient_in_recipes).
     */
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_in_recipes')
            ->withPivot(['quantity', 'unit'])
            ->withTimestamps();
    }

    public function children()
    {
        return $this->belongsToMany(
            Recipe::class,
            'recipe_components',
            'parent_recipe_id',
            'child_recipe_id'
        )
            ->withPivot(['child_portion_factor', 'position', 'role'])
            ->orderBy('recipe_components.position');
    }

    public function parents()
    {
        return $this->belongsToMany(
            Recipe::class,
            'recipe_components',
            'child_recipe_id',
            'parent_recipe_id'
        )
            ->withPivot(['child_portion_factor', 'position', 'role']);
    }

    /**
     * Direkter Zugriff auf Pivot-Zeilen
     */
    public function ingredientInRecipes()
    {
        return $this->hasMany(IngredientInRecipe::class, 'recipe_id', 'id');
    }

    /**
     * Arbeitsschritte des Rezepts.
     */
    public function steps()
    {
        return $this->hasMany(RecipeStep::class, 'recipe_id', 'id')->orderBy('step');
    }



    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string)$term);
        if ($term === '') return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%")
                ->orWhereHas('ingredients', function ($qi) use ($term) {
                    $qi->where('name', 'LIKE', "%{$term}%");
                });
        });
    }
}
