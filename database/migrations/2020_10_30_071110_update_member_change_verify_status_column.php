<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMemberChangeVerifyStatusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->dropColumn(['phone_number_verify_status', 'email_address_verify_status']);
        });

        Schema::table('member', function (Blueprint $table) {
            $table->string("phone_number_verify_status", 20);
            $table->string("email_address_verify_status", 20);
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
