<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GalleryImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category_name',
        'category_slug',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return '';
        }

        $imagePath = str_replace('\\', '/', trim($this->image));

        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            return $imagePath;
        }

        if (Str::startsWith($imagePath, '/')) {
            return url($imagePath);
        }

        if (Str::startsWith($imagePath, 'storage/')) {
            return asset($imagePath);
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }
}
