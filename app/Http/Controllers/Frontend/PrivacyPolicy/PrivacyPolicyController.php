<?php

namespace App\Http\Controllers\Frontend\PrivacyPolicy;

use App\Http\Controllers\Controller;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function index(){
        $privacy_policy = PrivacyPolicy::get();
        return view('frontend.privacy_policy.index',[
            'privacy_policy' => $privacy_policy
        ]);
    }
}
