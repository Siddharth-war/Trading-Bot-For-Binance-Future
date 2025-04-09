<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->string('add_on_taxes')->nullable();
            $table->string('add_on_prices')->nullable();
            $table->decimal('add_on_tax_amount', 8,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('add_on_taxes');
            $table->dropColumn('add_on_prices');
            $table->dropColumn('add_on_tax_amount');
        });
    }
};
