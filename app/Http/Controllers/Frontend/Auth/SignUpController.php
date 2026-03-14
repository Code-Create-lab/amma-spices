<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Auth\SignUpRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SignUpController extends Controller
{
    public function index(){
        return view('frontend.auth.signup');
    }
    public function register(SignUpRequest $request){
        $data = [
            'name' => trim($request->name),
            'email' => trim($request->email),
            'user_phone' => trim($request->user_phone)
        ];
        $user = User::create($data);
        $chars = "0123456789";
        $otpval = "";
        for ($i = 0; $i < 6; $i++) {
            $otpval .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $user->refresh();
        $user->update([
            'otp_value' => $otpval
        ]);
        return Redirect::back()->with('success', 'User Registered successfully!');
    }
}
