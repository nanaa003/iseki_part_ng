<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['Name_Area' => 'SUB ENGINE', 'Divisi' => 'Assembling', 'Proses' => 'SUB'],
            ['Name_Area' => 'SUB ASSY', 'Divisi' => 'Assembling', 'Proses' => 'SUB'],
            ['Name_Area' => 'TRANSMISI', 'Divisi' => 'Assembling', 'Proses' => 'LINE A'],
            ['Name_Area' => 'MAIN LINE', 'Divisi' => 'Assembling', 'Proses' => 'LINE B'],
            ['Name_Area' => 'PAINTING A', 'Divisi' => 'Painting', 'Proses' => 'PAINTING'],
            ['Name_Area' => 'PAINTING B', 'Divisi' => 'Painting', 'Proses' => 'PAINTING'],
            ['Name_Area' => 'MOWER', 'Divisi' => 'Mower', 'Proses' => 'MOWER'],
            ['Name_Area' => 'COLLECTOR', 'Divisi' => 'DST', 'Proses' => 'COLLECTOR'],
            ['Name_Area' => 'DST', 'Divisi' => 'DST', 'Proses' => 'DST'],
        ];

        DB::table('areas')->insert($areas);
    }
}
