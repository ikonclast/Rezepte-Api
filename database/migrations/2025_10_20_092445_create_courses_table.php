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
        Schema::create('courses', function (Blueprint $table) {

            $table->unsignedBigInteger('courseId');
            $table->unsignedBigInteger('menuId');
            $table->string('courseName');
            $table->integer('courseOrder');
            $table->timestamps();

           
            $table->foreign('menuId')->references('menuId')->on('menus')->onDelete('cascade');
            $table->primary(['courseId','menuId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
