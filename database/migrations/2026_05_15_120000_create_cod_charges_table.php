<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cod_charges', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('COD Charge');
            $table->decimal('cod_charge', 10, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        // Seed a default config row so the admin tab has something to edit.
        DB::table('cod_charges')->insert([
            'title' => 'COD Charge',
            'cod_charge' => 0,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cod_charges');
    }
};
