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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('day_id')->nullable()->after('id'); // Foreign Key
            $table->decimal('credit_limit', 10, 2)->default(0)->after('day_id'); // Money Format
            $table->decimal('remaining_credit_limit', 10, 2)->nullable()->after('credit_limit'); // String Option
            $table->string('branch_option')->nullable()->after('credit_limit'); // String Option

            $table->foreign('day_id')->references('id')->on('days')->onDelete('set null'); // Foreign Constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['day_id']);
            $table->dropColumn(['day_id', 'credit_limit', 'branch_option','remaining_credit_limit']);
        });
    }
};
