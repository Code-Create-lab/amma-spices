<?php

namespace App\Http\Controllers\Frontend\ContactUs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\ContactUs\ContactUsStoreRequest;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ContactUsController extends Controller
{
    public function index(){
        return view('frontend.contact_us.index');
    }
    public function store(ContactUsStoreRequest $request){
        $data = [
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'message' => $request->message
        ];
        ContactUs::create($data);
        return Redirect::route('contact-us.index')->with('success','Your Enquiry Has Been Submitted Successfully!');
    }
}
