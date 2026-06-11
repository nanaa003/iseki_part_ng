<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            if (!Schema::hasColumn('part_ng', 'proses')) {
                $table->string('proses', 50)->nullable()->after('Category_Part_Ng');
            }
        });
    }

    public function down(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            if (Schema::hasColumn('part_ng', 'proses')) {
                $table->dropColumn('proses');
            }
        });
    }
};
