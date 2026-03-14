<?php

namespace App\Http\Livewire;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Variation;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use PDO;

class NavCart extends Component
{

    public $cart_items;
    public $cart_items_count;
    public $cart_total;
    public $wishlist_items_count;
    public $quantity = 1;
    public $testData = 1;
    public $counterDAta = 0;
    public ?int $product_id = null;
    public ?int $variation_id = null;

    public function mount()
    {

        // session()->forget('cart');
        $this->updateCart();
        $this->updateWishlist();

        session()->put('testData', $this->testData);
    }


    public function loginWindow()
    {

    // dd('asdasd');
        $this->dispatch('show-login-modal');
    }

    #[On('addToWishlist')]
    public function addToWishlist(...$product_id)
    {


        // dd($prod uct_id,auth()->check());
        // Assign incoming values to component properties
        $this->product_id = $product_id[0] ?? null;


        $product_id = $product_id[0];
        // $variation = Variation::find($this->variation_id);
        $variation = Product::find($product_id)->variation;

        $this->variation_id  = $variation->id;

        if (!auth()->check()) {

            // dd( session()->get('wishlist'));
            // dd( $this->product_id , $product_id['product_id']);
            $wishlist = session()->get('wishlist', []);

            $key = $product_id;
            // dd($wishlist,isset($wishlist[$key]));
            if (isset($wishlist[$key])) {
                unset($wishlist[$key]);

                $message = 'Product removed from wishlist111111';
                $this->updateWishlist();
                session()->put('wishlist', $wishlist);
                $wishlist_count = collect($wishlist)->count();
                return  $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => true,
                    'message' => $message,
                ]);
            } else {
                $wishlist[$key] = [
                    'product_id' => $product_id,
                    'variation_id' => $this->variation_id
                ];
                $message = 'Product added to Wishlist successfully';

                $this->updateWishlist();
                session()->put('wishlist', $wishlist);
                $wishlist_count = collect($wishlist)->count();

                // $wishlist = session()->get('wishlist', []);
                // $cartItems = collect();
                // dd(($wishlist));
                // dd('asdasd');
                $this->wishlist_items_count = $wishlist_count;

                return  $this->dispatch('toast', [
                    'type' => 'success',
                    'success' => true,
                    'message' => $message,
                ]);

                // dd('asdas');
            }



            // dd($wishlist) ;
            // $this->updateWishlist();
            //    dd(session()->get('wishlist'), $wishlist);




