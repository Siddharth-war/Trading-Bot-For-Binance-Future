<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFloodRoadToCustomerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('road', 50)->nullable()->after('contact_person_number');
            $table->string('house', 50)->nullable()->after('contact_person_number');
            $table->string('floor', 10)->nullable()->after('contact_person_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn('road');
            $table->dropColumn('house');
            $table->dropColumn('floor');
        });
    }
}
