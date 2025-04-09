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
        Schema::table('product_by_branches', function (Blueprint $table) {
            $table->string('stock_type')->default('unlimited');
            $table->integer('stock')->default(0);
            $table->integer('sold_quantity')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_by_branches', function (Blueprint $table) {
            //
        });
    }
};
