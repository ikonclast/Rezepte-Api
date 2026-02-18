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
        Schema::create('recipe_in_dishes', function (Blueprint $table) {

            $table->unsignedBigInteger('recipeId');
            $table->unsignedBigInteger('dishId');
            $table->timestamps();

            $table->primary(['recipeId','dishId']);
            $table->foreign('recipeId')->references('recepieId')->on('recipes')->onDelete('cascade');
            $table->foreign('dishId')->references('dishId')->on('dishes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_in_dishes');
    }
};
