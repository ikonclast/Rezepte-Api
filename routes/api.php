<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\V1\Auth\RegisterController;
use App\Http\Controllers\API\V1\Auth\AuthController;
use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\API\V1\IngredientController;
use App\Http\Controllers\API\V1\RecipeComponentController;
use App\Http\Controllers\API\V1\MenuController;
use App\Http\Controllers\API\V1\SearchController;
use App\Http\Controllers\API\V1\MeController;


/**
 * Öffentliche Auth-Routen
 */
Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class)->name('auth.register');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('auth.login');
});

/**
 * Geschützte Routen (Sanctum)
 */

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::get('/me/summary', [MeController::class, 'summary']);

    // User (Admin ODER Selbst via Policy; DELETE explizit admin)

    Route::get('/users', [UserController::class, 'index']);

    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('admin')
        ->name('users.destroy');
});


// Zutaten
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('ingredients', IngredientController::class)
        ->parameters(['ingredients' => 'ingredient']); // für Route Model Binding auf Ingredient
});


// Rezepte 

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('recipes', \App\Http\Controllers\API\V1\RecipeController::class);
    Route::apiResource('ingredients', \App\Http\Controllers\API\V1\IngredientController::class);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('recipes/{recipe}/components', [RecipeComponentController::class, 'store']);    // add/replace list
    Route::delete('recipes/{recipe}/components/{child}', [RecipeComponentController::class, 'destroy']);
});

// menus
Route::middleware('auth:sanctum')->group(function () {
    // Alle Menü-Endpoints
    Route::apiResource('menus', MenuController::class)
        ->parameters(['menus' => 'menu']); // Für korrektes Route-Model-Binding
});





//SPielereinen


Route::get('/', function () {
    return response()->json(['message' => 'Hello Frau Cramer'], 200);
});


Route::get('/ping', fn() => response()->json(['pong' => true]));

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::get('/health', HealthController::class);

Route::get('/search', [SearchController::class, 'index']);