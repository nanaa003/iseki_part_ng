<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            if (!Schema::hasColumn('areas', 'Divisi')) {
                $table->string('Divisi', 100)->nullable()->after('Name_Area');
            }
            if (!Schema::hasColumn('areas', 'Proses')) {
                $table->string('Proses', 100)->nullable()->after('Divisi');
            }
        });

        DB::table('areas')->where('Name_Area', 'SUB ENGINE')->update(['Divisi' => 'Assembling', 'Proses' => 'SUB']);
        DB::table('areas')->where('Name_Area', 'SUB ASSY')->update(['Divisi' => 'Assembling', 'Proses' => 'SUB']);
        DB::table('areas')->where('Name_Area', 'TRANSMISI')->update(['Divisi' => 'Assembling', 'Proses' => 'LINE A']);
        DB::table('areas')->where('Name_Area', 'MAIN LINE')->update(['Divisi' => 'Assembling', 'Proses' => 'LINE B']);
        DB::table('areas')->where('Name_Area', 'PAINTING A')->update(['Divisi' => 'Painting', 'Proses' => 'PAINTING']);
        DB::table('areas')->where('Name_Area', 'PAINTING B')->update(['Divisi' => 'Painting', 'Proses' => 'PAINTING']);
        DB::table('areas')->where('Name_Area', 'MOWER')->update(['Divisi' => 'Mower', 'Proses' => 'MOWER']);
        DB::table('areas')->where('Name_Area', 'COLLECTOR')->update(['Divisi' => 'DST', 'Proses' => 'COLLECTOR']);
        DB::table('areas')->where('Name_Area', 'DST')->update(['Divisi' => 'DST', 'Proses' => 'DST']);
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            if (Schema::hasColumn('areas', 'Divisi')) {
                $table->dropColumn('Divisi');
            }
            if (Schema::hasColumn('areas', 'Proses')) {
                $table->dropColumn('Proses');
            }
        });
    }
};
