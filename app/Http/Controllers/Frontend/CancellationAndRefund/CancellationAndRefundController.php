<?php

namespace App\Http\Controllers\Frontend\CancellationAndRefund;

use App\Http\Controllers\Controller;
use App\Models\CancelAndRefund;
use Illuminate\Http\Request;

class CancellationAndRefundController extends Controller
{
    public function index(){
        $cancel_and_refund = CancelAndRefund::get();
        return view('frontend.cancel_and_refund.index',[
            'cancel_and_refund' => $cancel_and_refund
        ]);
    }
}
