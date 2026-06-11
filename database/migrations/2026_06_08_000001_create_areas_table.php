<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->increments('Id_Area');
            $table->string('Name_Area', 100);
            $table->string('Divisi', 100)->nullable();
            $table->string('Proses', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
