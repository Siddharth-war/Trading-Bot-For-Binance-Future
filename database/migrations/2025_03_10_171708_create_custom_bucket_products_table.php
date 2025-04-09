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
        Schema::create('custom_bucket_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('custom_bucket_id')->nullable(); // Foreign Key
            $table->unsignedBigInteger('product_id')->nullable(); // Foreign Key
            $table->string('qty')->nullable();
            $table->timestamps();
            $table->foreign('custom_bucket_id')->references('id')->on('custom_buckets')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_bucket_products');
    }
};
