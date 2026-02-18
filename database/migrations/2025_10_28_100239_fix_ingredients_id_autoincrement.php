<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
     
        $hasPrimary = collect(DB::select("
            SELECT k.CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS k
            WHERE k.TABLE_SCHEMA = DATABASE()
              AND k.TABLE_NAME = 'ingredients'
              AND k.CONSTRAINT_TYPE = 'PRIMARY KEY'
        "))->isNotEmpty();

        if (! $hasPrimary) {
          
            DB::statement("ALTER TABLE `ingredients` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL");

            DB::statement("ALTER TABLE `ingredients` ADD PRIMARY KEY (`id`)");
        }


        DB::statement("ALTER TABLE `ingredients` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `ingredients` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL");
    }
};
