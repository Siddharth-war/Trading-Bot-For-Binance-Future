<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->decimal('price')->default(0);
            $table->text('product_details')->nullable();
            $table->string('variation')->nullable();
            $table->bigInteger('add_on_id')->nullable();
            $table->decimal('discount_on_product')->nullable();
            $table->string('discount_type',20)->default('amount');
            $table->integer('quantity')->default(1);
            $table->decimal('tax_amount')->default(1);
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
        Schema::dropIfExists('order_details');
    }
}
