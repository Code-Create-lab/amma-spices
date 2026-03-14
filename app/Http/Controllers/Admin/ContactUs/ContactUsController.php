<?php

namespace App\Http\Controllers\Admin\ContactUs;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use App\Exports\ContactUsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;

class ContactUsController extends Controller
{
    public function index(){
        $enquiries = ContactUs::orderBy('id', 'desc')->paginate(10);
        return view('admin.contact_us.index',[
            'enquiries' => $enquiries
        ]);
    }

    public function delete($uuid){
        $enquiry = ContactUs::where('uuid',$uuid)->firstOrFail();
        $enquiry->delete();
        return Redirect::route('contact_us')->with('success','Enquiry Deleted Successfully');
    }

    public function export(){
        $fileName = 'contact_us_enquiries_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new ContactUsExport(), $fileName);
    }
}
