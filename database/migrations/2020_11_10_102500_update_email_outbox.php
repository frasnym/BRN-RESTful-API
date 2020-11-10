<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEmailOutbox extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_outbox', function (Blueprint $table) {
            $table->dropColumn(['response']);
        });

        Schema::table('email_outbox', function (Blueprint $table) {
            $table->longText('response')->nullable();
        });

        Schema::table('key_user', function (Blueprint $table) {
            $table->dropColumn(['response']);
        });

        Schema::table('key_user', function (Blueprint $table) {
            $table->longText('response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
