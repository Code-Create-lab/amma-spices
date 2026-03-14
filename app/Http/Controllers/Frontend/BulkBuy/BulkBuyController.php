<?php

namespace App\Http\Controllers\Frontend\BulkBuy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\BulkBuy\BulkBuyStoreRequest;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class BulkBuyController extends Controller
{
    public function index(){
        return view('frontend.bulk_buy.index');
    }
    public function store(BulkBuyStoreRequest $request){
        $data = [
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'message' => $request->message,
            'product_detail' => $request->product_detail,
            'gst_no' => $request->gst_no
        ];
        Enquiry::create($data);
        return Redirect::route('bulk-buy.index')->with('success','Your Enquiry Has Been Submitted Successfully!');
    }
}
