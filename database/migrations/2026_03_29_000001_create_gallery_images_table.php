<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category_name', 100);
            $table->string('category_slug', 100)->index();
            $table->string('image');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('gallery_images')->insert([
            [
                'title' => 'Signature Spice Blends',
                'category_name' => 'Products',
                'category_slug' => 'products',
                'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=600&q=80',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Spice Festival 2024',
                'category_name' => 'Events',
                'category_slug' => 'events',
                'image' => 'https://images.unsplash.com/photo-1529543544282-ea669407fca3?w=600&q=80',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Handcrafting Each Blend',
                'category_name' => 'Behind the Scenes',
                'category_slug' => 'behind-the-scenes',
                'image' => 'https://images.unsplash.com/photo-1505253716362-afaea1d3d1af?w=600&q=80',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Premium Jar Collection',
                'category_name' => 'Packaging',
                'category_slug' => 'packaging',
                'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Market Day Showcase',
                'category_name' => 'Events',
                'category_slug' => 'events',
                'image' => 'https://images.unsplash.com/photo-1470119693884-47d3a1d1f180?w=600&q=80',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Rasam & Sambar Powders',
                'category_name' => 'Products',
                'category_slug' => 'products',
                'image' => 'https://images.unsplash.com/photo-1615485500704-8e990f9900f7?w=600&q=80',
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Sun-Drying Spices',
                'category_name' => 'Behind the Scenes',
                'category_slug' => 'behind-the-scenes',
                'image' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=600&q=80',
                'sort_order' => 7,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Cooking Demo - Chennai',
                'category_name' => 'Events',
                'category_slug' => 'events',
                'image' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&q=80',
                'sort_order' => 8,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Gift Box Sets',
                'category_name' => 'Packaging',
                'category_slug' => 'packaging',
                'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600&q=80',
                'sort_order' => 9,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Chettinad Masala',
                'category_name' => 'Products',
                'category_slug' => 'products',
                'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=600&q=80',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Stone Grinding Process',
                'category_name' => 'Behind the Scenes',
                'category_slug' => 'behind-the-scenes',
                'image' => 'https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=600&q=80',
                'sort_order' => 11,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Food Expo Stall 2023',
                'category_name' => 'Events',
                'category_slug' => 'events',
                'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80',
                'sort_order' => 12,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};
