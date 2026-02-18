<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'guest_count', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'menu_recipe')
            ->withPivot('position')
            ->orderBy('menu_recipe.position');
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string)$term);
        if ($term === '') return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
                // nach Rezept-Titeln im Menü suchen
                ->orWhereHas('recipes', function ($qr) use ($term) {
                    $qr->where('title', 'LIKE', "%{$term}%")
                        ->orWhere('description', 'LIKE', "%{$term}%");
                })
                // nach Zutatennamen innerhalb der Menü-Rezepte suchen
                ->orWhereHas('recipes.ingredients', function ($qi) use ($term) {
                    $qi->where('name', 'LIKE', "%{$term}%");
                });
        });
    }



    public function scopeUpdatedSince($query, ?string $iso8601)
    {
        if (!$iso8601) return $query;
        return $query->where('updated_at', '>=', $iso8601);
    }
}
