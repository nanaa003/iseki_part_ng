<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('Id_Area')->nullable()->after('Id_Type_User');
            $table->foreign('Id_Area')->references('Id_Area')->on('areas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['Id_Area']);
            $table->dropColumn('Id_Area');
        });
    }
};
