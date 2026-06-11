<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_users', function (Blueprint $table) {
            $table->increments('Id_Type_User');
            $table->string('Name_Type_User', 55);
        });

        // Seed default type users
        DB::table('type_users')->insert([
            ['Id_Type_User' => 1, 'Name_Type_User' => 'Admin'],
            ['Id_Type_User' => 2, 'Name_Type_User' => 'Leader'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('type_users');
    }
};
