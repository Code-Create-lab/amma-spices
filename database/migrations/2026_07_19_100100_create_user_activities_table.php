<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('visitor_session_id')->index();
            $table->string('visitor_id', 36)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('event_type', 30)->default('page_view');
            $table->string('page_url', 500)->nullable();
            $table->string('route_name', 100)->nullable();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('search_query')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedTinyInteger('scroll_depth')->default(0);
            $table->timestamps();

            $table->index(['event_type', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
