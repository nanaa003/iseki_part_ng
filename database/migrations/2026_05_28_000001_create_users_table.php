<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('Id_User');
            $table->string('Name_User', 100);
            $table->string('Username_User', 100)->unique();
            $table->string('Password_User');
            $table->unsignedInteger('Id_Type_User')->default(1);
            $table->unsignedBigInteger('Id_Area')->nullable();
            // Foreign keys will be added in a separate migration after related tables exist.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
