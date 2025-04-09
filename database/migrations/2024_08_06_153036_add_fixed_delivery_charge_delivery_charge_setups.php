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
        Schema::table('delivery_charge_setups', function (Blueprint $table) {
            $table->double('fixed_delivery_charge')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_charge_setups', function (Blueprint $table) {
            $table->dropColumn('fixed_delivery_charge');
        });
    }
};
