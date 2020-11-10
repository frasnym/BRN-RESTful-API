<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeyUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_user', function (Blueprint $table) {
            $table->increments('id');
            $table->string("code", 10);
            $table->string("ip_address", 15);
            $table->string("status", 10)->comment("ACTIVE; USED; EXPIRED");
            $table->string("type", 30);
            $table->longText("value");
            $table->longText("response");
            $table->timestamp("expired_time");
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
        Schema::dropIfExists('key_user');
    }
}
