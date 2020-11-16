<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_method', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name", 20);
            $table->string("detail", 20);
            $table->string("payment_gateway", 20);
            $table->string("status", 10)->comment('ACTIVE; INACTIVE; COMING SOON');
            $table->longText("image_url");
        });

        Schema::create('sales_item', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name", 50);
            $table->integer("weigth_grams")->default(0);
            $table->integer("stock")->default(0);
            $table->integer("sales_count")->default(0);
            $table->string("status", 8)->comment('ACTIVE: INACTIVE');
        });

        Schema::create('order', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('member_id')->constrained('member');
            $table->foreignId('payment_method_id')->constrained('payment_method');
            $table->string("type", 8)->comment('SALES; REGISTER');
            $table->string("invoice", 22)->comment('[SLS/REG]YYYYMMDDHHIISS[5_RANDOM_STRING]');
            $table->string("status", 10)->comment('INQUIRY; REQPAYMENT; PAID; VERIFIED; SENT; DONE; REJECT; CANCEL; EXPIRED');
            $table->timestamp("date_inquiry");
            $table->timestamp("date_payment")->nullable();
            $table->integer("total_price")->default(0);
            $table->longText("shipment_address");
            $table->string("shipment_receipt_number", 50)->nullable();
            $table->integer("shipment_price")->default(0);
            $table->integer("grand_total_price")->default(0);
            $table->timestamps();
        });

        Schema::create('order_item', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');
            $table->foreignId('sales_item_id')->constrained('sales_item');
            $table->integer("quantity")->default(0);
        });

        Schema::create('order_payment', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_method');
            $table->string("status", 7)->comment('INQUIRY; PAID; CANCEL; EXPIRED');
            $table->longText("request")->nullable();
            $table->longText("response")->nullable();
            $table->longText("payment_code")->nullable();
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
        Schema::dropIfExists('payment_method');
        Schema::dropIfExists('sales_item');
        Schema::dropIfExists('order');
        Schema::dropIfExists('order_item');
        Schema::dropIfExists('order_payment');
    }
}
