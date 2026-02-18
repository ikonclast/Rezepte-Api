<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {

            if (Schema::hasColumn('ingredients', 'ingredientId')) {
                $table->renameColumn('ingredientId', 'id');
            }
            if (Schema::hasColumn('ingredients', 'ingredientName')) {
                $table->renameColumn('ingredientName', 'name');
            }

            $table->unsignedBigInteger('id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {

            $table->unsignedInteger('id')->change();
            if (Schema::hasColumn('ingredients', 'name')) {
                $table->renameColumn('name', 'ingredientName');
            }
            if (Schema::hasColumn('ingredients', 'id')) {
                $table->renameColumn('id', 'ingredientId');
            }
        });
    }
};
