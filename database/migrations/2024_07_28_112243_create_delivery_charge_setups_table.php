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
        Schema::create('delivery_charge_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->index();
            $table->string('delivery_charge_type')->comment('area/distance')->default('distance');
            $table->double('delivery_charge_per_kilometer')->default(0);
            $table->double('minimum_delivery_charge')->default(0);
            $table->double('minimum_distance_for_free_delivery')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_charge_setups');
    }
};
