<?php 

// app/Models/ShippingMethod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipping extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'shippings';
    protected $fillable = ['title', 'shipping_charge', 'minimum_cart_value', 'status'];
}
