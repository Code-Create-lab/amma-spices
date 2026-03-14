<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table='cart';
    protected $primaryKey = 'cart_id';
    protected $fillable = [
        'product_id',
        'variation_id',
        'user_id',
        'quantity'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
    public function variation(){
        return $this->belongsTo(Variation::class,'variation_id','id');
    }
}
