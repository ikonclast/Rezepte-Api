<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingredient_in_recipes', function (Blueprint $table) {
            if (!Schema::hasColumn('ingredient_in_recipes', 'unit')) {
                $table->string('unit', 50)->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_in_recipes', function (Blueprint $table) {
            if (Schema::hasColumn('ingredient_in_recipes', 'unit')) {
                $table->dropColumn('unit');
            }
        });
    }
};