            // return   $this->dispatch('toast', [
            //             'type' => 'error',
            //             'success' => true,
            //             'message' => 'Please Login!',
            //         ]);

        } else {

            try {
                $wishlist = Wishlist::where('product_id', $this->product_id)
                    ->whereHas('product', function ($query) {
                        $query->where('is_deleted', 0);
                    })->where('user_id', Auth::id())->first();


                if (empty($wishlist)) {
                    $product = Product::where('product_id', $this->product_id)->first();
                    Wishlist::create([
                        'user_id' => Auth::id(),
                        'quantity' => 1,
                        'product_id' => $this->product_id,
                        'store_id' => 1,
                        'product_name' => $product->product_name,
                        'description' => $product->description,
                        'price' => $product->base_price,
                        'mrp' => $product->base_mrp,
                        'product_image' => $product->product_image
                    ]);
                    $wishlist_count = Wishlist::where('user_id', Auth::id())
                        ->whereHas('product', function ($query) {
                            $query->where('is_deleted', 0);
                        })->count();
                    // dd($wishlist_count, $this->product_id);
                    $this->updateWishlist();

                    $this->dispatch('toast', [
                        'type' => 'success',
                        'success' => true,
                        'message' => 'Product Added To Wishlist',
                    ]);


                    return Response::json([
                        'success' => true,
                        'message' => 'Product Added To Wishlist',
                        'count' => $wishlist_count
                    ]);
                } else {

                    $this->dispatch('toast', [
                        'type' => 'success',
                        'success' => true,
                        'message' => 'Product Already In Wishlist'
                    ]);

                    return Response::json([
                        'success' => false,
                        'message' => 'Product Already In Wishlist'
                    ]);
                }
            } catch (Exception $e) {

                $this->dispatch('toast', [
                    'type' => 'success',
                    'success' => true,
                    'message' => $e->getMessage()
                ]);

                return Response::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }
    }


    #[On('addToCart')]
    public function addToCart($product_id, $quantity = 1)
    {

        
        // Assign incoming values to component properties
        $this->product_id = $product_id ?? null;
        $this->quantity =  $quantity ?? 1;
        // dd($product_id, $quantity);
        
        $product_id = $this->product_id;
        // $variation = Variation::find($this->variation_id);
        $variation = Product::find($product_id)->variation;
        // dd( $variation, $variation->stock, $this->quantity);
        

        $this->variation_id  = $variation->id;
        if (!$variation || $variation->stock < $this->quantity) {

            return  $this->dispatch('toast', [
                'type' => 'error',
                'success' => true,
                'message' => "Out Of stock",
            ]);
        }

        if (auth()->check()) {
            try {
                DB::beginTransaction();
                $userId = Auth::id();

                // Merge session cart on first cart action
                // $this->mergeSessionCartToDatabase();

                $cartItem = Cart::where('product_id', $product_id)
                    ->where('variation_id', $this->variation_id)
                    ->where('user_id', $userId)
                    ->first();

                if ($cartItem) {

                    if ($cartItem->quantity == $this->quantity) {

                        $message = 'Product already added in cart';
                    } else {

                        $cartItem->quantity = $this->quantity;
                        $cartItem->save();
                        $message = 'Product already added in cart';
                    }
                } else {
                    Cart::create([
                        'product_id' => $product_id,
                        'variation_id' => $this->variation_id,
                        'user_id' => $userId,
                        'quantity' => $this->quantity,
                    ]);
                    $message = 'Product added to cart successfully';
                }

                $cartItems = Cart::where('user_id', $userId)->with('variation')->get();
                $cartTotal = $cartItems->sum(fn($item) => $item->quantity * ($item->variation && $item->variation->price ? $item->variation->price : $item->base_price));
                // dd($cartTotal);

                DB::commit();


                // Emit a global event
                $this->dispatch('cart-updated'); // 🔥 emit global event


                return  $this->dispatch('toast', [
                    'type' => 'success',
                    'success' => true,
                    'message' => $message,
                ]);



                // dd("asdasd");
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => $cartItems->count(),
                    'cart_total' => $cartTotal,
                ]);
            } catch (Exception $e) {
                //  dd("asdasd", $e);
                DB::rollBack();
                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => true,
                    'message' => $e->getMessage(),
                ]);
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            // Guest user


            $cart = session()->get('cart', []);

            // dd($cart);
            $key = $product_id . '-' . $this->variation_id;
            $this->counterDAta++;
            if (isset($cart[$key])) {
                $cart[$key]['quantity'] = $this->quantity;
                $message = 'Product already added in cart';
            } else {
                $cart[$key] = [
                    'product_id' => $product_id,
                    'variation_id' => $this->variation_id,
                    'quantity' => $this->quantity,
                    'price' => $variation->price == 0 ? $variation->mrp : $variation->price,
                ];

                // dd($cart[$key]);
                $message = 'Product added to cart successfully';
            }

            session()->put('cart', $cart);
            $cartTotal = collect($cart)->sum(fn($item) => $item['quantity'] * $item['price']);

            // Emit a global event
            $this->dispatch('cart-updated'); // 🔥 emit global event

            response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => count($cart),
                'cart_total' => $cartTotal,
            ]);


            return   $this->dispatch('toast', [
                'type' => 'success',
                'success' => true,
                'message' => $message,
            ]);
        }
    }


    #[On('cart-updated')] // 🔥 listen to global event
    public function updateCart()
    {

        // dd("asdasd");

        if (auth()->check()) {
            $cartItems = Cart::with(['product', 'variation.variation_attributes.attribute.attribute'])->where('user_id', Auth::id())->get();

            $cartTotal = 0;
            foreach ($cartItems as $key => $item) {

                // dd($item);
                $product = Product::find($item['product_id']);
                $variation = Variation::with(['product', 'variation_attributes.attribute.attribute'])->find($item['variation_id']);
                if ($variation) {

                    $mrp =  $variation->mrp ?? $product['base_mrp'];
                    $price = $variation->price ?? $product['base_price'];
                    // $percentOff = $mrp > 0 && $price > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
                    if ($price == 0) {
                        $price = $mrp;
                    }

                    $cartTotal += $item['quantity'] * $price;
                }
            }


            // $cartTotal = $cartItems->sum(fn($item) => $item->quantity * $item->variation->mrp);


            $this->cart_items = $cartItems;
            $this->cart_items_count = $cartItems->count();

            // dd($this->cart_total);
            $this->cart_total = $cartTotal;
        } else {
            $cart = session()->get('cart', []);
            $cartItems = collect();
            $cartTotal = 0;
            // dd($cart);
            foreach ($cart as $key => $item) {

                // dd($item);
                $product = Product::find($item['product_id']);
                $variation = Variation::with(['product', 'variation_attributes.attribute.attribute'])->find($item['variation_id']);
                if ($variation) {
                    $cartItems->push([
                        'variation_id' => $variation->id,
                        'quantity' => $item['quantity'] ?? 1,
                        'variation' => $variation,
                        'product' => $variation->product,
                    ]);

                    $mrp =  $variation->mrp ?? $product['base_mrp'];
                    $price = $variation->price ?? $product['base_price'];
                    if ($price == 0) {
                        $price = $mrp;
                    }
                    // dd( $variation->price ?? $product['base_price']);
                    $percentOff = $mrp > 0 && $price > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;


                    $cartTotal += ($item['quantity'] ?? 1) * $price;

                    //  dd($this->cart_total,$cartTotal, $price ,$item['quantity'] ?? 1 * $price);

                }

                //
            }
            // 

            $this->cart_items = $cartItems;
            $this->cart_items_count = $cartItems->count();
            $this->cart_total = $cartTotal;
        }
    }

    #[On('removeFromWishlist')] // 🔥 listen to global event
    public function removeFromWishlist(...$product_id)
    {


        // dd($product_id);
        // Assign incoming values to component properties
        $product_id = $product_id[0] ?? null;
        $cart = session()->get('wishlist', []);
        // session()->forget('wishlist');
        $key = $product_id;


        // dd($key,$cart,session()->get('wishlist', [])  );
        if (auth()->user()) {


            // dd($this->product_id);
            try {
                Wishlist::where('product_id', $product_id)->where('user_id', Auth::id())->delete();
                $wishlist_count = Wishlist::where('user_id', Auth::id())->count();

                $this->updateWishlist();

                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => true,
                    'message' => "Product removed from Wishlist",
                ]);
                // return redirect(route('wishlist'));

                Response::json([
                    'success' => true,
                    'message' => 'Product Removed From Wishlist',
                    'count' => $wishlist_count
                ]);
            } catch (Exception $e) {

                return  $this->dispatch('toast', [
                    'type' => 'erroe',
                    'success' => true,
                    'message' =>  $e->getMessage()
                ]);
                // return Response::json([
                //     'success' => false,
                //     'message' => $e->getMessage()
                // ]);
            }
        } else {


            $wishlist = session()->get('wishlist', []);
            // dd($wishlist);
            if (isset($wishlist[$key])) {
                unset($wishlist[$key]);

                $message = 'Product removed from wishlist';
                $this->updateWishlist();
                session()->put('wishlist', $wishlist);
                $wishlist_count = collect($wishlist)->count();
                return  $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => true,
                    'message' => $message,
                ]);
            }

            // return redirect(route('wishlist'));
        }
    }

    #[On('updateWishlistNavCart')] // 🔥 listen to global event
    public function updateWishlist()
    {


        // dd(auth()->check());

        if (auth()->check()) {
            $wishlistItems = Wishlist::where('user_id', Auth::id())->whereHas('product', function ($query) {
                $query->where('is_deleted', 0);
            })->get();

            $this->wishlist_items_count = $wishlistItems->count();
        } else {


            $wishlist = session()->get('wishlist', []);
            // $cartItems = collect();
            // dd(($wishlist));
            // dd('asdasd');
            $this->wishlist_items_count = count($wishlist);
        }
    }


    #[On('removeProductFromJS')]
    public function productRemoved($variation_id, $product_id)
    {

        // dd($variation_id, $product_id, session()->get('cart', []));
        try {
            if (auth()->check()) {
                DB::beginTransaction();

                $userId = auth()->id();

                Cart::where('product_id', $product_id)
                    ->where('variation_id', $variation_id)
                    ->where('user_id', $userId)
                    ->delete();

                $cartItems = Cart::where('user_id', Auth::id())->with('variation')->get();
                
                if($cartItems->count() == 0)
                {
                        session()->forget("coupon_code");                 
                }

                DB::commit();
            } else {
                $cart = session()->get('cart', []);
                $key = $product_id . '-' . $variation_id;

                if (isset($cart[$key])) {
                    unset($cart[$key]); // 🗑️ Remove from session cart
                    session()->put('cart', $cart);
                }
            }

            logger('🚨 Product removed logic completed');
           
            usleep(300000); // 300ms delay
            $this->dispatch('cart-updated');
            $this->updateCart();
            logger('✅ Dispatched cart-updated');

            // $this->dispatch('main-cart-updated');
            logger('✅ Dispatched main-cart-updated');

            // ✅ Dispatch events after DB/session change is done
            // $this->dispatch('cart-updated');    

            // $this->dispatch('main-cart-updated');// For NavCart component
            // $this->dispatch('main-cart-updated');   // For Cart component
            $this->dispatch('toast', [              // Show success message
                'type' => 'success',
                'success' => true,
                'message' => 'Product removed from cart',
            ]);

            //  $this->dispatch('refresh-page');
            return redirect(request()->header('Referer'));
            // return redirect(route('getCartItems'));

        } catch (\Exception $e) {
            DB::rollBack(); // Safe even if no transaction was started (no error)

            $this->dispatch('toast', [
                'type' => 'error',
                'success' => false,
                'message' => 'Failed to remove product: ' . $e->getMessage(),
            ]);
        }
    }




    // #[On('removeProductFromJS')]
    // public function productRemoved($variation_id , $product_id){

    //     // dd(\$variation_id, $product_id);





    //     if (auth()->check()) {

    //          try {


    //             DB::beginTransaction();
    //             $userId = Auth::id();

    //             // Merge session cart on first cart action
    //             // $this->mergeSessionCartToDatabase();

    //             Cart::where('product_id', $product_id)
    //                 ->where('variation_id', $variation_id)
    //                 ->where('user_id', $userId)
    //                 ->delete();

    //             $message = 'Product removed from cart';
    //             DB::commit();

    //              $this->dispatch('cart-updated'); // 🔥 emit global event
    //              $this->dispatch('main-cart-updated');
    //              $this->dispatch('toast', [
    //                     'type' => 'success',
    //                     'success' => true,
    //                     'message' => $message,
    //                 ]);

    //                 // dd("asdsad");
    //             // Emit a global event
    //         //  return   $this->updateCart(); // 🔥 emit global event

    //         } catch (Exception $e) {
    //             //  dd("asdasd", $e);
    //             DB::rollBack();
    //             $this->dispatch('toast', [
    //                     'type' => 'error',
    //                     'success' => true,
    //                     'message' => $e->getMessage(),
    //                 ]);



    //             // return response()->json(['success' => false, 'message' => $e->getMessage()]);
    //         }

    //     }else{

    //     try {


    //     $cart = session()->get('cart', []);

    //     //  dd($cart);
    //         $key = $product_id . '-' . $variation_id;

    //         // dd(session('testData'), $this->testData);
    //         $this->testData += 1;
    //         // dd($key,$product_id . '-' . $variation_id ,$cart, $cart[$key]);
    //         if (isset($cart[$key])) {

    //             unset($cart[$key]); // 🗑️ Remove the item from cart
    //             session()->put('cart', $cart); // 🔁 Save updated cart back to session

    //             $message = 'Product removed from cart';
    //             // Emit a global event
    //            $this->dispatch('cart-updated'); // 🔥 emit global event
    //             $this->dispatch('main-cart-updated');

    //              $this->dispatch('toast', [
    //                     'type' => 'success',
    //                     'success' => true,
    //                     'message' => $message,
    //                 ]);
    //         } 

    //         } catch (Exception $e) {
    //             //  dd("asdasd", $e);
    //             DB::rollBack();
    //             $this->dispatch('toast', [
    //                     'type' => 'error',
    //                     'success' => true,
    //                     'message' => $e->getMessage(),
    //                 ]);

    //             // $this->updateCart();
    //             // return response()->json(['success' => false, 'message' => $e->getMessage()]);
    //         }

    //     }

    //     // dd(session()->get('cart', []));


    // }



    public function render()
    {
        return view('livewire.nav-cart');
    }
}
