<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('Id_Type_User')->default(1)->after('Password_User');
            $table->foreign('Id_Type_User')->references('Id_Type_User')->on('type_users');
        });

        // Set existing users as Admin (type 1)
        DB::table('users')->update(['Id_Type_User' => 1]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['Id_Type_User']);
            $table->dropColumn('Id_Type_User');
        });
    }
};
