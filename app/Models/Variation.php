<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Variation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stock',
        'price',
        'image',
        'mrp',
        'uuid',
        'is_deleted'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = trim((string) Str::uuid());
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
    public function variation_attributes(){
        return $this->hasMany(VariationAttribute::class,'variation_id');
    }
}
