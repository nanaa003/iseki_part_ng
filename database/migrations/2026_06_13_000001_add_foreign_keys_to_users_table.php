<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ensure referenced columns exist and are indexed
            $table->foreign('Id_Type_User')
                ->references('Id_Type_User')
                ->on('type_users')
                ->onDelete('cascade');

            $table->foreign('Id_Area')
                ->references('Id_Area')
                ->on('areas')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['Id_Type_User']);
            $table->dropForeign(['Id_Area']);
        });
    }
};
