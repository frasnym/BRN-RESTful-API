<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEmailOutbox extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_outbox', function (Blueprint $table) {
            $table->increments('id');
            $table->string("sender", 50);
            $table->string("recipient", 50);
            $table->string("subject", 50);
            $table->longText("body");
            $table->string("status", 10);
            $table->longText("response");
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
        Schema::dropIfExists('table_email_outbox');
    }
}
