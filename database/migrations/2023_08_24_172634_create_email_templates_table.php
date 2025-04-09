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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title',100)->nullable();
            $table->text('body')->nullable();
            $table->string('background_image',100)->nullable();
            $table->string('image',100)->nullable();
            $table->string('logo',100)->nullable();
            $table->string('icon',100)->nullable();
            $table->string('button_name',100)->nullable();
            $table->string('button_url')->nullable();
            $table->string('footer_text')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('type')->nullable();
            $table->string('email_type')->nullable();
            $table->string('email_template')->nullable();
            $table->boolean('privacy')->default(0);
            $table->boolean('refund')->default(0);
            $table->boolean('cancelation')->default(0);
            $table->boolean('contact')->default(0);
            $table->boolean('facebook')->default(0);
            $table->boolean('instagram')->default(0);
            $table->boolean('twitter')->default(0);
            $table->boolean('linkedin')->default(0);
            $table->boolean('pinterest')->default(0);
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('email_templates');
    }
};
