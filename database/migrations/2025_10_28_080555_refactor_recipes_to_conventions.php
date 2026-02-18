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
        Schema::table('recipes', function (Blueprint $table) {
           
            $table->renameColumn('recepieId', 'id');
            $table->renameColumn('recipeName', 'title');
            $table->renameColumn('instructions', 'description');
            $table->unsignedInteger('portions')->default(1)->after('description');

 
            if (!Schema::hasColumn('recipes', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->after('id')
                      ->constrained('users')->nullOnDelete();
            }
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
           // Rückbau
            if (Schema::hasColumn('recipes', 'owner_id')) {
                $table->dropConstrainedForeignId('owner_id');
            }
            $table->dropColumn('portions');
            $table->renameColumn('description', 'instructions');
            $table->renameColumn('title', 'recipeName');
            $table->renameColumn('id', 'recepieId');
        });

       
    }
};
