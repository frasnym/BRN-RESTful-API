<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member', function (Blueprint $table) {
            $table->increments('id');
            $table->string("code", 10);
            $table->string("full_name", 100);
            $table->string("phone_number", 30);
            $table->string("phone_number_verify_status", 10);
            $table->string("email_address", 100);
            $table->string("email_address_verify_status", 10);
            $table->longText("current_address");
            $table->string("shirt_size", 10);
            $table->string("position", 50);
            $table->string("account_status", 10);
            $table->longText("password");
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
        Schema::dropIfExists('member');
    }
}
