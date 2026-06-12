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
        Schema::create('racks', function (Blueprint $table) {
            $table->bigIncrements('Id_Rack'); // primary key
            $table->string('Code_Rack', 55)->unique();
            $table->string('Code_Item_Rack', 55);
            $table->string('Name_Item_Rack', 255)->nullable();
            $table->string('Type_Tractor_Rack', 100)->nullable();
            $table->timestamp('Update_Rack')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('racks');
    }
};
