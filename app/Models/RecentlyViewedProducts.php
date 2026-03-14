<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentlyViewedProducts extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function product() {
        return $this->hasOne(Product::class, 'product_id','product_id');
    }
}
