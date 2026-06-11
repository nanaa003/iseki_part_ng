<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            $table->string('Photo_Path_Part_Ng_2', 255)->nullable()->after('Photo_Path_Part_Ng');
            $table->string('Photo_Path_Part_Ng_3', 255)->nullable()->after('Photo_Path_Part_Ng_2');
        });
    }

    public function down(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            $table->dropColumn('Photo_Path_Part_Ng_3');
            $table->dropColumn('Photo_Path_Part_Ng_2');
        });
    }
};
