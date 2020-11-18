<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCallbackToTableOrderPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_payment', function (Blueprint $table) {
            $table->text('callback')->nullable();
            $table->string('status', 7)->comment('INQUIRY; PAID; CANCEL; EXPIRED; ERROR')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_payment', function (Blueprint $table) {
            $table->dropColumn(['callback']);
            $table->string('status', 7)->comment('INQUIRY; PAID; CANCEL; EXPIRED')->change();
        });
    }
}
