<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAndDelivery extends Model
{
    use HasFactory;
    protected $table = 'shippinganddeliverypage';
    protected $fillable = [
        'title',
        'description'
    ];
}
