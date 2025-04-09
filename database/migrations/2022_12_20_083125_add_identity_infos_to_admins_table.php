<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdentityInfosToAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('identity_image')->after('status')->nullable();
            $table->string('identity_type', 30)->after('status')->nullable();
            $table->string('identity_number', 30)->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('identity_number');
            $table->dropColumn('identity_type');
            $table->dropColumn('identity_image');
        });
    }
}
