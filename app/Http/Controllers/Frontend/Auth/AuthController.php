<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Auth\AuthRequest;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    public function index()
    {

        return view("frontend.login.index");
    //    return redirect(route('login.index'))->with('error', 'Please Login!');
    }

    public function login(AuthRequest $request)
    {
        // dd($request->all());
        $user = User::where('user_phone', $request->user_phone)->where('otp_value', $request->otp)->first();
        if ($user) {
            Auth::login($user);
            $this->mergeSessionCartToDatabase();
            return Redirect::route('index')->with('success', 'Otp Matched Successfully');
        } else {
            return Redirect::back()->with('error', 'Invalid Credentials!');
        }
    }
    public function logout(){
        Auth::logout();
        return Redirect::route('index')->with('success','Logged Out Successfully!');
    }
    public function mergeSessionCartToDatabase()
    {
        $sessionCart = session()->get('cart', []);
        $userId = Auth::id();

        foreach ($sessionCart as $key => $item) {
            $dbCartItem = Cart::where('product_id', $item['product_id'])
                ->where('variation_id', $item['variation_id'])
                ->where('user_id', $userId)
                ->first();

            if ($dbCartItem) {
                $dbCartItem->quantity += $item['quantity'];
                $dbCartItem->save();
            } else {
                Cart::create([
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'user_id' => $userId,
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        session()->forget('cart');
    }
}
