<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MenuPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Menu $menu): bool
    {
        return $user->id === $menu->user_id || $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return true; 
    }

    public function update(User $user, Menu $menu): bool
    {
        return $user->id === $menu->user_id || $user->role === 'admin';
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $user->id === $menu->user_id || $user->role === 'admin';
    }
    public function restore(User $user, Menu $menu): bool
    {
        return false;
    }

    public function forceDelete(User $user, Menu $menu): bool
    {
        return false;
    }
}
