<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_areas', function (Blueprint $table) {
            $table->integer('Id_User');
            $table->unsignedInteger('Id_Area');
            $table->primary(['Id_User', 'Id_Area']);
            $table->foreign('Id_User')->references('Id_User')->on('users')->onDelete('cascade');
            $table->foreign('Id_Area')->references('Id_Area')->on('areas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_areas');
    }
};
