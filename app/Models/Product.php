<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stripe\OrderItem;

class Product extends Model
{
    use HasFactory, HasSlug;
    public $primaryKey = 'product_id';
    protected $fillable = [
        'ean',
        'uuid',
        'cat_id',
        'product_name',
        'description',
        'info',
        'shipping',
        'product_image',
        'type',
        'hide',
        'hsn_number',
        'tax_id',
        'hide',
        'is_deleted',
        'added_by',
        'approved',
        'base_price',
        'base_mrp',
        'on_sale',
        'tags',
        'size_guide_images',
        'product_video',
    ];
    const TYPE = ['Simple', 'Variable'];
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('product_name')
            ->saveSlugsTo('slug');
    }
    public function getRouteKeyName()
    {
        return 'uuid';
    }

 

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id')->where('is_deleted', 0);
    }

    public function variations()
    {
        return $this->hasMany(Variation::class, 'product_id', 'product_id')->where('is_deleted', 0);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }
    public function variation()
    {
        return $this->hasOne(Variation::class, 'product_id', 'product_id')->where('is_deleted', 0);
    }

    public function is_in_cart()
    {
        return $this->hasOne(Cart::class, 'product_id', 'product_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, "tax_id", "tax_id");
    }

    public function cart()
    {
        return $this->hasOne(Cart::class, 'product_id', 'product_id')->where('user_id', auth()->id());
    }
    
    public function reviews()
    {
        return $this->hasMany(RatingReview::class, 'product_id', 'product_id');
    }

    public function getIsInCartAttribute()
    {
        return $this->is_in_cart()->exists();
    }
}
