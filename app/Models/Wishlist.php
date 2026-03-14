<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;
    protected $primaryKey = 'wish_id';
    protected $table = 'wishlist';
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'unit',
        'price',
        'mrp',
        'product_name',
        'description',
        'product_image',
        'store_id'
    ];
    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}
