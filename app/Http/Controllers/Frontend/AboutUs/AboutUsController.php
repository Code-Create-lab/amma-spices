<?php

namespace App\Http\Controllers\Frontend\AboutUs;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    public function index(){
        $about_us = AboutUs::get();
        return view('frontend.about_us.index',[
            'about_us' => $about_us
        ]);
    }
}
