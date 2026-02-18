<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_type',
        'created_by',
    ];

    protected $casts = [
        'id'         => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * Benutzer, der die Zutat angelegt hat.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Pivot-Relation (eine Zutat kann in vielen Rezeptzeilen vorkommen).
     */
    public function ingredientInRecipes()
    {
        return $this->hasMany(IngredientInRecipe::class, 'ingredient_id', 'id');
    }

    /**
     * Rezepte, in denen diese Zutat verwendet wird.
     */
    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'ingredient_in_recipes')
            ->withPivot(['quantity', 'unit'])
            ->withTimestamps();
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string)$term);
        if ($term === '') return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%");
        });
    }
}
