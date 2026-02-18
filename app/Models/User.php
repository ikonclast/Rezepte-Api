<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean'
    ];

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }


    /* ==========================================================
     | Beziehungen
     ========================================================== */




    /**
     * Rezepte des Benutzers.
     */
    public function recipes()
    {
        return $this->hasMany(Recipe::class, 'owner_id', 'id');
    }

    /**
     * Zutaten, die der Benutzer erstellt hat.
     */
    public function ingredientsCreated()
    {
        return $this->hasMany(Ingredient::class, 'created_by', 'id');
    }
}
