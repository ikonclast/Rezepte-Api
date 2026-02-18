<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        if (Schema::hasColumn('ingredients', 'unit') && !Schema::hasColumn('ingredients', 'unit_type')) {
            Schema::table('ingredients', function (Blueprint $table) {
                $table->renameColumn('unit', 'unit_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ingredients', 'unit_type') && !Schema::hasColumn('ingredients', 'unit')) {
            Schema::table('ingredients', function (Blueprint $table) {
                $table->renameColumn('unit_type', 'unit');
            });
        }
    }
};
