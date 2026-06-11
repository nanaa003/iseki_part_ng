<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            // Snapshot harga USD saat data diinput.
            // Null = kode part tidak ada di pricelist saat itu.
            $table->decimal('harga_snapshot', 15, 6)->nullable()->after('Total_Part_Ng');
        });
    }

    public function down(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            $table->dropColumn('harga_snapshot');
        });
    }
};
