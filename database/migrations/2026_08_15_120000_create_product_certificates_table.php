<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_certificates', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->index();
            $table->string('title');
            $table->string('file');
            $table->string('file_type', 10)->default('image');
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_certificates');
    }
};
