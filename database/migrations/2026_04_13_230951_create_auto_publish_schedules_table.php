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
        Schema::create('auto_publish_schedules', function (Blueprint $table) {
            $table->id();

            // ── Identity ──────────────────────────────────
            $table->string('name');                     // e.g. "Tech Blog Week #1"
            $table->text('context');                    // AI writing instructions / topic

            // ── Blog Defaults ─────────────────────────────
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->string('author')->nullable();
            $table->string('author_role')->nullable();

            // ── AI Configuration ──────────────────────────
            $table->enum('ai_provider', ['openrouter', 'claude'])->default('openrouter');
            $table->string('ai_model')->default('anthropic/claude-3.5-sonnet');

            // ── Schedule Window ───────────────────────────
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('blogs_per_day')->default(1); // max 5

            // ── Runtime Tracking ──────────────────────────
            $table->enum('status', ['active', 'paused', 'completed', 'failed'])
                ->default('active');
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedInteger('total_generated')->default(0);
            $table->text('last_error')->nullable();

            // ── Notes ─────────────────────────────────────
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auto_publish_schedules');
    }
};
