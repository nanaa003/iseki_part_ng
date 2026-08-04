<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['Name_Area' => 'SUB ENGINE', 'Divisi' => 'Assembling', 'Proses' => 'sub engine'],
            ['Name_Area' => 'SUB ASSY',   'Divisi' => 'Assembling', 'Proses' => 'subassy'],
            ['Name_Area' => 'TRANSMISI',  'Divisi' => 'Assembling', 'Proses' => 'transmisi'],
            ['Name_Area' => 'MAIN LINE',  'Divisi' => 'Assembling', 'Proses' => 'mainline'],
            ['Name_Area' => 'INSPEKSI',   'Divisi' => 'Assembling', 'Proses' => 'inspeksi'],
            ['Name_Area' => 'REPAIR',     'Divisi' => 'Assembling', 'Proses' => 'repair'],
            ['Name_Area' => 'PAINTING A', 'Divisi' => 'Painting',   'Proses' => 'painting a'],
            ['Name_Area' => 'PAINTING B', 'Divisi' => 'Painting',   'Proses' => 'painting b'],
            ['Name_Area' => 'MOWER',      'Divisi' => 'Mower',      'Proses' => 'mower'],
            ['Name_Area' => 'COLLECTOR',  'Divisi' => 'DST',        'Proses' => 'COLLECTOR'],
            ['Name_Area' => 'DST',        'Divisi' => 'DST',        'Proses' => 'DST'],
        ];

        DB::table('areas')->insert($areas);
    }
}
