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
        Schema::create('dish_in_courses', function (Blueprint $table) {

            $table->unsignedBigInteger('courseId');
            $table->unsignedBigInteger('menuId'); 
            $table->unsignedBigInteger('dishId');
            $table->timestamps();

            $table->primary(['courseId','menuId','dishId']);

            $table->foreign(['courseId','menuId'])
                ->references(['courseId','menuId'])->on('courses')
                ->cascadeOnDelete();

            $table->foreign('dishId')->references('dishId')->on('dishes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dish_in_courses');
    }
};
