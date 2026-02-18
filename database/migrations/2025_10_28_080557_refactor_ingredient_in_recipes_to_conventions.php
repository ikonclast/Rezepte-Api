<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $db = DB::getDatabaseName();

      
        $fkRows = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = 'ingredient_in_recipes'
        ", [$db]);

        foreach ($fkRows as $row) {
            DB::statement("ALTER TABLE `ingredient_in_recipes` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`");
        }

     
        $idxRows = DB::select("
            SHOW INDEX FROM `ingredient_in_recipes`
        ");
        $idxToDrop = collect($idxRows)->pluck('Key_name')->unique()->filter(function ($name) {
            return in_array($name, [
                'ingredient_in_recipes_recipe_id_foreign',
                'ingredient_in_recipes_ingredient_id_foreign',
            ]);
        });
        foreach ($idxToDrop as $idx) {
            DB::statement("ALTER TABLE `ingredient_in_recipes` DROP INDEX `{$idx}`");
        }

  
        try {
            DB::statement("ALTER TABLE `ingredient_in_recipes` DROP PRIMARY KEY");
        } catch (\Throwable $e) {}


        Schema::table('ingredient_in_recipes', function (Blueprint $table) {
            if (Schema::hasColumn('ingredient_in_recipes', 'recipeId')) {
                $table->renameColumn('recipeId', 'recipe_id');
            }
            if (Schema::hasColumn('ingredient_in_recipes', 'ingredientId')) {
                $table->renameColumn('ingredientId', 'ingredient_id');
            }
        });


        Schema::table('ingredient_in_recipes', function (Blueprint $table) {

            $table->unsignedBigInteger('recipe_id')->change();
            $table->unsignedBigInteger('ingredient_id')->change();
        });


        $hasPrimary = collect(DB::select("SHOW KEYS FROM ingredient_in_recipes WHERE Key_name = 'PRIMARY'"))->isNotEmpty();
        if (! $hasPrimary) {
            Schema::table('ingredient_in_recipes', function (Blueprint $table) {
                $table->primary(['recipe_id', 'ingredient_id']);
            });
        }


        Schema::table('ingredient_in_recipes', function (Blueprint $table) {
            $table->foreign('recipe_id', 'fk_iir_recipe_id')
                  ->references('id')->on('recipes')
                  ->cascadeOnDelete();

            $table->foreign('ingredient_id', 'fk_iir_ingredient_id')
                  ->references('id')->on('ingredients')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Down nur grob: FKs droppen, PK droppen, Spalten zurückbenennen (wenn nötig)
        $db = DB::getDatabaseName();

        // FKs droppen (egal wie sie heißen)
        $fkRows = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = 'ingredient_in_recipes'
        ", [$db]);
        foreach ($fkRows as $row) {
            DB::statement("ALTER TABLE `ingredient_in_recipes` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`");
        }

        try { DB::statement("ALTER TABLE `ingredient_in_recipes` DROP PRIMARY KEY"); } catch (\Throwable $e) {}

        Schema::table('ingredient_in_recipes', function (Blueprint $table) {
            if (Schema::hasColumn('ingredient_in_recipes', 'recipe_id')) {
                $table->renameColumn('recipe_id', 'recipeId');
            }
            if (Schema::hasColumn('ingredient_in_recipes', 'ingredient_id')) {
                $table->renameColumn('ingredient_id', 'ingredientId');
            }
        });
    }
};
