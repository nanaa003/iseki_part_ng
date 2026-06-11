<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('type_users')->insertOrIgnore([
            ['Name_Type_User' => 'Area'],
        ]);
    }
}
