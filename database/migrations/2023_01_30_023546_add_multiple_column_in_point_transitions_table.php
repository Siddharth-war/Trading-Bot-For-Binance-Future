<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipleColumnInPointTransitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_transitions', function (Blueprint $table) {
            $table->string('transaction_id')->nullable();
            $table->decimal('credit',24,3)->default(0);
            $table->decimal('debit',24,3)->default(0);
            $table->string('reference',191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_transitions', function (Blueprint $table) {
            //
        });
    }
}
