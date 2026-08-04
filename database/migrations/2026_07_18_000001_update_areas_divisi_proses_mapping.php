<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('areas')->where('Name_Area', 'SUB ENGINE')->update(['Divisi' => 'Assembling', 'Proses' => 'sub engine']);
        DB::table('areas')->where('Name_Area', 'SUB ASSY')->update(['Divisi' => 'Assembling', 'Proses' => 'subassy']);
        DB::table('areas')->where('Name_Area', 'TRANSMISI')->update(['Divisi' => 'Assembling', 'Proses' => 'transmisi']);
        DB::table('areas')->where('Name_Area', 'MAIN LINE')->update(['Divisi' => 'Assembling', 'Proses' => 'mainline']);
        DB::table('areas')->where('Name_Area', 'PAINTING A')->update(['Divisi' => 'Painting', 'Proses' => 'painting a']);
        DB::table('areas')->where('Name_Area', 'PAINTING B')->update(['Divisi' => 'Painting', 'Proses' => 'painting b']);
        DB::table('areas')->where('Name_Area', 'MOWER')->update(['Divisi' => 'Mower', 'Proses' => 'mower']);

        if (!DB::table('areas')->where('Name_Area', 'INSPEKSI')->exists()) {
            DB::table('areas')->insert(['Name_Area' => 'INSPEKSI', 'Divisi' => 'Assembling', 'Proses' => 'inspeksi']);
        }
        if (!DB::table('areas')->where('Name_Area', 'REPAIR')->exists()) {
            DB::table('areas')->insert(['Name_Area' => 'REPAIR', 'Divisi' => 'Assembling', 'Proses' => 'repair']);
        }

        $prosesMapping = [
            'SUB ENGINE' => 'sub engine',
            'SUB ASSY'   => 'subassy',
            'TRANSMISI'  => 'transmisi',
            'MAIN LINE'  => 'mainline',
            'PAINTING A' => 'painting a',
            'PAINTING B' => 'painting b',
            'MOWER'      => 'mower',
        ];

        foreach ($prosesMapping as $oldProses => $newProses) {
            DB::table('part_ng')
                ->where('proses', $oldProses)
                ->update(['proses' => $newProses]);
        }

        $divisiMapping = [
            'SUB ENGINE' => 'Assembling',
            'SUB ASSY'   => 'Assembling',
            'TRANSMISI'  => 'Assembling',
            'MAIN LINE'  => 'Assembling',
            'PAINTING A' => 'Painting',
            'PAINTING B' => 'Painting',
            'MOWER'      => 'Mower',
        ];

        foreach ($divisiMapping as $oldDivisi => $newDivisi) {
            DB::table('part_ng')
                ->where('Divisi', $oldDivisi)
                ->update(['Divisi' => $newDivisi]);
        }

        $categoryMapping = [
            'bukan tanggung jawab MAIN LINE'  => 'bukan tanggung jawab Assembling',
            'bukan tanggung jawab SUB ENGINE' => 'bukan tanggung jawab Assembling',
            'bukan tanggung jawab PAINTING A' => 'bukan tanggung jawab Painting',
            'bukan tanggung jawab PAINTING B' => 'bukan tanggung jawab Painting',
            'bukan tanggung jawab MOWER'      => 'bukan tanggung jawab Mower',
        ];

        foreach ($categoryMapping as $oldCategory => $newCategory) {
            DB::table('part_ng')
                ->where('Category_Part_Ng', $oldCategory)
                ->update(['Category_Part_Ng' => $newCategory]);
        }
    }

    public function down(): void
    {
    }
};
