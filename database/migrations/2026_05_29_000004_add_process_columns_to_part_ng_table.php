<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            if (!Schema::hasColumn('part_ng', 'penanggungjawab')) {
                $table->string('penanggungjawab', 255)->nullable()->after('Total_Part_Ng');
            }
            if (!Schema::hasColumn('part_ng', 'penyebab')) {
                $table->string('penyebab', 255)->nullable()->after('penanggungjawab');
            }
            if (!Schema::hasColumn('part_ng', 'penanganan')) {
                $table->string('penanganan', 255)->nullable()->after('penyebab');
            }
        });
    }

    public function down(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            if (Schema::hasColumn('part_ng', 'penanggungjawab')) {
                $table->dropColumn(['penanggungjawab', 'penyebab', 'penanganan']);
            }
        });
    }
};
