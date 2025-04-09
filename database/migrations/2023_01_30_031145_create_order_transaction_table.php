<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('delivery_man_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->decimal('order_amount')->default(0);
            $table->string('received_by')->nullable();
            $table->decimal('delivery_charge')->default(0);
            $table->decimal('original_delivery_charge')->default(0);
            $table->decimal('tax')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_transactions');
    }
}
