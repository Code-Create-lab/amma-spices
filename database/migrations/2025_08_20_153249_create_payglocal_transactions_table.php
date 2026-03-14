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
        Schema::create('payglocal_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('gid')->unique();
            $table->string('merchant_txn_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('status');
            $table->text('redirect_url')->nullable();
            $table->text('status_url')->nullable();
            $table->json('callback_data')->nullable();
            $table->timestamps();
            
            $table->index(['merchant_txn_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payglocal_transactions');
    }
};
