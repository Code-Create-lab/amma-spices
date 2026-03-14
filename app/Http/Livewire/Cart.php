<?php

namespace App\Http\Livewire;

use App\Models\Address;
use App\Models\Cart as ModelCart;
use App\Models\City;
use App\Models\Coupon;
use App\Models\Orders;
use App\Models\Society;
use App\Models\StoreOrders;
use App\Models\Variation;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Livewire;
use App\Models\Shipping;
use App\Events\SendOrderPlacedMailEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class Cart extends Component
{

    public $cart_items;
    public $cart_items_count;
    public $cart_total;
    public $product_id;
    public $coupon_code;
    public $cart_id;
    public $discount;
    public $final_price;
    public $coupon_id;
    public $subTotal = 0;
    public $total_saving;
    public $updateAddressButton = false;
    public $addressForm = false;

    public $addresses;
    public ?string $address_id = null;


    public $cartView = true;

    public $addressView = false;
    public $getShippingCharge;
    public $shippingAmount = 0.0;



    public $getCouponList;

    // public $currentQty=0 ;

    // public $quantity;

    public function mount()
    {


        $today = Carbon::today();
        $this->getCouponList = Coupon::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();
        $this->getShippingCharge = Shipping::where('status', 1)->first();
        $this->updateCart();

        //  $this->addresses = Address::where('user_id', Auth::id())->where('is_deleted', 0)->get();
        //  if(session()->get('addressView', [])){

        //      $this->addressView = true;
        //      $this->cartView = false;
        //     //  dd(session()->get('addressView', []),$this->addressView  );
        // }

        if (session()->get('coupon_code')) {

            $this->coupon_code = session()->get('coupon_code');
            $this->apply_coupon(session()->get('coupon_code'));
        }
        // dd(session()->get('coupon_code'));
        $this->calculateSubtotal();
        // dd($this->coupon_code, $this->discount, $this->final_price, $this->coupon_id, $this->total_saving);
        // dd($this->subTotal, $this->discount, $this->final_price, $this->coupon_code, $this->coupon_id, $this->total_saving);
    }

    public function emptyCart($user_id)
    {

        if ($user_id != 0) {

            ModelCart::where('user_id', $user_id)->delete();
        } else {

            session()->forget('cart');
        }

        $this->cart_items = [];
        $this->cart_items_count = 0;
        $this->cart_total = 0;
    }

    #[On('main-cart-updated')] // 🔥 listen to global event
    public function updateCart()
    {


        if (auth()->check()) {
            $cartItems = ModelCart::with(['product', 'variation.variation_attributes.attribute.attribute'])->where('user_id', Auth::id())->get();
            $cartTotal = $cartItems->sum(fn($item) => $item->quantity * $item->variation->mrp);

            $this->cart_items = $cartItems;
            // dd($this->cart_items);
            $this->cart_items_count = $cartItems->count();
            $this->cart_total = $cartTotal;
        } else {
            $cart = session()->get('cart', []);
            $cartItems = collect();
            $cartTotal = 0;

            foreach ($cart as $key => $item) {
                $variation = Variation::with(['product', 'variation_attributes.attribute.attribute'])->find($item['variation_id']);

                if ($variation && $item['quantity'] <= $variation->stock) {
                    $cartItems->push([
                        'variation_id' => $variation->id,
                        'quantity' => $item['quantity'],
                        'variation' => $variation,
                        'product' => $variation->product,
                    ]);
                    $cartTotal += $item['quantity'] * $variation->mrp;
                }
            }
            if ($cartItems->count() == 0) {
                session()->forget("coupon_code");
            }
            // dd('asdasd');
            //   dd($cartItems,session()->get('cart', []) );
            $this->cart_items = $cartItems;
            $this->cart_items_count = $cartItems->count();
            $this->cart_total = $cartTotal;
        }

        // dd($cart);
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

    //             $this->updateCart();
    //             // return response()->json(['success' => false, 'message' => $e->getMessage()]);
    //         }

    //     }else{

    //     try {


    //     $cart = session()->get('cart', []);

    //     //  dd($cart);
    //         $key = $product_id . '-' . $variation_id;

    //         // dd(session('testData'), $this->testData);
    //         // dd($key,$product_id . '-' . $variation_id ,$cart, $cart[$key]);
    //         if (isset($cart[$key])) {

    //             unset($cart[$key]); // 🗑️ Remove the item from cart
    //             session()->put('cart', $cart); // 🔁 Save updated cart back to session

    //             $message = 'Product removed from cart';
    //             // Emit a global event
    //             $this->updateCart(); // 🔥 emit global event


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

    public function stockNotAvailable()
    {

        return $this->dispatch('toast', [
            'type' => 'error',
            'success' => true,
            'message' => "No more stock available",
        ]);
    }

    #[On('updateQuantityFromJS')]
    public function updateQuantityFromJS($variation_id, $product_id,  $quantity)
    {

        // session()->forget('currentQty');
        // dd(session('currentQty'));
        // dd(session()->all() ,$quantity );

        if ($quantity == 0) {
            return   $this->removeProduct($variation_id,  $product_id);
        }

        if (session('currentQty-' . $variation_id) == 1 &&  $quantity < 1) {


            return $this->dispatch('toast', [
                'type' => 'error',
                'success' => true,
                'message' => "Product can not be less than 1",
            ]);
        }


        session()->put('currentQty-' . $variation_id, $quantity);


        if (auth()->check()) {
            try {
                DB::beginTransaction();
                $userId = Auth::id();

                // Merge session cart on first cart action
                // $this->mergeSessionCartToDatabase();

                $cartItem = ModelCart::where('product_id', $product_id)
                    ->where('variation_id', $variation_id)
                    ->where('user_id', $userId)
                    ->first();
                $variation = Variation::with(['product', 'variation_attributes.attribute.attribute'])->find($variation_id);


                if ($cartItem && $quantity <=  $variation->stock) {
                    $cartItem->quantity = $quantity;
                    $cartItem->save();
                    $message = 'Product quantity updated in cart';

                    $this->dispatch('toast', [
                        'type' => 'success',
                        'success' => true,
                        'message' => $message,
                    ]);
                } else {

                    $message = 'No more stock available';

                    $this->dispatch('toast', [
                        'type' => 'error',
                        'success' => true,
                        'message' => $message,
                    ]);
                }

                $this->updateCart(); // 🔥 emit global event

                if (session()->get('coupon_code')) {

                    $this->coupon_code = session()->get('coupon_code');
                    $this->apply_coupon();

                    // dd(($coupon_data));
                }
                DB::commit();



                // Emit a global event

            } catch (Exception $e) {
                //  dd("asdasd", $e);
                DB::rollBack();
                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => true,
                    'message' => $e->getMessage(),
                ]);
                // return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {

            // Guest user
            $cart = session()->get('cart', []);
            $key = $product_id . '-' . $variation_id;

            $variation = Variation::with(['product', 'variation_attributes.attribute.attribute'])->find($variation_id);

            // dd($variation , $quantity);
            if (($cart[$key]) && $quantity <=  $variation->stock) {
                $cart[$key]['quantity'] = (int)$quantity;
                $cart[$key]['product'] = $variation->product->toArray();
                $message = 'Product quantity updated in cart';
                session()->put('cart', $cart); // 🔁 update session here
                // dd($cart[$key]);

                $this->dispatch('toast', [
                    'type' => 'success',
                    'success' => true,
                    'message' => $message,
                ]);
            } else {
                $message = 'No more stock available';

                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => true,
                    'message' => $message,
                ]);
            }
            // dd( session()->get('cart', []), $cart[$key]);
            // Emit a global event
            $this->updateCart(); // 🔥 emit global event

            // $this->dispatch('toast', [
            //     'type' => 'success',
            //     'success' => true,
            //     'message' => $message,
            // ]);
            // session()->put('cart', $cart);

        }
        // dd($variation_id, $product_id,  $quantity);
        $this->updateCart();
        $this->calculateSubtotal();
        $this->dispatch('cart-updated');
        //  Livewire::dispatch('cart-updated');

    }




    public function loginError()
    {

        $this->dispatch('toast', [
            'type' => 'error',
            'success' => true,
            'message' => "Please Login!",
        ]);
    }



    public function checkout()
    {
        // Check if user is logged in
        if (Auth::check()) {
            // Logged in user cart from DB
            $cart_items = ModelCart::with(['product', 'variation'])
                ->where('user_id', Auth::id())
                ->get();
        } else {
            // Guest cart from session

            // $this->dispatch('toast', [
            //     'type' => 'error',
            //     'success' => true,
            //     'message' => "Please Login to proceed to checkout",
            // ]);
            $this->dispatch('show-login-modal');
            // return;
            return;


            $cart_items = collect(session('cart', [])); // returns empty array if null
        }

        // Check if cart is empty
        if ($cart_items->isEmpty()) {
            $this->dispatch('toast', [
                'type' => 'error',
                'success' => true,
                'message' => "Your Cart Is Empty",
            ]);
            return false;
        }

        // Redirect to checkout page
        return redirect()->route('checkout');
    }








    public function validateAddress()
    {

        $this->validate([
            // Basic Info
            'addressData.type' => 'required',
            'addressData.firstName' => 'required|string|min:2|max:50',
            'addressData.lastName' => 'required|string|min:2|max:50',

            // Contact Info
            'addressData.receiver_email' => 'required|email|max:255',
            'addressData.receiver_phone' => 'required|string|min:10|max:15|regex:/^[0-9+\-\s()]+$/',
            'addressData.alternate_phone' => 'required|string|min:10|max:15|regex:/^[0-9+\-\s()]+$/',

            // Address Info
            'addressData.house_no' => 'required|string|min:1|max:100',
            'addressData.street' => 'required|string|min:2|max:255',
            'addressData.city' => 'required|string|min:2|max:100',
            'addressData.state' => 'required|string|min:2|max:100',
            'addressData.pincode' => 'required|string|min:4|max:10|regex:/^[0-9A-Za-z\s\-]+$/',
        ], [
            // Delivery Type Messages
            'addressData.type.required' => 'Please select your delivery type.',

            // Name Messages
            'addressData.firstName.required' => 'Please enter your first name.',
            'addressData.firstName.min' => 'First name must be at least 2 characters.',
            'addressData.firstName.max' => 'First name cannot exceed 50 characters.',

            'addressData.lastName.required' => 'Please enter your last name.',
            'addressData.lastName.min' => 'Last name must be at least 2 characters.',
            'addressData.lastName.max' => 'Last name cannot exceed 50 characters.',

            // Contact Messages
            'addressData.receiver_email.required' => 'Please enter your email address.',
            'addressData.receiver_email.email' => 'Please enter a valid email address.',
            'addressData.receiver_email.max' => 'Email address is too long.',

            'addressData.receiver_phone.required' => 'Please enter your phone number.',
            'addressData.receiver_phone.min' => 'Phone number must be at least 10 digits.',
            'addressData.receiver_phone.max' => 'Phone number cannot exceed 15 characters.',
            'addressData.receiver_phone.regex' => 'Please enter a valid phone number.',

            'addressData.alternate_phone.required' => 'Please enter your alternate phone number.',
            'addressData.alternate_phone.min' => 'Alternate phone number must be at least 10 digits.',
            'addressData.alternate_phone.max' => 'Alternate phone number cannot exceed 15 characters.',
            'addressData.alternate_phone.regex' => 'Please enter a valid alternate phone number.',

            // Address Messages
            'addressData.house_no.required' => 'Please enter your house number and street name.',
            'addressData.house_no.min' => 'House number and street name is required.',
            'addressData.house_no.max' => 'House number and street name is too long.',

            'addressData.street.required' => 'Please enter additional address details (apartment, suite, etc.).',
            'addressData.street.min' => 'Address details must be at least 2 characters.',
            'addressData.street.max' => 'Address details are too long.',

            'addressData.city.required' => 'Please enter your city.',
            'addressData.city.min' => 'City name must be at least 2 characters.',
            'addressData.city.max' => 'City name is too long.',

            'addressData.state.required' => 'Please enter your state.',
            'addressData.state.min' => 'State name must be at least 2 characters.',
            'addressData.state.max' => 'State name is too long.',

            'addressData.pincode.required' => 'Please enter your postcode/ZIP.',
            'addressData.pincode.min' => 'Postcode/ZIP must be at least 4 characters.',
            'addressData.pincode.max' => 'Postcode/ZIP cannot exceed 10 characters.',
            'addressData.pincode.regex' => 'Please enter a valid postcode/ZIP.',
        ]);
    }


    public function addressStore($address_id)
    {

        $this->address_id = $address_id;


        // dd($this->addressData);
        // $addressvar = 23;

        // dd($this->addressData);
        try {
            $this->validate();
            DB::beginTransaction();

            // Create or find the city
            $city = City::firstOrCreate([
                'city_name' => $this->addressData['city'],
            ]);

            // 


            // dd($city->city_id ?? $city->id);
            // Common data for address
            $addressData = [
                'type' => $this->addressData['type'],
                'user_id' => Auth::id(),
                'receiver_name' => $this->addressData['firstName'] . " " . $this->addressData['lastName'],
                'receiver_phone' => $this->addressData['receiver_phone'],
                'city' => $this->addressData['city'],
                'city_id' => $city->city_id ?? $city->id,
                'house_no' => $this->addressData['house_no'],
                'street' => $this->addressData['street'],
                'landmark' => $this->addressData['type'],
                'state' => $this->addressData['state'],
                'pincode' => $this->addressData['pincode'],
                'lat' => 0,
                'lng' => 0,
                'select_status' => 1,
                'receiver_email' => $this->addressData['receiver_email'],
                'updated_at' => Carbon::now(),
            ];

            // Create or Update
            if ($this->address_id != 0) {
                $address = Address::where('uuid', $this->address_id)->first();
                if ($address) {
                    $address->update($addressData);
                } else {
                    // If address_id is invalid, treat it as a create
                    $address = Address::create(array_merge($addressData, [
                        'added_at' => Carbon::now(),
                    ]));
                }
            } else {

                try {
                    $address = Address::create(array_merge($addressData, [
                        'added_at' => Carbon::now(),
                    ]));
                    // dd($address);
                } catch (\Exception $e) {
                    // Log::error('Address creation failed: ' . $e->getMessage());
                    dd($e->getMessage());
                }
            }

            $this->addresses = Address::where('user_id', Auth::id())->where('is_deleted', 0)->get();
            DB::commit();

            $this->address_id = $address->uuid;
            $this->addressForm = false;

            $this->dispatch('toast', [
                'type' => 'success',
                'success' => true,
                'message' =>  "Address Updated Successfully",
            ]);

            // dd($address,$this->address_id);
            // Optional: Toast or redirect
            // $this->dispatch('toast', [
            //     'type' => 'success',
            //     'success' => true,
            //     'message' => 'Address saved successfully',
            // ]);
        } catch (Exception $e) {
            DB::rollBack();
            // dd($address,$this->address_id);
            $this->dispatch('toast', [
                'type' => 'error',
                'success' => true,
                'message' =>  $e->getMessage(),
            ]);

            // Handle error
            // $this->dispatch('toast', [
            //     'type' => 'error',
            //     'success' => false,
            //     'message' => $e->getMessage(),
            // ]);
        }
    }



    public function showAddress($addressId)
    {


        $getAddress = Address::where('uuid', $addressId)->first()->toArray();
        // dd($getAddress);
        $this->address_id = $addressId;

        $name = explode(' ', $getAddress['receiver_name']);


        $this->addressData = [
            'type' => $getAddress['type'],
            'user_id' => Auth::id(),
            'firstName' => $name[0],
            'lastName' => $name[1],
            'receiver_phone' => $getAddress['receiver_phone'],
            'alternate_phone' => $getAddress['alternate_phone'],
            'city' => $getAddress['city'],
            // 'society' => $this->society,
            // 'city_id' => $city->id,
            // 'society' => $society->id,
            'house_no' => $getAddress['house_no'],
            'street' => $getAddress['society'],
            'landmark' => $getAddress['type'],
            'state' => $getAddress['state'],
            'pincode' => $getAddress['pincode'],
            'lat' => 0,
            'lng' => 0,
            'select_status' => 1,
            'added_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'receiver_email' => $getAddress['receiver_email']
        ];

        $this->updateAddressButton = true;
        $this->addressForm = true;
    }


    // public function showAddressForm(){

    //     $this->addressForm = true;
    //     $this->addressData = [];
    // }


    public function apply_coupon($coupon_code_applied = null)
    {
        try {
            $coupon_code = $coupon_code_applied ?? $this->coupon_code;
            $coupon_code = trim($coupon_code);

            if (!$coupon_code) {
                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Please enter a coupon code.',
                ]);
            }

            $coupon = Coupon::where('coupon_code', $coupon_code)->first();

            if (!$coupon) {
                $this->coupon_code = "";
                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Invalid coupon code.',
                ]);
            }

            /**
             * 1a. Check coupon validity period (start_date & end_date)
             */
            $now = now();

            // Check if coupon has not started yet
            if ($coupon->start_date && $now->lt($coupon->start_date)) {
                $this->coupon_code = "";
                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'This coupon is not valid yet. Valid from ' . \Carbon\Carbon::parse($coupon->start_date)->format('d M Y'),
                ]);
            }

            // Check if coupon has expired
            if ($coupon->end_date && $now->gt($coupon->end_date)) {
                $this->coupon_code = "";
                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'This coupon has expired on ' . \Carbon\Carbon::parse($coupon->end_date)->format('d M Y'),
                ]);
            }

            /**
             * 1b. Get cart data (DB cart for logged-in user, session cart for guest)
             */
            $cart_total   = 0;
            $total_saving = 0;

            if (Auth::check()) {
                // Logged-in user → use ModelCart
                $cart_items = ModelCart::with(['product', 'variation', 'variation.variation_attributes.attribute.attribute'])
                    ->where('user_id', Auth::id())
                    ->get();

                if ($cart_items->isEmpty()) {
                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => 'Your cart is empty.',
                    ]);
                }

                $cart_total = $cart_items->sum(function ($item) {
                    if ($item->variation->price == 0) {
                        return $item->variation->mrp * $item->quantity;
                    } else {
                        return $item->variation->price * $item->quantity;
                    }
                });

                $total_saving = $cart_items->sum(function ($item) {
                    return ($item->variation->mrp - $item->variation->price) * $item->quantity;
                });
            } else {
                // Guest user → use session('cart')
                $sessionCart = collect(session('cart', []));

                if ($sessionCart->isEmpty()) {
                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => 'Your cart is empty.',
                    ]);
                }

                // Assumes: price, mrp, quantity keys exist in session cart
                $cart_total = $sessionCart->sum(function ($item) {
                    if ($item['price'] == 0 || $item['price'] == null) {
                        return ($item['mrp'] ?? 0) * ($item['quantity'] ?? 0);
                    } else {
                        return ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                    }
                });

                $total_saving = $sessionCart->sum(function ($item) {
                    $mrp      = $item['mrp'] ?? ($item['price'] ?? 0);
                    $price    = $item['price'];
                    $quantity = $item['quantity'] ?? 0;
                    return ($mrp - ($price == 0 ? $mrp : $price)) * $quantity;
                });
            }

            /**
             * 2. Check minimum cart value
             */
            if ($cart_total < $coupon->cart_value) {
                session()->forget("coupon_code");
                $this->coupon_code = '';
                $this->discount    = 0;

                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Minimum cart value should be ₹' . $coupon->cart_value,
                ]);
            }

            /**
             * 3. Check coupon usage (only for logged-in users, guests are treated as first time)
             */
            $coupon_uses = 0;
            if (Auth::check()) {
                $coupon_uses = DB::table('orders')
                    ->where('coupon_id', $coupon->coupon_id)
                    ->where('user_id', Auth::id())
                    ->where('order_status', '!=', 'Cancelled')
                    ->count();
            }

            if ($coupon_uses > 1 && $coupon->typecoupon == 1) {
                session()->forget("coupon_code");
                $this->coupon_code = '';
                $this->discount    = 0;
                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => "Maximum use limit reached for this coupon.",
                ]);
            }

            if ($coupon_uses >= $coupon->uses_restriction) {
                session()->forget("coupon_code");
                $this->coupon_code = '';
                $this->discount    = 0;
                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => "Maximum use limit reached for this coupon.",
                ]);
            }

            /**
             * 4. Calculate discount
             */
            if (strtolower($coupon->type) === 'percent') {
                $discount = ($cart_total * $coupon->amount) / 100;

                // Apply max discount if any
                if ($coupon->max_discount != 0 && $discount > $coupon->max_discount) {
                    $discount = $coupon->max_discount;
                }
            } else {
                $discount = $coupon->amount;
            }

            $discount     = min($discount, $cart_total); // Can't exceed cart value
            $final_price  = $cart_total - $discount;
            $total_saving = $total_saving + $discount;
            $this->shippingAmount = $this->getShippingAmount($cart_total - $discount);


            /**
             * 5. Store coupon details in session (works for both guest + logged in)
             */
            session([
                'applied_coupon' => [
                    'discount'      => round($discount, 2),
                    'final_price'   => round($final_price, 2),
                    'coupon_code'   => $coupon->coupon_code,
                    'coupon_id'     => $coupon->coupon_id,
                    'total_saving'  => round($total_saving, 2),
                ]
            ]);

            $this->discount     = round($discount, 2);
            $this->final_price  = round($final_price, 2) + $this->shippingAmount;
            $this->coupon_code  = $coupon->coupon_code;
            $this->coupon_id    = $coupon->coupon_id;
            $this->total_saving = round($total_saving, 2);
            $this->subTotal = $this->final_price;
            // dd($this->subTotal, $this->discount, $this->final_price, $this->coupon_code, $this->coupon_id, $this->total_saving);
            session()->put('coupon_code', $this->coupon_code);

            // $this->calculateSubtotal();
            // Notify other components
            $this->dispatch('cart-updated');

            if (!session('coupon_code') || $coupon_code_applied === null) {
                $this->dispatch('toast', [
                    'type'    => 'success',
                    'success' => true,
                    'message' => "Coupon Applied",
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type'    => 'error',
                'success' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function removeCoupon()
    {

        session()->forget("coupon_code");
        $this->coupon_code = '';
        $this->subTotal =  $this->final_price + $this->discount;
        $this->discount = 0;

        // dd(   $this->subTotal , $this->final_pric , );
        $this->dispatch('toast', [
            'type' => 'success',
            'success' => true,
            'message' =>  "Coupon Removed",
        ]);
    }

    public function calculateSubtotal()
    {
        $this->subTotal = 0;
        // dd($this->cart_items);
        if (auth()->user()) {

            $getCartItems = ModelCart::with(['product', 'variation.variation_attributes.attribute.attribute'])->where('user_id', Auth::id())->get();
        } else {
            $getCartItems = $this->cart_items;
        }
        //  dd( $getCartItems);
        foreach ($getCartItems as $item) {

            $mrp = $item['variation']->mrp ?? $item['product']->base_mrp;
            $price = $item['variation']->price ?? $item['product']->base_price;
            $this->subTotal += (float) ($price == 0 ? $mrp : $price) * (int) $item['quantity'];
        }
        $this->shippingAmount = $this->getShippingAmount($this->subTotal);


        // dd($mrp,($price == 0 ? $mrp : $price ),$this->subTotal);
        if (count($getCartItems) > 0) {
            // dd($this->subTotal, $this->discount);
            $this->subTotal += $this->getShippingAmount($this->subTotal - ($this->discount ?? 0));
            // dd($this->subTotal, $this->getShippingAmount($this->subTotal));

            //     $this->subTotal += ($this->subTotal < (int)$this->getShippingCharge->minimum_cart_value) ? $this->getShippingCharge->shipping_charge : 0;
        }
        if ($this->discount > 0) {
            $this->subTotal = $this->subTotal - $this->discount;
        }
    }

    private function getShippingAmount($subTotal)
    {
        $subTotal = (int) floor($subTotal);

        return Shipping::where('status', 1)
            ->where('minimum_cart_value', '<=', $subTotal)
            ->orderBy('minimum_cart_value', 'desc')
            ->value('shipping_charge') ?? 0;
    }

    public function removeProduct($variation_id, $product_id)
    {


        try {
            if (auth()->check()) {
                DB::beginTransaction();

                $userId = auth()->id();

                ModelCart::where('product_id', $product_id)
                    ->where('variation_id', $variation_id)
                    ->where('user_id', $userId)
                    ->delete();

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
            // $this->dispatch('cart-updated');
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

            return redirect(route('getCartItems'));
        } catch (\Exception $e) {
            DB::rollBack(); // Safe even if no transaction was started (no error)

            $this->dispatch('toast', [
                'type' => 'error',
                'success' => false,
                'message' => 'Failed to remove product: ' . $e->getMessage(),
            ]);
        }
    }


    public function render()
    {

        // try {
        return view('livewire.cart');

        //     } catch (\Throwable $e) {
        //     // \Log::error('Error rendering cart: ' . $e->getMessage());

        //     session()->flash('error', 'Unable to load your cart. Please try again.');

        //     return view('livewire.cart', [
        //         'cartItems' => collect(),
        //         'total' => 0
        //     ]);
        // }
    }
}
