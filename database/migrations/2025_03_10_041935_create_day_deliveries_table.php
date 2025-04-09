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
        Schema::create('day_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('day_id')->nullable();
            $table->string('location');
            $table->foreign('day_id')->references('id')->on('days')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_deliveries');
    }
};
