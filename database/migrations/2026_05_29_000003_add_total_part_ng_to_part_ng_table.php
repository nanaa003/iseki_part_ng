<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            if (!Schema::hasColumn('part_ng', 'Total_Part_Ng')) {
                $table->integer('Total_Part_Ng')->default(1)->after('Category_Part_Ng');
            }
        });
    }

    public function down(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            if (Schema::hasColumn('part_ng', 'Total_Part_Ng')) {
                $table->dropColumn('Total_Part_Ng');
            }
        });
    }
};
