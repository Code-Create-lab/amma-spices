<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'banner_image')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('banner_image')->nullable()->after('image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'banner_image')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('banner_image');
            });
        }
    }
};
