<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {

            $table->id();

            // ── Hero ──────────────────────────────────────
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail')->nullable();
            $table->string('category')->nullable();

            // ── Author ────────────────────────────────────
            // NOTE: No default() here — apostrophes break MariaDB SQL syntax.
            //       Defaults are handled in the Model $attributes array instead.
            $table->string('author')->nullable();
            $table->string('author_role')->nullable();

            // ── Content ───────────────────────────────────
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->unsignedSmallInteger('read_time')->nullable();

            // ── Tags (JSON array) ─────────────────────────
            $table->json('tags')->nullable();

            // ── Publishing ────────────────────────────────
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // ── SEO ───────────────────────────────────────
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};