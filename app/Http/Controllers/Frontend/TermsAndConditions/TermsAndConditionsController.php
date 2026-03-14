<?php

namespace App\Http\Controllers\Frontend\TermsAndConditions;

use App\Http\Controllers\Controller;
use App\Models\TermAndCondition;
use Illuminate\Http\Request;

class TermsAndConditionsController extends Controller
{
    public function index(){
        $terms_and_conditions = TermAndCondition::get();
        return view('frontend.terms_and_conditions.index',[
            'terms_and_conditions' => $terms_and_conditions
        ]);
    }
}
