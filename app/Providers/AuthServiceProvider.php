<?php

namespace App\Providers;



use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\User;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Policies\RecipePolicy;
use App\Policies\IngredientPolicy;
use App\Policies\MenuPolicy;
use App\Policies\UserPolicy;

use App\Services\RecipeScalingService;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        User::class => UserPolicy::class,   // <-- wichtig
        Recipe::class     => RecipePolicy::class,
        Ingredient::class => IngredientPolicy::class,
        Menu::class       => MenuPolicy::class,

    ];

    public function register(): void
    {
        $this->app->bind(RecipeScalingService::class, RecipeScalingService::class);
    }

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('admin', function (User $user) {
            return $user->role === 'admin';
        });
    }
}
