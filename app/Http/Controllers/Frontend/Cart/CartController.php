<?php

namespace App\Http\Controllers\Frontend\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Variation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CartController extends Controller
{




    public function addToCart(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'product_id' => 'required|exists:products,product_id',
            // 'variation_id' => 'required|exists:variations,id',
        ]);

        $variation = Variation::find($request->variation_id);

         if(!$variation){

            $variation = Product::find($request->product_id)->variation;
        // dd( $variation, $variation->stock, $this->quantity);
            // $variation_id  = $variation->id;
        }

        if (!$variation || $variation->stock < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Out of stock']);
        }

        if (auth()->check()) {
            try {
                DB::beginTransaction();
                $userId = Auth::id();

                // Merge session cart on first cart action
                // $this->mergeSessionCartToDatabase();
             
                $cartItem = Cart::where('product_id', $request->product_id)
                    ->where('variation_id', $request->variation_id ?? $variation->id)
                    ->where('user_id', $userId)
                    ->first();

                if ($cartItem) {
                    $cartItem->quantity = $request->quantity;
                    $cartItem->save();
                    $message = 'Product quantity updated in cart';
                } else {
                    Cart::create([
                        'product_id' => $request->product_id,
                        'variation_id' => $request->variation_id ?? $variation->id,
                        'user_id' => $userId,
                        'quantity' => $request->quantity,
                    ]);
                    $message = 'Product added to cart successfully';
                }

                $cartItems = Cart::where('user_id', $userId)->with('variation')->get();
                $cartTotal = $cartItems->sum(fn($item) => $item->quantity * $item->variation->price);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => $cartItems->count(),
                    'cart_total' => $cartTotal,
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            // Guest user
            $cart = session()->get('cart', []);
            // session()->forget('cart');

            // dd(session()->get('cart', []));
            if($request->variation_id != ""){

                $varientID = $request->variation_id;
            }else{
                               
                $varientID =  $variation->id;
            }
            $key = $request->product_id . '-' .  $varientID;
            // dd($key );

            if (isset($cart[$key])) {
                $cart[$key]['quantity'] = $request->quantity;
                $message = 'Product quantity updated in cart';
            } else {
                $cart[$key] = [
                    'product_id' => $request->product_id,
                    'variation_id' => $request->variation_id ?? $variation->id,
                    'quantity' => $request->quantity,
                    'price' => $variation->price,
                ];
                $message = 'Product added to cart successfully';
            }

            session()->put('cart', $cart);
            $cartTotal = collect($cart)->sum(fn($item) => $item['quantity'] * $item['price']);

            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => count($cart),
                'cart_total' => $cartTotal,
            ]);
        }
    }

    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'variation_id' => 'required|exists:variations,id',
        ]);

        $product = Product::where('product_id',$request->product_id)->first();
        if (auth()->check()) {
            try {
                DB::beginTransaction();
                $cartItem = Cart::where('product_id', $request->product_id)
                    ->where('variation_id', $request->variation_id)
                    ->where('user_id', Auth::id())
                    ->first();
                if ($cartItem) {
                    $cartItem->delete();
                    $message = 'Product removed from cart';
                } else {
                    $message = 'Product not found in cart';
                }

                $cartItems = Cart::where('user_id', Auth::id())->with('variation')->get();
                $cartTotal = $cartItems->sum(fn($item) => $item->quantity * $item->variation->price);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => $cartItems->count(),
                    'cart_total' => $cartTotal,
                    'product' => $product
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $cart = session()->get('cart', []);
            $key = $request->product_id . '-' . $request->variation_id;

            if (isset($cart[$key])) {
                unset($cart[$key]);
                session()->put('cart', $cart);
                $message = 'Product removed from cart';
            } else {
                $message = 'Product not found in cart';
            }

            $cartTotal = collect($cart)->sum(fn($item) => $item['quantity'] * $item['price']);

            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => count($cart),
                'cart_total' => $cartTotal,
                'product' => $product
            ]);
        }
    }

    public function cartUpdateDecrease(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'variation_id' => 'required|exists:variations,id',
        ]);

        $product = Product::where('product_id',$request->product_id)->first();
        if (auth()->check()) {
            try {
                DB::beginTransaction();
                $cartItem = Cart::where('product_id', $request->product_id)
                    ->where('variation_id', $request->variation_id)
                    ->where('user_id', Auth::id())
                    ->first();
                if ($cartItem) {
                    if ($cartItem->quantity > 1) {
                        $cartItem->decrement('quantity');
                        $message = 'Product quantity decreased';
                    } else {
                        $cartItem->delete();
                        $message = 'Product removed from cart';
                    }
                } else {
                    $message = 'Product not found in cart';
                }

                $cartItems = Cart::where('user_id', Auth::id())->with('variation')->get();
                $cartTotal = $cartItems->sum(fn($item) => $item->quantity * $item->variation->price);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => $cartItems->count(),
                    'cart_total' => $cartTotal,
                    'item_total' => $cartItem ? $cartItem->variation->price * $cartItem->quantity : 0,
                    'product' => $product
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $cart = session()->get('cart', []);
            $key = $request->product_id . '-' . $request->variation_id;

            if (isset($cart[$key])) {
                if ($cart[$key]['quantity'] > 1) {
                    $cart[$key]['quantity']--;
                    $message = 'Product quantity decreased';
                } else {
                    unset($cart[$key]);
                    $message = 'Product removed from cart';
                }
            } else {
                $message = 'Product not found in cart';
            }

            session()->put('cart', $cart);
            $cartTotal = collect($cart)->sum(fn($item) => $item['quantity'] * $item['price']);
            $itemTotal = isset($cart[$key]) ? $cart[$key]['quantity'] * $cart[$key]['price'] : 0;

            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => count($cart),
                'cart_total' => $cartTotal,
                'item_total' => $itemTotal,
                'product' => $product
            ]);
        }
    }


    public function cartUpdateIncrease(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'variation_id' => 'required|exists:variations,id',
        ]);
        $product = Product::where('product_id',$request->product_id)->first();
        $variation = Variation::find($request->variation_id);
        if (!$variation) {
            return response()->json(['success' => false, 'message' => 'Variation not found']);
        }

        if (auth()->check()) {
            try {
                DB::beginTransaction();
                $cartItem = Cart::where('product_id', $request->product_id)
                    ->where('variation_id', $request->variation_id)
                    ->where('user_id', Auth::id())
                    ->first();

                if ($cartItem) {
                    $cartItem->increment('quantity');
                    $message = 'Product quantity increased';
                } else {
                    $message = 'Product not found in cart';
                }

                $cartItems = Cart::where('user_id', Auth::id())->with(['variation','product'])->get();
                $cartTotal = $cartItems->sum(fn($item) => $item->quantity * $item->variation->price);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => $cartItems->count(),
                    'cart_total' => $cartTotal,
                    'item_total' => $cartItem ? $cartItem->variation->price * $cartItem->quantity : 0,
                    'product' => $product
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $cart = session()->get('cart', []);
            $key = $request->product_id . '-' . $request->variation_id;

            if (isset($cart[$key])) {
                $cart[$key]['quantity']++;
                $message = 'Product quantity increased';
            } else {
                $message = 'Product not found in cart';
            }

            session()->put('cart', $cart);
            $cartTotal = collect($cart)->sum(fn($item) => $item['quantity'] * $item['price']);
            $itemTotal = isset($cart[$key]) ? $cart[$key]['quantity'] * $cart[$key]['price'] : 0;

            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => count($cart),
                'cart_total' => $cartTotal,
                'item_total' => $itemTotal,
                'product' => $product
            ]);
        }
    }


    public function getCartItems()
    {

        // dd("getCartItems");
        if (auth()->check()) {
            $cartItems = Cart::with(['product', 'variation.variation_attributes.attribute.attribute'])->where('user_id', Auth::id())->get();
        //   dd( $cartItems ); 
            $cartTotal = $cartItems->sum(fn($item) => $item->quantity * $item->variation->price);


            return view('frontend.cart.index', [
                'success' => true,
                'cart_items' => $cartItems,
                'cart_count' => $cartItems->count(),
                'cart_total' => $cartTotal,
            ]);

            return response()->json([
                'success' => true,
                'cart_items' => $cartItems,
                'cart_count' => $cartItems->count(),
                'cart_total' => $cartTotal,
            ]);
        } else {
            $cart = session()->get('cart', []);
            $cartItems = collect();
            $cartTotal = 0;

            foreach ($cart as $key => $item) {
                $variation = Variation::with(['product', 'variation_attributes.attribute.attribute'])->find($item['variation_id']);
                if ($variation) {
                    $cartItems->push([
                        'variation_id' => $variation->id,
                        'quantity' => $item['quantity'],
                        'variation' => $variation,
                        'product' => $variation->product,
                    ]);
                    $cartTotal += $item['quantity'] * $variation->price;
                }
            }
            // dd('asdasd');

            return view('frontend.cart.index', [
                'success' => true,
                'cart_items' => $cartItems,
                'cart_count' => $cartItems->count(),
                'cart_total' => $cartTotal,
            ]);
            // return response()->json([
            //     'success' => true,
            //     'cart_items' => $cartItems,
            //     'cart_count' => $cartItems->count(),
            //     'cart_total' => $cartTotal,
            // ]);
        }
    }


}
