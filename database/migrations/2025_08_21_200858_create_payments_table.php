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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('payment_gateway')->default('payglocal')->index();
            $table->string('gid')->nullable()->unique()->comment('PayGlocal Global ID');
            $table->string('merchant_txn_id')->nullable()->index();
            $table->string('merchant_unique_id')->nullable()->index();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('INR');
            $table->string('payment_status')->nullable()->index();
            $table->string('payment_method')->nullable();
            $table->json('gateway_response')->nullable()->comment('Full response from payment gateway');
            $table->json('billing_data')->nullable()->comment('Customer billing information');
            $table->string('transaction_reference')->nullable();
            $table->string('bank_reference')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->boolean('is_refunded')->default(false);
            $table->decimal('refunded_amount', 10, 2)->default(0.00);
            $table->timestamp('refunded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['order_id', 'payment_gateway']);
            $table->index(['payment_status', 'created_at']);
            $table->index(['payment_status', 'payment_gateway']);
            $table->index(['is_refunded', 'refunded_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
