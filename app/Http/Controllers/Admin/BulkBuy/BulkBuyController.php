<?php

namespace App\Http\Controllers\Admin\BulkBuy;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class BulkBuyController extends Controller
{
    public function index(){
        $enquiries = Enquiry::paginate(10);
        return view('admin.bulk_buy.index',[
            'enquiries' => $enquiries
        ]);
    }

    public function delete($uuid){
        $enquiry = Enquiry::where('uuid',$uuid)->firstOrFail();
        $enquiry->delete();
        return Redirect::route('enquiry')->with('success','Enquiry Deleted Successfully');
    }
}
