<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus klausa ON UPDATE CURRENT_TIMESTAMP agar Date_Part_Ng
        // tidak berubah ketika baris di-update (mis. saat admin memberi komentar/penanganan).
        DB::statement('ALTER TABLE part_ng MODIFY Date_Part_Ng timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');

        if (!Schema::hasColumn('part_ng', 'proses_at')) {
            DB::statement('ALTER TABLE part_ng ADD COLUMN proses_at timestamp NULL DEFAULT NULL AFTER penanganan');
        }

        // Samakan kolasi part_ng dengan pricelists (utf8mb4_unicode_ci)
        // agar JOIN antar tabel tidak error "Illegal mix of collations".
        DB::statement('ALTER TABLE part_ng CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        if (Schema::hasColumn('part_ng', 'proses_at')) {
            Schema::table('part_ng', function (Blueprint $table) {
                $table->dropColumn('proses_at');
            });
        }

        DB::statement('ALTER TABLE part_ng MODIFY Date_Part_Ng timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE part_ng CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    }
};
