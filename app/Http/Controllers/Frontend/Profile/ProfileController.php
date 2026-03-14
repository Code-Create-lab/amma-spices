<?php

namespace App\Http\Controllers\Frontend\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Profile\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    public function index(){
        return view('frontend.profile.index');
    }
    public function update(ProfileUpdateRequest $request){
        // dd("asdsad");
        $data  = [
            'name' => $request->name,
            'user_phone' => $request->user_phone,
            'email' => $request->email
        ];

        Auth::user()->update($data);
           return Redirect::route('profile.index')->with('success','Profile Updated Successfully!');
    }
}
