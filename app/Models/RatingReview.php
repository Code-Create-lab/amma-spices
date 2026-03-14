<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatingReview extends Model
{

    use HasFactory, SoftDeletes;


    protected $fillable = ['user_id', 'product_id', 'rating', 'comment', 'order_item_id', 'is_approved'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id');
    }
}
