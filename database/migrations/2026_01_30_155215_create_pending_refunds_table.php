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
        Schema::create('pending_refunds', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_id');

            $table->decimal('amount', 10, 2);

            $table->string('reason', 255);

            $table->enum('status', ['pending', 'processed', 'failed'])
                ->default('pending');

            $table->timestamp('requested_at');

            $table->unsignedBigInteger('requested_by');

            // Optional but future-proof
            $table->timestamp('processed_at')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Indexes (important for admin queries)
            $table->index('order_id');
            $table->index('payment_id');
            $table->index('status');
            $table->index('requested_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pending_refunds');
    }
};
