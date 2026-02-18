<?php

namespace App\Policies;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IngredientPolicy
{
    /**
     * Admin darf alles.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ingredient $ingredient): bool
    {
        // Zutaten sind global sichtbar 
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ingredient $ingredient): Response|bool
    {
        // Zusätzlich zur admin-Middleware auch in der Policy absichern (Defence in depth)
        return $ingredient->created_by === $user->id
            ? Response::allow()
            : Response::deny('Nur Ersteller oder Admin dürfen Zutaten bearbeiten.');
    }

    public function delete(User $user, Ingredient $ingredient): Response|bool
    {
        return $ingredient->created_by === $user->id
            ? Response::allow()
            : Response::deny('Nur Ersteller oder Admin dürfen Zutaten löschen.');
    }

    public function restore(User $user, Ingredient $ingredient): bool
    {
        return false;
    }

    public function forceDelete(User $user, Ingredient $ingredient): bool
    {
        return false;
    }
}
