<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecipePolicy
{

    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        // Jeder eingeloggte User darf "seine" Liste sehen (Filter erfolgt im Controller).
        return true;
    }

    public function view(User $user, Recipe $recipe): Response|bool
    {
        return $recipe->owner_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound(); // schützt vor Enumeration
    }

    public function create(User $user): bool
    {
        return true; // jeder eingeloggte User darf Rezepte anlegen
    }

    public function update(User $user, Recipe $recipe): Response|bool
    {
        return $recipe->owner_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function delete(User $user, Recipe $recipe): Response|bool
    {
        return $recipe->owner_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function restore(User $user, Recipe $recipe): bool
    {
        return false;
    }

    public function forceDelete(User $user, Recipe $recipe): bool
    {
        return false;
    }
}
