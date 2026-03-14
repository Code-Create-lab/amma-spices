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
        Schema::create('shipment_trackings', function (Blueprint $table) {
            $table->id();

            // Core identifiers
            $table->string('awb_number')->index();
            $table->string('order_id')->nullable()->index(); // your internal order id or iThink order id

            // Tracking information from webhook
            $table->string('logistics_name')->nullable();
            $table->string('current_tracking_status')->nullable(); // numeric or status code
            $table->string('status')->nullable(); // readable status ("picked up", "in transit")
            $table->string('remark')->nullable();
            $table->string('location')->nullable();

            // Datetime fields from iThink
            $table->timestamp('latest_scan_time')->nullable();
            $table->timestamp('edd_date')->nullable();

            // Extras
            $table->string('tracking_url')->nullable();

            // Store raw payload for debugging
            $table->json('raw_payload')->nullable();

            // For idempotency (avoid duplicate entries)
            $table->unique(['awb_number', 'latest_scan_time']);

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
        Schema::dropIfExists('shipment_trackings');
    }
};
