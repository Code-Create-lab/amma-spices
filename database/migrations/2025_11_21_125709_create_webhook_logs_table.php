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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('awb_number')->index();
            $table->string('logistics_name')->nullable();
            $table->json('payload');
            $table->string('status_code')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->string('signature')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamps();

            // For idempotency: unique on awb + latest_scan_time if they provide exact timestamp
            $table->unique(['awb_number', 'received_at']); // adjust if you get exact latest_scan_time field
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webhook_logs');
    }
};
