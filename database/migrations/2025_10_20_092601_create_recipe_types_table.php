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
        Schema::create('recipe_types', function (Blueprint $table) {
            $table->unsignedBigInteger('recipeId');
            $table->unsignedBigInteger('typeId');
            $table->string('recipeTypeName');
            $table->timestamps();

            // Define foreign key constraint
            $table->primary(['recipeId','typeId']);
            $table->foreign('typeId')->references('typeId')->on('types')->onDelete('cascade');
            $table->foreign('recipeId')->references('recepieId')->on('recipes')->onDelete('cascade');

            
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_types');
    }
};
