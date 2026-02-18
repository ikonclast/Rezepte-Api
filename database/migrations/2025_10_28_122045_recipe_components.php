<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recipe_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('child_recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->decimal('child_portion_factor', 10, 4)->default(1.0);
            $table->unsignedInteger('position')->default(1);
            $table->string('role')->nullable();
            $table->timestamps();

            $table->unique(['parent_recipe_id', 'child_recipe_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('recipe_components');
    }
};
