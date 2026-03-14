<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreOrders extends Model
{
    use HasFactory;

    protected $table = 'store_orders';
    protected $primaryKey = 'store_order_id';

    public $timestamps = false;

    protected $fillable = [
        'product_name',
        'varient_image',
        'quantity',
        'unit',
        'varient_id',
        'qty',
        'price',
        'total_mrp',
        'order_cart_id',
        'order_date',
        'store_approval',
        'store_id',
        'description',
        'tx_per',
        'price_without_tax',
        'tx_price',
        'tx_name',
        'type'
    ];

    protected $attributes = [
        'store_approval' => 1,
        'tx_per' => NULL,
        'price_without_tax' => NULL,
        'tx_price' => NULL,
        'tx_name' => NULL, 
        'type' => 'Regular'
    ];

    public function variation()
    {
        return $this->belongsTo(Variation::class, 'varient_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_cart_id', 'cart_id');
    }
    public function review()
    {
        return $this->hasOne(RatingReview::class, 'order_item_id', 'store_order_id');
    }
}
