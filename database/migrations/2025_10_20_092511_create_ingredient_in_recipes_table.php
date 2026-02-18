<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingredient_in_recipes', function (Blueprint $table) {
           
            $table->unsignedBigInteger('recipeId');
            $table->unsignedBigInteger('ingredientId');
            $table->double('quantity');
            $table->string('unit');
            $table->timestamps();

            $table->primary(['recipeId','ingredientId']);
           
            $table->foreign('recipeId')->references('recepieId')->on('recipes')->onDelete('cascade');
            $table->foreign('ingredientId')->references('ingredientId')->on('ingredients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_in_recipes');
    }
};
