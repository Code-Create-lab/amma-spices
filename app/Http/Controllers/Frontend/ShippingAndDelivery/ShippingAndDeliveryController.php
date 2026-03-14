<?php

namespace App\Http\Controllers\Frontend\ShippingAndDelivery;

use App\Http\Controllers\Controller;
use App\Models\ShippingAndDelivery;
use Illuminate\Http\Request;

class ShippingAndDeliveryController extends Controller
{
    public function index(){
        $shipping_and_delivery = ShippingAndDelivery::get();
        return view('frontend.shipping_and_delivery.index',[
            'shipping_and_delivery' => $shipping_and_delivery
        ]);
    }
}
