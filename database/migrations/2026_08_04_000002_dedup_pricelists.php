<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus kode_part duplikat di pricelist, sisakan baris dengan id terkecil.
        // Lookup harga sekarang hanya memakai kode_part, jadi duplikat membuat
        // harga tidak konsisten antara form input dan laporan.
        DB::statement('
            DELETE p1 FROM pricelists p1
            INNER JOIN pricelists p2
                ON p1.kode_part = p2.kode_part
                AND p1.id > p2.id
        ');

        // Buang cache harga agar laporan langsung memakai data bersih
        Cache::forget('pricelist_map');
    }

    public function down(): void
    {
        // Tidak bisa mengembalikan baris yang sudah dihapus.
    }
};
