<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('coupon')){
            $this->createCouponNewTable();
        } else {
            Schema::rename('coupon', 'coupon_backup');
            $this->createCouponNewTable();
        }
    }

    /**
     * Reverse the migrations. If user didn't erased backup it will try to revert migration to original point
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon');
        if(Schema::hasTable('coupon_backup')) Schema::rename('coupon_backup', 'coupon');
    }

    private function createCouponNewTable(){
        Schema::create('coupon', function (Blueprint $table) {
            $table->increments('coupon_id');
            $table->enum('typecoupon', ['default', 'forder'])->default('forder');
            $table->string('coupon_name', 30);
            $table->string('coupon_image');
            $table->string('coupon_code', 11);
            $table->string('coupon_description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->float('cart_value', 10, 2);
            $table->float('max_discount', 9, 2);
            $table->float('amount', 10, 2);
            $table->enum('type', ['percent', 'amount']);
            $table->integer('uses_restriction')->default('1');
            $table->integer('store_id');
        });
    }
}
