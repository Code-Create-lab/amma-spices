<?php

namespace App\Http\Controllers\Frontend\ReturnAndExchange;

use App\Http\Controllers\Controller;
use App\Models\ReturnAndExchange;
use Illuminate\Http\Request;

class ReturnAndExchangeController extends Controller
{
    public function index(){
        $return_and_exchange = ReturnAndExchange::get();
        return view('frontend.return_and_exchange.index',[
            'return_and_exchange' => $return_and_exchange
        ]);
    }
}
