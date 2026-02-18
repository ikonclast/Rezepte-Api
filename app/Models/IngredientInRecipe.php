<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientInRecipe extends Model
{
    protected $table = 'ingredient_in_recipes';
    public $incrementing = false;        // composite key
    public $timestamps = true;

    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'quantity',
        'unit',
    ];

    /**
     * Zugehöriges Rezept.
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    }

    /**
     * Zugehörige Zutat.
     */
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id', 'id');
    }
}
