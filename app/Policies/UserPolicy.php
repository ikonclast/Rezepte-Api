<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
     public function view(User $actor, User $target): Response
    {
        return ($actor->id === $target->id || $actor->role === 'admin')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $actor)     
    {
        
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $actor, User $target): Response
    {
            return ($actor->id === $target->id || $actor->role === 'admin')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $actor, User $target): Response
    {
        // Löschen nur für Admin (kein Self-Delete hier):
         return ($actor->role === 'admin')
            ? Response::allow()
            : Response::deny('Forbidden'); // hier 403 ist gewollt
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
