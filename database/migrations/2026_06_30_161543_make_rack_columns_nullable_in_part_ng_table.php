<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            $table->integer('Id_Rack')->nullable()->change();
            $table->string('Code_Rack', 55)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('part_ng', function (Blueprint $table) {
            $table->integer('Id_Rack')->nullable(false)->change();
            $table->string('Code_Rack', 55)->nullable(false)->change();
        });
    }
};
