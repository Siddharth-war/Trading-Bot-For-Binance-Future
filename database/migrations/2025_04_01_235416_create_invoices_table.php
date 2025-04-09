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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("vendor_id")->nullable();
            $table->string('upload_pdf');
            $table->longText('notes');
            $table->double('amount');
            $table->date('due_date');
            $table->tinyInteger('is_send')->comment('1->sent,2->no send');


            $table->string('invoice_number');
            $table->string('invoice_id');

            $table->tinyInteger('status')->comment('1->paid,2->unpaid,3->partially paid');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
