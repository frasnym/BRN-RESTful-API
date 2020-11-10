<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'email_address']);
        });

        Schema::table('member', function (Blueprint $table) {
            $table->string('phone_number', 30)->unique();
            $table->string('email_address', 100)->unique();
            $table->longText('api_token');
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
