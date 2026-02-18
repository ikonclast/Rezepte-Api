<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('ingredients', function (Blueprint $table) {
           
            $table->unsignedBigInteger('created_by')->nullable()->after('unit');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->unique('ingredientName', 'uq_ingredients_name');
            $table->index('unit', 'idx_ingredients_unit');
        });
    }

    public function down(): void {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->dropUnique('uq_ingredients_name');
            $table->dropIndex('idx_ingredients_unit');
        });
    }
};
