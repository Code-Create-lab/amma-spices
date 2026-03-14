<?php

namespace App\Http\Livewire;

use App\Events\SendOrderPlacedMailEvent;
use App\Models\Address;
use App\Models\City;
use App\Models\Coupon;
use App\Models\Orders;
use App\Models\StoreOrders;
use App\Models\Variation;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\User;
use App\Models\OrderPayments;
use App\Services\PayGlocalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Models\Shipping;
use App\Jobs\OrderPlacedEmailJob;
use App\Jobs\SendOrderNotificationJob;


class Checkout extends Component
{
    // ONLY store primitive types - this is crucial for Livewire
    public string $address_id = '';
    public float $discount = 0.0;
    public float $final_price = 0.0;
    public int $coupon_id = 0;
    public float $subTotal = 0.0;
    public float $total_saving = 0.0;
    public bool $addressForm = false;
    public string $coupon_code = '';
    public int $cart_items_count_checkOut = 0;
    public float $cart_total_checkOut = 0.0;
    public $order_condition = false;
    public $payment_option = "";
    public $codOrderCount = 0;
    public $guestUser;
    public $getShippingCharge;

    public $shippingAmount = 0.0;

    // Store address data as simple array - FIXED STRUCTURE
    public array $addressData = [
        'type' => '',
        'firstName' => '',
        'lastName' => '',
        'receiver_email' => '',
        'receiver_phone' => '',
        'alternate_phone' => '',
        'house_no' => '',
        'society' => '',
        'city' => '',
        'state' => '',
        'pincode' => '',
        'landmark' => '',
    ];

    // Store cart items as simple arrays instead of Eloquent models
    public array $cart_items_checkOut = [];

    // Store coupon list as simple array instead of collection
    public array $getCouponList = [];

    protected $listeners = [];

    // Add this method to help debug serialization issues
    public function dehydrate()
    {
        // Ensure all properties are primitives before serialization
        $this->address_id = (string)$this->address_id;
        $this->discount = (float)$this->discount;
        $this->final_price = (float)$this->final_price;
        $this->coupon_id = (int)$this->coupon_id;
        $this->subTotal = (float)$this->subTotal;
        $this->total_saving = (float)$this->total_saving;
        $this->addressForm = (bool)$this->addressForm;
        $this->coupon_code = (string)$this->coupon_code;
        $this->cart_items_count_checkOut = (int)$this->cart_items_count_checkOut;
        $this->cart_total_checkOut = (float)$this->cart_total_checkOut;

        // Ensure arrays are properly formatted
        if (!is_array($this->cart_items_checkOut)) {
            $this->cart_items_checkOut = [];
        }
        if (!is_array($this->getCouponList)) {
            $this->getCouponList = [];
        }
        if (!is_array($this->addressData)) {
            $this->resetAddressData();
        }
    }

    // Computed properties for complex data
    public function getAddressesProperty()
    {
        if (!Auth::id()) {

            $guestUserId =  session('guestUser')->id ?? null;
        }
        return Address::where('user_id', Auth::id() ??  $guestUserId)->where('is_deleted', 0)->get();
    }

    public function getSelectedAddressProperty()
    {
        if ($this->address_id) {
            return Address::where('uuid', $this->address_id)->first();
        }
        return null;
    }

    public function getCouponsProperty()
    {
        $today = Carbon::today();
        return Coupon::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();
    }

    protected function rules()
    {
        $user = auth()->user();
        return [
            'addressData.type' => 'required',
            'addressData.firstName' => 'required|string|min:2|max:50',
            'addressData.lastName' => 'required|string|min:2|max:50',
            'addressData.receiver_email' => [$user->email ? 'nullable' : 'required', 'email', 'max:255'],
            'addressData.receiver_phone' => [$user->user_phone ? 'nullable' : 'required', 'string', 'min:10', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'addressData.alternate_phone' => 'string|min:10|max:15|regex:/^[0-9+\-\s()]+$/',
            'addressData.house_no' => 'required|string|min:1|max:100',
            'addressData.society' => 'required|string|min:2|max:255',
            'addressData.city' => 'required|string|min:2|max:100',
            'addressData.state' => 'required|string|min:2|max:100',
            'addressData.pincode' => 'required|string|min:4|max:6|regex:/^[0-9A-Za-z\s\-]+$/',
        ];
    }

    protected function messages()
    {
        return [
            'addressData.type.required' => 'Please select your delivery type.',
            'addressData.firstName.required' => 'Please enter your first name.',
            'addressData.firstName.min' => 'First name must be at least 2 characters.',
            'addressData.firstName.max' => 'First name cannot exceed 50 characters.',
            'addressData.lastName.required' => 'Please enter your last name.',
            'addressData.lastName.min' => 'Last name must be at least 2 characters.',
            'addressData.lastName.max' => 'Last name cannot exceed 50 characters.',
            'addressData.receiver_email.required' => 'Please enter your email address.',
            'addressData.receiver_email.email' => 'Please enter a valid email address.',
            'addressData.receiver_email.max' => 'Email address is too long.',
            'addressData.receiver_phone.required' => 'Please enter your phone number.',
            'addressData.receiver_phone.min' => 'Phone number must be at least 10 digits.',
            'addressData.receiver_phone.max' => 'Phone number cannot exceed 10 characters.',
            'addressData.receiver_phone.regex' => 'Please enter a valid phone number.',
            // 'addressData.alternate_phone.required' => 'Please enter your alternate phone number.',
            'addressData.alternate_phone.min' => 'Alternate phone number must be at least 10 digits.',
            'addressData.alternate_phone.max' => 'Alternate phone number cannot exceed 10 characters.',
            'addressData.alternate_phone.regex' => 'Please enter a valid alternate phone number.',
            'addressData.house_no.required' => 'Please enter your house number and street name.',
            'addressData.house_no.min' => 'House number and street name is required.',
            'addressData.house_no.max' => 'House number and street name is too long.',
            'addressData.society.required' => 'Please enter additional address details (apartment, suite, etc.).',
            'addressData.society.min' => 'Address details must be at least 2 characters.',
            'addressData.society.max' => 'Address details are too long.',
            'addressData.city.required' => 'Please enter your city.',
            'addressData.city.min' => 'City name must be at least 2 characters.',
            'addressData.city.max' => 'City name is too long.',
            'addressData.state.required' => 'Please enter your state.',
            'addressData.state.min' => 'State name must be at least 2 characters.',
            'addressData.state.max' => 'State name is too long.',
            'addressData.pincode.required' => 'Please enter your postcode/ZIP.',
            'addressData.pincode.min' => 'Postcode/ZIP must be at least 4 characters.',
            'addressData.pincode.max' => 'Postcode/ZIP cannot exceed 6 characters.',
            'addressData.pincode.regex' => 'Please enter a valid postcode/ZIP.',
        ];
    }

    #[Computed]
    public function first_addresses()
    {
        // dd(auth()->user()->addressess);
        return auth()->user()->addresses; // or however you fetch addresses
    }

    public function mount()
    {
        //  dd(auth()->user()->addresses, $this->addresses);
        // Initialize all properties as primitives first
        $this->address_id = '';
        $this->discount = 0.0;
        $this->final_price = 0.0;
        $this->coupon_id = 0;
        $this->subTotal = 0.0;
        $this->total_saving = 0.0;
        $this->addressForm = false;
        $this->coupon_code = '';
        $this->cart_items_count_checkOut = 0;
        $this->cart_total_checkOut = 0.0;
        $this->cart_items_checkOut = [];
        $this->getCouponList = [];

        $this->payment_option = 'ONLINE';
        if ($this->addresses->isNotEmpty()) {
            $this->address_id = $this->addresses->first()->uuid;
        }

        // if(!Auth::user()){
        //     // dd(session('cart'));
        //      $this->cart_items_checkOut = (session('cart'));
        // }

        $this->getShippingCharge = Shipping::where('status', 1)->first();
        // $this->shippingAmount = $this->getShippingAmount($this->subTotal);
        $this->codOrderCount = Orders::where('user_id', Auth::id())->where('payment_method', "COD")->count();
        // dd( $this->codOrderCount);
        $this->resetAddressData(); // Initialize address data properly
        $this->getCartData();
        // dd($this->getCartData());
        // Convert coupon collection to simple array
        try {
            $today = Carbon::today();
            $coupons = Coupon::whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)->where('is_visible', 1)
                ->get();

            $this->getCouponList = [];
            foreach ($coupons as $coupon) {
                $this->getCouponList[] = [
                    'coupon_id' => (int)$coupon->coupon_id,
                    'coupon_code' => (string)$coupon->coupon_code,
                    'coupon_description' => (string)($coupon->coupon_description ?? 'Discount Coupon'),
                    'amount' => (float)$coupon->amount,
                    'type' => (string)$coupon->type,
                    'cart_value' => (float)$coupon->cart_value,
                    'uses_restriction' => (int)$coupon->uses_restriction,
                ];
            }
        } catch (\Exception $e) {
            $this->getCouponList = [];
            \Log::error('Error loading coupons: ' . $e->getMessage());
        }

        if (session()->get('coupon_code')) {
            $this->coupon_code = (string)session()->get('coupon_code');
            $this->apply_coupon(session()->get('coupon_code'), 0);
        }
    }

    // ADDED: Method to properly reset address data
    public function resetAddressData(): void
    {
        $this->addressData = [
            'type' => '',
            'firstName' => '',
            'lastName' => '',
            'receiver_email' => auth()->user()->email ?? '',
            'receiver_phone' => auth()->user()->user_phone ?? '',
            'alternate_phone' => '',
            'house_no' => '',
            'society' => '',
            'city' => '',
            'state' => '',
            'pincode' => '',
            'landmark' => '',
        ];
    }

    public function calculateSubtotal(): void
    {
        $this->subTotal = 0.0;
        foreach ($this->cart_items_checkOut as $item) {
            $mrp = $item['variation']['mrp '] ?? $item['product']['base_mrp'];
            $price = (float)($item['variation']['price'] ?? $item['product']['base_price']);
            $quantity = (int)$item['quantity'];
            $this->subTotal += ($price == 0 ? $mrp : $price) * $quantity;
        }

        $this->shippingAmount = $this->getShippingAmount($this->subTotal);
        // dd($this->getShippingAmount($this->subTotal), $this->subTotal);

        // dd(
        //      $this->subTotal,
        //     Shipping::where('status', 1)
        //         ->where('minimum_cart_value', '<=', (int)  $this->subTotal)
        //         ->orderBy('minimum_cart_value', 'desc')
        //         ->get(['minimum_cart_value', 'shipping_charge'])
        // );
        $this->subTotal += $this->getShippingAmount($this->subTotal);
    }

    private function getShippingAmount($subTotal)
    {
        $subTotal = (int) floor($subTotal);

        return Shipping::where('status', 1)
            ->where('minimum_cart_value', '<=', $subTotal)
            ->orderBy('minimum_cart_value', 'desc')
            ->value('shipping_charge') ?? 0;
    }

    public function getCartData()
    {
        try {
            if (auth()->check()) {
                $cartItems = Cart::with(['product', 'variation.variation_attributes.attribute.attribute'])
                    ->where('user_id', Auth::id())
                    ->get();

                // Convert to simple arrays to avoid Livewire serialization issues
                $this->cart_items_checkOut = [];
                foreach ($cartItems as $item) {
                    $variationAttributes = [];

                    if ($item->variation && $item->variation->variation_attributes) {
                        foreach ($item->variation->variation_attributes as $attr) {
                            $variationAttributes[] = [
                                'attribute' => [
                                    'attribute' => [
                                        'attribute_name' => (string)($attr->attribute->attribute->name ?? ''),
                                        'attribute_value' => (string)($attr->attribute->value ?? ''),
                                    ]
                                ]
                            ];

                            // dd($attr->attribute->attribute);
                        }
                    }
                    $this->cart_items_checkOut[] = [
                        'variation_id' => (string)$item->variation_id,
                        'product_id' => (string)$item->product_id,
                        'quantity' => (string)$item->quantity,
                        'variation' => [
                            'id' => (int)$item->variation->id,
                            'price' => (float)$item->variation->price,
                            'mrp' => (float)$item->variation->mrp,
                            'stock' => (int)$item->variation->stock,
                            'variation_attributes' => $variationAttributes,
                        ],
                        'product' => $item->variation->product->toArray(),
                    ];
                    // dd($this->cart_items_checkOut, $item);

                }
                // dd($this->cart_items_checkOut);
                $this->cart_items_count_checkOut = (int)$cartItems->count();
                $this->cart_total_checkOut = (float)$cartItems->sum(fn($item) => $item->quantity * $item->variation->mrp);
            } else {
                $cart = session()->get('cart', []);
                $this->cart_items_checkOut = [];
                $cartTotal = 0.0;

                foreach ($cart as $key => $item) {
                    $variation = Variation::with(['product', 'variation_attributes.attribute.attribute'])
                        ->find($item['variation_id']);

                    if ($variation) {
                        $this->cart_items_checkOut[] = [
                            'variation_id' => (string)$variation->id,
                            'product_id' => (string)$variation->product->id,
                            'quantity' => (string)$item['quantity'],
                            'variation' => [
                                'id' => (int)$variation->id,
                                'price' => (float)$variation->price,
                                'mrp' => (float)$variation->mrp,
                                'stock' => (int)$variation->stock,
                            ],
                            'product' => $variation->product->toArray(),
                        ];
                        $cartTotal += (float)($item['quantity'] * $variation->mrp);
                    }
                }

                $this->cart_items_count_checkOut = (int)count($this->cart_items_checkOut);
                $this->cart_total_checkOut = $cartTotal;
            }

            // Calculate subtotal after loading cart
            $this->calculateSubtotal();
        } catch (\Exception $e) {
            \Log::error('Error loading cart data: ' . $e->getMessage());
            $this->cart_items_checkOut = [];
            $this->cart_items_count_checkOut = 0;
            $this->cart_total_checkOut = 0.0;
            $this->subTotal = 0.0;
        }
    }

    public function removeCoupon()
    {
        session()->forget("coupon_code");
        $this->coupon_code = '';
        $this->subTotal = $this->final_price + $this->discount;
        $this->discount = 0;

        $this->dispatch('toast', [
            'type' => 'success',
            'success' => true,
            'message' => "Coupon Removed",
        ]);
    }

    #[On('applyCoupon')]
    public function apply_couponJS(...$data)
    {
        $coupon_code_applied = $data[0] ?? null;
        $isApplied           = $data[1] ?? 0;

        try {
            // ---- 1. Resolve coupon code ----
            $coupon_code = trim($coupon_code_applied ?? $this->coupon_code);

            if (!$coupon_code) {
                $this->dispatch('couponApplied', [
                    'type'    => 'error',
                    'success' => false,
                    'message' => 'Please enter a coupon code.',
                ]);

                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Please enter a coupon code.',
                ]);
            }

            $coupon = Coupon::where('coupon_code', $coupon_code)->first();

            if (!$coupon) {
                $this->dispatch('couponApplied', [
                    'type'    => 'error',
                    'success' => false,
                    'message' => 'Invalid coupon code.',
                ]);

                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Invalid coupon code.',
                ]);
            }

            // ---- 2. Get cart for logged-in OR guest ----
            $cart_total   = 0;
            $total_saving = 0;

            if (Auth::check()) {
                // Logged-in user → DB cart
                $cart_items = Cart::with([
                    'product',
                    'variation',
                    'variation.variation_attributes.attribute.attribute'
                ])
                    ->where('user_id', Auth::id())
                    ->get();

                if ($cart_items->isEmpty()) {
                    $this->dispatch('couponApplied', [
                        'type'    => 'error',
                        'success' => false,
                        'message' => 'Your cart is empty.',
                    ]);

                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => 'Your cart is empty.',
                    ]);
                }

                $cart_total = round($cart_items->sum(function ($item) {

                    // dd($item->variation);
                    if ($item->variation->price == 0) {

                        return $item->variation->mrp * $item->quantity;
                    } else {

                        return $item->variation->price * $item->quantity;
                    }
                }), 2);

                $total_saving = $cart_items->sum(function ($item) {
                    return ($item->variation->mrp - $item->variation->price) * $item->quantity;
                });
            } else {
                // Guest user → Session cart
                // Assumed structure:
                // session('cart') = [
                //   ['price' => 100, 'mrp' => 150, 'quantity' => 2, ...],
                //   ...
                // ]
                $sessionCart = collect(session('cart', []));

                if ($sessionCart->isEmpty()) {
                    $this->dispatch('couponApplied', [
                        'type'    => 'error',
                        'success' => false,
                        'message' => 'Your cart is empty.',
                    ]);

                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => 'Your cart is empty.',
                    ]);
                }

                $cart_total =  round($sessionCart->sum(function ($item) {

                    // dd($item);
                    if ($item['price'] == 0 || $item['price'] ==   null) {

                        return ($item['mrp'] ?? 0) * ($item['quantity'] ?? 0);
                    } else {

                        return ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                    }
                }), 2);

                $total_saving = $sessionCart->sum(function ($item) {
                    $mrp      = $item['mrp'] ?? ($item['price'] ?? 0);
                    $price    = $item['price'] ?? 0;
                    $quantity = $item['quantity'] ?? 0;
                    return ($mrp - $price) * $quantity;
                });
            }

            // ---- 3. Minimum cart value check ----
            if ($cart_total < $coupon->cart_value) {
                $this->dispatch('couponApplied', [
                    'type'    => 'error',
                    'success' => false,
                    'message' => 'Minimum cart value should be ₹' . $coupon->cart_value,
                ]);

                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Minimum cart value should be ₹' . $coupon->cart_value,
                ]);
            }

            // ---- 4. Coupon usage checks (only for logged-in user) ----
            $coupon_uses = 0;

            if (Auth::check()) {
                $coupon_uses = DB::table('orders')
                    ->where('coupon_id', $coupon->coupon_id)
                    ->where('user_id', Auth::id())
                    ->where('order_status', '!=', 'Cancelled')
                    ->count();

                if ($coupon_uses > 1 && $coupon->typecoupon == 1) {
                    $this->dispatch('couponApplied', [
                        'type'    => 'error',
                        'success' => false,
                        'message' => "Maximum use limit reached for this coupon.",
                    ]);

                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => "Maximum use limit reached for this coupon.",
                    ]);
                }

                if ($coupon_uses >= $coupon->uses_restriction) {
                    $this->dispatch('couponApplied', [
                        'type'    => 'error',
                        'success' => false,
                        'message' => "Maximum use limit reached for this coupon.",
                    ]);

                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => "Maximum use limit reached for this coupon.",
                    ]);
                }
            }

            // ---- 5. Calculate discount ----
            if (strtolower($coupon->type) === 'percent') {
                $discount = ($cart_total * $coupon->amount) / 100;

                // Apply max discount if any
                if ($coupon->max_discount != 0 && $discount > $coupon->max_discount) {
                    $discount = $coupon->max_discount;
                }
            } else {
                $discount = $coupon->amount;
            }

            // Extra safety
            if ($cart_total < $discount) {
                $this->dispatch('couponApplied', [
                    'type'    => 'error',
                    'success' => false,
                    'message' => 'Cart value should not be less than coupon discount',
                ]);

                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Cart value should not be less than coupon discount',
                ]);
            }

            $discount     = min($discount, $cart_total);
            $final_price  = $cart_total - $discount;
            $total_saving = $total_saving + $discount;

            // ---- 6. Store coupon in session (works for guest + logged-in) ----
            session([
                'applied_coupon' => [
                    'discount'     => round($discount, 2),
                    'final_price'  => round($final_price, 2),
                    'coupon_code'  => $coupon->coupon_code,
                    'coupon_id'    => $coupon->coupon_id,
                    'total_saving' => round($total_saving, 2),
                ]
            ]);

            $this->discount     = round($discount, 2);
            $this->final_price  = round($final_price, 2);
            $this->coupon_code  = $coupon->coupon_code;
            $this->coupon_id    = $coupon->coupon_id;
            $this->total_saving = round($total_saving, 2);
            $this->subTotal     = $this->final_price;

            session()->put('coupon_code', $this->coupon_code);

            // Notify other Livewire components
            $this->dispatch('cart-updated');

            // ---- 7. Success events ----
            if ($isApplied == 1) {
                $this->dispatch('toast', [
                    'type'    => 'success',
                    'success' => true,
                    'message' => "Coupon Applied",
                ]);

                $this->dispatch('couponApplied', [
                    'success'     => true,
                    'coupon_code' => $this->coupon_code,
                    'message'     => 'Coupon applied successfully!',
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('couponApplied', [
                'success' => false,
                'message' => 'Failed to apply coupon: ' . $e->getMessage(),
            ]);

            $this->dispatch('toast', [
                'type'    => 'error',
                'success' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }


    public function apply_coupon($coupon_code_applied = null, $isApplied = 0)
    {
        try {
            // 1. Resolve coupon
            $coupon_code = trim($coupon_code_applied ?? $this->coupon_code);

            if (!$coupon_code) {
                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Please enter a coupon code.',
                ]);
            }

            $coupon = Coupon::where('coupon_code', $coupon_code)->first();

            if (!$coupon) {
                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Invalid coupon code.',
                ]);
            }

            // 2. Get cart totals (DB for logged-in, session for guest)
            $cart_total   = 0;
            $total_saving = 0;

            if (Auth::check()) {
                // Logged-in user → DB cart
                $cart_items = Cart::with(['product', 'variation', 'variation.variation_attributes.attribute.attribute'])
                    ->where('user_id', Auth::id())
                    ->get();

                if ($cart_items->isEmpty()) {
                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => 'Your cart is empty.',
                    ]);
                }

                // $cart_total = round($cart_items->sum(function ($item) {
                //     return $item->variation->price * $item->quantity;
                // }), 2);

                $cart_total = round($cart_items->sum(function ($item) {

                    // dd($item->variation);
                    if ($item->variation->price == 0) {

                        return $item->variation->mrp * $item->quantity;
                    } else {

                        return $item->variation->price * $item->quantity;
                    }
                }), 2);

                $total_saving = round($cart_items->sum(function ($item) {
                    return ($item->variation->mrp - $item->variation->price) * $item->quantity;
                }), 2);
            } else {
                // Guest user → session cart
                // Expected structure:
                // session('cart') = [
                //   ['price' => 100, 'mrp' => 150, 'quantity' => 2, ...],
                //   ...
                // ]
                $sessionCart = collect(session('cart', []));

                if ($sessionCart->isEmpty()) {
                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => 'Your cart is empty.',
                    ]);
                }


                $cart_total =  round($sessionCart->sum(function ($item) {

                    // dd($item);
                    if ($item['price'] == 0 || $item['price'] ==   null) {

                        return ($item['product']['base_mrp'] ?? 0) * ($item['quantity'] ?? 0);
                    } else {

                        return ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                    }
                }), 2);

                $total_saving = round($sessionCart->sum(function ($item) {
                    $mrp      = $item['mrp'] ?? ($item['price'] ?? 0);
                    $price    = $item['price'] ?? 0;
                    $quantity = $item['quantity'] ?? 0;
                    return ($mrp - $price) * $quantity;
                }), 2);
            }

            // 3. Minimum cart value validation
            if ($cart_total < $coupon->cart_value) {
                session()->forget("coupon_code");
                $this->coupon_code = '';
                $this->discount    = 0;
                $this->subTotal    = $cart_total;

                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Minimum cart value should be ₹' . $coupon->cart_value,
                ]);
            }

            // 4. Check coupon usage (only for logged-in users)
            $coupon_uses = 0;

            if (Auth::check()) {
                $coupon_uses = DB::table('orders')
                    ->where('coupon_id', $coupon->coupon_id)
                    ->where('user_id', Auth::id())
                    ->where('order_status', '!=', 'Cancelled')
                    ->count();

                if ($coupon_uses > 1 && $coupon->typecoupon == 1) {
                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => "Maximum use limit reached for this coupon.",
                    ]);
                }

                if ($coupon_uses >= $coupon->uses_restriction) {
                    return $this->dispatch('toast', [
                        'type'    => 'error',
                        'success' => true,
                        'message' => "Maximum use limit reached for this coupon.",
                    ]);
                }
            }

            // 5. Calculate discount
            if (strtolower($coupon->type) === 'percent') {
                $discount = round(($cart_total * ($coupon->amount / 100)), 2);

                if ($coupon->max_discount != 0 && $discount > $coupon->max_discount) {
                    $discount = $coupon->max_discount;
                }
            } else {
                $discount = $coupon->amount;
            }

            if ($cart_total < $discount) {
                session()->forget("coupon_code");
                $this->coupon_code = '';
                $this->discount    = 0;
                $this->subTotal    = $cart_total;

                return $this->dispatch('toast', [
                    'type'    => 'error',
                    'success' => true,
                    'message' => 'Cart value should not be less than coupon discount.',
                ]);
            }
            $discount     = min($discount, $cart_total); // Can't exceed cart value
            $final_price  = (($cart_total - $discount) + $this->getShippingAmount($cart_total - $discount));;
            $total_saving = $total_saving + $discount;
            $this->shippingAmount = $this->getShippingAmount($cart_total - $discount);




            // 6. Store in session
            session([
                'applied_coupon' => [
                    'discount'     => $discount,
                    'final_price'  => $final_price,
                    'coupon_code'  => $coupon->coupon_code,
                    'coupon_id'    => $coupon->coupon_id,
                    'total_saving' => $total_saving,
                ]
            ]);

            // 7. Update component state
            $this->discount     = $discount;
            $this->final_price  = $final_price;
            $this->coupon_code  = $coupon->coupon_code;
            $this->coupon_id    = $coupon->coupon_id;
            $this->total_saving = $total_saving;
            $this->subTotal     = $final_price;

            session()->put('coupon_code', $this->coupon_code);
            $this->dispatch('cart-updated');

            if ($isApplied == 1) {
                $this->dispatch('toast', [
                    'type'    => 'success',
                    'success' => true,
                    'message' => "Coupon Applied Successfully.",
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


    public function updateAddress()
    {
        if ($this->address_id == '' || $this->address_id == 0) {
            $this->dispatch('toast', [
                'type' => 'error',
                'success' => false,
                'message' => "No address selected for update",
            ]);
            return false;
        }

        if ($this->addressForm) {
            try {
                $this->validate();
            } catch (\Illuminate\Validation\ValidationException $e) {
                $errors = $e->errors();

                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {

                        session()->flash('error_' . $field, $fieldErrors[0]); // Get first error for each field
                        // $this->dispatch('toast', [
                        //     'type' => 'error',
                        //     'success' => false,
                        //     'field' => $field,
                        //     'message' => $error,
                        //     'delay' => array_search($field, array_keys($errors)) * 500
                        // ]);
                    }
                }
                return false;
            }

            DB::beginTransaction();

            try {
                // Find the existing address
                $address = Address::where('uuid', $this->address_id)
                    ->where('user_id', Auth::id())
                    ->first();

                if (!$address) {
                    $this->dispatch('toast', [
                        'type' => 'error',
                        'success' => false,
                        'message' => "Address not found or unauthorized access",
                    ]);
                    return false;
                }

                // Create or find city
                $city = City::firstOrCreate([
                    'city_name' => $this->addressData['city'],
                ]);

                // Prepare update data
                $updateData = [
                    'type' => $this->addressData['type'],
                    'receiver_name' => $this->addressData['firstName'] . " " . $this->addressData['lastName'],
                    'receiver_phone' => $this->addressData['receiver_phone'],
                    'alternate_phone' => $this->addressData['alternate_phone'],
                    'city' => $this->addressData['city'],
                    'city_id' => $city->city_id ?? $city->id,
                    'house_no' => $this->addressData['house_no'],
                    'society' => $this->addressData['society'],
                    'landmark' => $this->addressData['landmark'] ?? '',
                    'state' => $this->addressData['state'],
                    'pincode' => $this->addressData['pincode'],
                    'receiver_email' => $this->addressData['receiver_email'],
                    'updated_at' => Carbon::now(),
                ];

                // Update the address
                $address->update($updateData);

                $this->addressForm = false;

                DB::commit();

                $this->dispatch('toast', [
                    'type' => 'success',
                    'success' => true,
                    'message' => "Address Updated Successfully",
                ]);

                return true;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => false,
                    'message' => 'Failed to update address: ' . $e->getMessage(),
                ]);
                return false;
            }
        } else {
            $this->dispatch('toast', [
                'type' => 'error',
                'success' => false,
                'message' => "Address form is not active",
            ]);
            return false;
        }
    }

    public function addAddress()
    {
        // dd(session()->all());
        // if ($this->addressForm) {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();

            // dd($errors);
            foreach ($errors as $field => $fieldErrors) {
                foreach ($fieldErrors as $error) {


                    session()->flash('error_' . $field, $fieldErrors[0]); // Get first error for each field
                    // $this->dispatch('toast', [
                    //     'type' => 'error',
                    //     'success' => false,
                    //     'field' => $field,
                    //     'message' => $error,
                    //     'delay' => array_search($field, array_keys($errors)) * 500
                    // ]);
                }
            }
            return false;
        }

        DB::beginTransaction();

        if (!auth()->user()) {

            return   $this->dispatch('toast', [
                'type' => 'error',
                'success' => true,
                'message' => "Please Login",
            ]);
            $storedUser = User::where('email', $this->addressData['receiver_email'])->orWhere("user_phone", $this->addressData['receiver_phone'])->first();
            // dd($storedUser);
            // if ($storedUser) {

            //     $this->guestUser = $storedUser;
            // } else {

            $this->guestUser = User::create([
                'name' => $this->addressData['firstName'] . " " . $this->addressData['lastName'],
                'email' => $this->addressData['receiver_email'],
                'user_phone' => $this->addressData['receiver_phone'],
                'reg_date' => Carbon::now(),
                "status" => 1,
                "is_guest" => 1
            ]);
            // }
            session()->put('guestUser',  $this->guestUser);
        }



        try {
            $city = City::firstOrCreate([
                'city_name' => $this->addressData['city'],
            ]);

            $addressData = [
                'type' => $this->addressData['type'],
                'user_id' => Auth::id() ??  $this->guestUser->id,
                'receiver_name' => $this->addressData['firstName'] . " " . $this->addressData['lastName'],
                'receiver_phone' => $this->addressData['receiver_phone'],
                'alternate_phone' => $this->addressData['alternate_phone'],
                'city' => $this->addressData['city'],
                'city_id' => $city->city_id ?? $city->id,
                'house_no' => $this->addressData['house_no'],
                'society' => $this->addressData['society'],
                'landmark' => $this->addressData['landmark'] ?? '',
                'state' => $this->addressData['state'],
                'pincode' => $this->addressData['pincode'],
                'lat' => 0,
                'lng' => 0,
                'select_status' => 1,
                'receiver_email' => $this->addressData['receiver_email'],
                'updated_at' => Carbon::now(),
            ];

            $address = Address::create(array_merge($addressData, [
                'added_at' => Carbon::now(),
            ]));

            $this->address_id = $address->uuid;
            $this->addressForm = false;



            DB::commit();

            $this->dispatch('toast', [
                'type' => 'success',
                'success' => true,
                'message' => "Address Created Successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toast', [
                'type' => 'error',
                'success' => false,
                'message' => 'Failed to create address: ' . $e->getMessage(),
            ]);
            return false;
        }
        // } else {
        //     $this->dispatch('toast', [
        //         'type' => 'error',
        //         'success' => false,
        //         'message' => "Please select or add an address",
        //     ]);
        //     return false;
        // }
    }

    public function storeOrder()
    {

        $loggedInUser = auth()->user();
        if (!$loggedInUser) {
            if ($this->address_id == null || $this->address_id == "") {
                // dd($this->address_id);

                $this->addAddress();
            }
            // dd($this->guestUser, session('guestUser'),$this->addAddress());
            // dd();
            $sessionCart = session()->get('cart', []);
            $userId = $this->guestUser?->id ?? session('guestUser')->id;
            // session()->forget('cart');
            // dd(session()->get('cart', []) , $sessionWishlist);
            // dd( $sessionCart);
            foreach ($sessionCart as $key => $item) {
                $dbCartItem = Cart::where('product_id', $item['product_id'])
                    ->where('variation_id', $item['variation_id'])
                    ->where('user_id', $userId)
                    ->first();

                if (!$dbCartItem) {

                    Cart::create([
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'],
                        'user_id' => $userId,
                        'quantity' => $item['quantity'],
                    ]);
                }
            }
        }
        // dd("dasdasd");
        try {



            $couponCodeSession = Session()->get('coupon_code');
            $getCoupon = Coupon::where('coupon_code', $couponCodeSession)->first();

            $getAddress = Address::where('user_id', $loggedInUser->id ?? session('guestUser')?->id)->where('is_deleted', 0)->count();


            if ($this->payment_option == "") {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => false,
                    'message' => "Select Payment Option",
                ]);
                return false;
            }

            // dd($getAddress);
            if ($getAddress == 0) {

                $this->addressForm = true;
            }
            DB::beginTransaction();

            $cart_items = Cart::with(['product', 'variation'])
                ->where('user_id', Auth::id() ?? session('guestUser')?->id)
                ->get();

            if ($cart_items->isEmpty()) {
                DB::rollBack();
                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => false,
                    'message' => "Your Cart Is Empty",
                ]);
                return false;
            }

            if ($this->address_id == "") {

                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => false,
                    'message' => "Please Select an Address",
                ]);
                return false;
            }

            $address = Address::where('uuid', $this->address_id)->first();

            // dd($this->address_id);
            if (!$address) {
                DB::rollBack();
                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => false,
                    'message' => "Selected address not found",
                ]);
                return false;
            }

            foreach ($cart_items as $item) {
                $variation = Variation::where('id', $item->variation_id)->first();
                if (!$variation) {
                    DB::rollBack();
                    $this->dispatch('toast', [
                        'type' => 'error',
                        'success' => false,
                        'message' => "Product variation not found for {$item->product->product_name}",
                    ]);
                    return false;
                }
                // dd( $item);
                if ($variation->stock < $item->quantity) {
                    DB::rollBack();
                    $this->dispatch('toast', [
                        'type' => 'error',
                        'success' => false,
                        'message' => " {$item->quantit} Not enough stock for {$item->product->product_name}. Available: {$variation->stock} ",
                    ]);
                    return false;
                }
            }

            // dd($this->order_condition);
            // if (!$this->order_condition) {

            //     $this->dispatch('toast', [
            //         'type' => 'error',
            //         'success' => false,
            //         'message' => "Please check terms & conditions",
            //     ]);
            //     return false;
            // }

            $delivery_charge = 0;

            $total_price = $cart_items->sum(function ($item) {
                if ($item->variation->price == 0) {

                    return ($item->variation->mrp ?? $item->product->base_mrp) * $item->quantity;
                } else {

                    return ($item->variation->price ?? $item->product->base_price) * $item->quantity;
                }
            });

            // dd($total_price );
            $total_products_mrp = $cart_items->sum(function ($item) {
                if ($item->variation->price == 0) {

                    return ($item->variation->mrp ?? $item->product->base_mrp) * $item->quantity;
                } else {

                    return ($item->variation->mrp ?? $item->product->base_mrp) * $item->quantity;
                }
            });
            $price_without_delivery = $total_price - ($this->discount ?? 0);

            // if ($total_price < (int)$this->getShippingCharge->minimum_cart_value) {
            $total_price  += $this->getShippingAmount($total_price);
            $delivery_charge = $this->getShippingAmount($total_price);
            // }
            $cart_id = strtoupper(substr(md5(microtime()), 0, 6));


            $total_price =  round($total_price - (round($this->discount) ?? 0));

            $order = Orders::create([
                'user_id' => Auth::id() ?? session('guestUser')?->id,
                'store_id' => 1,
                'address_id' => $address->address_id,
                'cart_id' => $cart_id,
                'total_price' => $total_price,
                'price_without_delivery' => $price_without_delivery,
                'total_products_mrp' => $total_products_mrp,
                'payment_method' => $this->payment_option,
                'payment_status' => 'Pending',
                'rem_price' => $price_without_delivery,
                'order_date' => Carbon::now(),
                'order_time' => now()->format('H:i:s'),
                // 'delivery_date' => Carbon::now()->addDays(2),
                'delivery_charge' => $delivery_charge,
                'coupon_id' => $getCoupon->coupon_id ?? 0,
                'time_slot' => Carbon::now()->addDays(2),
                'order_status' => 'Pending',
                'coupon_discount' => $this->discount ?? 0,
                'note' => NULL,
            ]);

            if (!$order) {
                DB::rollBack();
                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => false,
                    'message' => "Failed to create order",
                ]);
                return false;
            }

            foreach ($cart_items as $item) {

                $unitPrice =
                    ($item->variation && $item->variation->price > 0)
                    ? $item->variation->price
                    : (
                        ($item->product->base_price > 0)
                        ? $item->product->base_price
                        : (
                            ($item->variation && $item->variation->mrp > 0)
                            ? $item->variation->mrp
                            : $item->product->base_mrp
                        )
                    );

                $unitMrp =
                    ($item->variation && $item->variation->mrp > 0)
                    ? $item->variation->mrp
                    : $item->product->base_mrp;

                StoreOrders::create([
                    'product_name'   => $item->product->product_name,
                    'varient_image'  => $item->product->product_image,
                    'quantity'       => $item->quantity,
                    'qty'            => $item->quantity,
                    'varient_id'     => $item->variation_id,
                    'price'          => $unitPrice * $item->quantity,
                    'total_mrp'      => $unitMrp * $item->quantity,
                    'order_cart_id'  => $cart_id,
                    'order_date'     => Carbon::now(),
                    'store_id'       => 1,
                    'description'    => $item->product->description,
                ]);

                // Variation::where('id', $item->variation_id)->decrement('stock', $item->quantity);
            }

            // Cart::where('user_id', Auth::id())->delete();
            session()->forget(['cart', 'addressView', 'coupon_code']);



            DB::commit();


            if ($this->payment_option === 'COD') {
                try {
                    // $checkoutComponent = new \App\Http\Livewire\Checkout();
                    // $emailResult = $checkoutComponent->sendOrderSuccessEmail($order->order_id);
                    // Log::info('Email notification result:', $emailResult);

                    SendOrderNotificationJob::dispatch($order->order_id);
                    OrderPlacedEmailJob::dispatch($order);
                } catch (Exception $e) {
                    Log::error('Failed to send email notification:', [
                        'order_id' => $order->order_id,
                        'error' => $e->getMessage()
                    ]);
                }

                $cart_data = Cart::where('user_id', Auth::id() ?? session('guestUser')?->id)->get();
                foreach ($cart_data as $item) {
                    // $storeOrder = StoreOrders::where('order_cart_id', $item->cart_id)->first();
                    // // dd($payload, "CUSTOMER_CANCELLED");
                    // $productVarientId = $storeOrder->varient_id;
                    // $productOrderQty = $storeOrder->quantity;

                    Variation::where('id', $item->variation_id)->decrement('stock', $item->quantity);
                }
                Cart::where('user_id', Auth::id() ?? session('guestUser')?->id)->delete();


                Log::info('Payment Success Processed:', [
                    'order_id' => $order->order_id,
                    'amount' =>   $total_price
                ]);
                $encryptedId = Crypt::encrypt($order->order_id);

                $this->coupon_code = "";
                $this->discount = 0;

                // Reset cart data after successful order
                $this->cart_items_checkOut = [];
                $this->cart_items_count_checkOut = 0;
                $this->cart_total_checkOut = 0;
                $this->subTotal = 0;

                return redirect()->route('customer.orders.order_success', ['orderId' => $encryptedId]);
            }


            // dd('asdasdasdasd',env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
            // Razorpay Handling
            $razorpayOrder = (new \Razorpay\Api\Api(config('services.razorpay.key'), config('services.razorpay.secret')))->order->create([
                'receipt'  =>  $cart_id,
                'amount'   => (int)round($total_price * 100), // amount in paise
                'currency' => 'INR',
            ]);


            Log::info('razorpay order response', ['result' => $razorpayOrder]);

            // dd($razorpayOrder);
            return redirect()->route('payment', ['cart_id' => $cart_id, 'order_id' => $razorpayOrder['id']]);
            // $payGlocalService = new PayGlocalService();

            // $response = $payGlocalService->initiatePayment($cart_id);

            $this->discount = 0;
            // dd( $response);
            // Reset cart data after successful order
            $this->cart_items_checkOut = [];
            $this->cart_items_count_checkOut = 0;
            $this->cart_total_checkOut = 0;
            $this->subTotal = 0;


            return redirect()->away($response['redirectUrl']);

            // dd($response);
            // $this->dispatch('toast', [
            //     'type' => 'success',
            //     'success' => true,
            //     'message' => "Order Created Successfully",
            // ]);

            // $encryptedId = Crypt::encrypt($order->order_id);
            // $this->successOrder($cart_id);


            // $this->sendOrderSuccessEmail($order->order_id);
            // dd($response);


            // return redirect()->route('customer.orders.order_success', ['orderId' => $encryptedId]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::info('exception occured', ['message' => $e->getMessage()]);
            $this->dispatch('toast', [
                'type' => 'error',
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]);

            return false;
        }
    }

    // public function successOrder($cart_id)
    // {
    //     $order = Orders::with(['orderItems.variation.variation_attributes.attribute.attribute', 'address'])
    //         ->where('user_id', Auth::id())
    //         ->where('cart_id', $cart_id)
    //         ->firstOrFail();

    //     $pdf = Pdf::loadView('frontend.order.invoice', ['order' => $order]);
    //     $pdf->render();
    //     $pdfContent = $pdf->output();

    //     $directory = 'orders';
    //     $filename = 'order-generate-' . $order->cart_id . '.pdf';
    //     $path = $directory . '/' . $filename;

    //     if (!Storage::disk('public')->exists($directory)) {
    //         Storage::disk('public')->makeDirectory($directory);
    //     }

    //     Storage::disk('public')->put($path, $pdfContent);
    //     SendOrderPlacedMailEvent::dispatch($order);
    // }


    public function successOrder($cart_id)
    {
        $order = Orders::with([
            'orderItems.variation.variation_attributes.attribute.attribute',
            'orderItems.variation.product',
            'address',
            'coupon'
        ])
            ->where('user_id', Auth::id())
            ->where('cart_id', $cart_id)
            ->firstOrFail();

        // Convert logo to base64
        $logoPath = public_path('assets/images/demos/demo-9/SOAP.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        // Convert product images to base64
        foreach ($order->orderItems as $item) {
            if ($item->varient_image) {
                $imagePath = storage_path('app/public/' . $item->varient_image);
                if (file_exists($imagePath)) {
                    $imageContent = file_get_contents($imagePath);
                    $item->varient_image_base64 = base64_encode($imageContent);

                    // Optional: Determine image type for better data URI
                    $imageInfo = getimagesize($imagePath);
                    if ($imageInfo) {
                        $mimeType = $imageInfo['mime'];
                        $item->image_mime_type = $mimeType;
                    } else {
                        $item->image_mime_type = 'image/jpeg'; // Default fallback
                    }
                }
            }
        }

        //    $this->sendOrderSuccessSMS($order->address->receiver_phone,$order->address->receiver_name, $order->cart_id );
        //     SendOrderPlacedMailEvent::dispatch($order);

        // try {
        //     // Generate PDF with base64 images
        //     $pdf = Pdf::loadView('frontend.order.invoice', [
        //         'order' => $order,
        //         'logoBase64' => $logoBase64
        //     ]);

        //     // Configure PDF options for better rendering
        //     $pdf->setPaper('a4', 'portrait')
        //         ->setOption('margin-top', 10)
        //         ->setOption('margin-right', 10)
        //         ->setOption('margin-bottom', 10)
        //         ->setOption('margin-left', 10)
        //         ->setOption('enable-local-file-access', true)
        //         ->setOption('images', true)
        //         ->setOption('enable-javascript', false)
        //         ->setOption('javascript-delay', 1000)
        //         ->setOption('no-stop-slow-scripts', true)
        //         ->setOption('lowquality', false)
        //         ->setOption('print-media-type', true);

        //     $pdf->render();
        //     $pdfContent = $pdf->output();

        //     // Store PDF
        //     $directory = 'orders';
        //     $filename = 'order-generate-' . $order->cart_id . '.pdf';
        //     $path = $directory . '/' . $filename;

        //     if (!Storage::disk('public')->exists($directory)) {
        //         Storage::disk('public')->makeDirectory($directory);
        //     }

        //     Storage::disk('public')->put($path, $pdfContent);

        //     // Add PDF path to order for email attachment
        //     $order->pdf_path = $path;

        //     // Dispatch email event
        //     $this->sendOrderSuccessSMS($order->address->receiver_phone,$order->address->receiver_name, $order->cart_id );
        //     SendOrderPlacedMailEvent::dispatch($order);

        //     return [
        //         'success' => true,
        //         'pdf_path' => Storage::disk('public')->url($path),
        //         'message' => 'Order PDF generated successfully'
        //     ];

        // } catch (\Exception $e) {
        //     // \Log::error('PDF Generation Error: ' . $e->getMessage());

        //     // Still send email even if PDF generation fails
        //     SendOrderPlacedMailEvent::dispatch($order);

        //     return [
        //         'success' => false,
        //         'error' => $e->getMessage(),
        //         'message' => 'PDF generation failed but order email sent'
        //     ];
        // }
    }

    // FIXED: Properly initialize address data when showing existing address
    public function showAddress($addressId)
    {
        $getAddress = Address::where('uuid', $addressId)->first();

        if (!$getAddress) {
            $this->dispatch('toast', [
                'type' => 'error',
                'success' => false,
                'message' => 'Address not found',
            ]);
            return;
        }

        // Set the address ID for selection
        $this->address_id = $addressId;

        // Parse the name
        $name = explode(' ', $getAddress->receiver_name, 2);

        // FIXED: Properly reset and set address data
        $this->resetAddressData();
        $this->addressData = [
            'type' => (string)($getAddress->type ?? ''),
            'firstName' => (string)($name[0] ?? ''),
            'lastName' => (string)($name[1] ?? ''),
            'receiver_phone' => (string)($getAddress->receiver_phone ?? ''),
            'alternate_phone' => (string)($getAddress->alternate_phone ?? ''),
            'city' => (string)($getAddress->city ?? ''),
            'house_no' => (string)($getAddress->house_no ?? ''),
            'society' => (string)($getAddress->society ?? ''),
            'landmark' => (string)($getAddress->landmark ?? ''),
            'state' => (string)($getAddress->state ?? ''),
            'pincode' => (string)($getAddress->pincode ?? ''),
            'receiver_email' => (string)($getAddress->receiver_email ?? ''),
        ];

        $this->addressForm = true;
    }

    // FIXED: Properly reset address form
    public function showAddressForm(): void
    {
        $this->addressForm = true;
        $this->address_id = ''; // Clear address ID
        $this->resetAddressData(); // Use the reset method
    }

    public function clearAddress(): void
    {
        $this->address_id = "";
        $this->addressForm = false; // Also hide the form
        $this->resetAddressData(); // Reset address data
    }

    public function removeProduct($variation_id, $product_id)
    {
        try {
            if (auth()->check()) {
                DB::beginTransaction();

                $userId = auth()->id();

                Cart::where('product_id', $product_id)
                    ->where('variation_id', $variation_id)
                    ->where('user_id', $userId)
                    ->delete();

                DB::commit();
            } else {
                $cart = session()->get('cart', []);
                $key = $product_id . '-' . $variation_id;

                if (isset($cart[$key])) {
                    unset($cart[$key]);
                    session()->put('cart', $cart);
                }
            }

            usleep(300000); // 300ms delay
            $this->getCartData(); // Reload cart data

            $this->dispatch('toast', [
                'type' => 'success',
                'success' => true,
                'message' => 'Product removed from cart',
            ]);

            // Don't redirect, just update the component
            // return redirect(route('checkout'));

        } catch (\Exception $e) {
            if (auth()->check()) {
                DB::rollBack();
            }

            $this->dispatch('toast', [
                'type' => 'error',
                'success' => false,
                'message' => 'Failed to remove product: ' . $e->getMessage(),
            ]);
        }
    }

    private function sendOrderSuccessSMS($mobile, $name, $orderNumber = null, $amount = null)
    {
        try {
            // Construct the message based on whether order number is provided
            if ($orderNumber) {
                $message = "Thank you for your order! Order {$orderNumber} for Rs.{$amount} has been placed successfully. We will notify you once confirmed. - ZAKHEC";

                $admin_message = "🔔 Order Alert: #{$orderNumber} placed by {$name} for ₹{$amount}. Login to process - ZAKH";


                $whatsappResponse =   Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6Inpha2hfYm90IiwiZW1haWwiOiJ6YWtoQG1haWxpbmF0b3IuY29tIiwidGltZXN0YW1wIjoiMjAyNS0xMC0zMVQwNjozNTozMS4zNTJaIiwiY2hhbm5lbCI6IndoYXRzYXBwIiwiaWF0IjoxNzYxODkyNTMxfQ.gmwdMS60eECJ9Mdx3VUq9KvMMnBZXQET2U6PVpfDHeI')
                    ->post('https://api.helloyubo.com/v3/whatsapp/notification', [
                        // "clientId" => "zakh_bot",
                        // "channel" => "whatsapp",
                        // "send_to" => "91$mobile",
                        // "templateName" => "order_placed",
                        // "parameters" => ["#$orderNumber", $amount],
                        // "msg_type" => "TEXT",
                        // "header" => "",
                        // "footer" => "-ZAKHEC",
                        // "buttonUrlParam" => null,
                        // "button" => "false",
                        // "media_url" => "",
                        // "lang" => "en",
                        // "msg" => null,
                        // "userName" => null,
                        // "parametersArrayObj" => null,
                        // "headerParam" => null


                        "clientId" => "zakh_bot",
                        "channel" => "whatsapp",
                        "send_to" => "91$mobile",
                        "templateName" => "order_placed",
                        "parameters" =>  ["#$orderNumber", $amount],
                        "msg_type" => "TEXT",
                        "header" => "",
                        "footer" => "-ZAKHEC",
                        "buttonUrlParam" => null,
                        "button" => "false",
                        "media_url" => "",
                        "lang" => "en",
                        "msg" => null,
                        "userName" => null,
                        "parametersArrayObj" => null,
                        "headerParam" => null
                    ]);

                Log::info('SMS sent successfully', [
                    'whatsappResponse' => $whatsappResponse->body(),
                ]);

                Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6Inpha2hfYm90IiwiZW1haWwiOiJ6YWtoQG1haWxpbmF0b3IuY29tIiwidGltZXN0YW1wIjoiMjAyNS0xMC0zMVQwNjozNTozMS4zNTJaIiwiY2hhbm5lbCI6IndoYXRzYXBwIiwiaWF0IjoxNzYxODkyNTMxfQ.gmwdMS60eECJ9Mdx3VUq9KvMMnBZXQET2U6PVpfDHeI')
                    ->post('https://api.helloyubo.com/v3/whatsapp/notification', [
                        "clientId" => "zakh_bot",
                        "channel" => "whatsapp",
                        "send_to" => "917979068408",
                        "templateName" => "order_alert",
                        "parameters" => ["#$orderNumber", "$name", $amount],
                        "msg_type" => "TEXT",
                        "header" => "",
                        "footer" => "",
                        "buttonUrlParam" => null,
                        "button" => "false",
                        "media_url" => "",
                        "lang" => "en",
                        "msg" => null,
                        "userName" => null,
                        "parametersArrayObj" => null,
                        "headerParam" => null
                    ]);
            } else {
                $message = "Welcome to ZAKH, {$name}! Your account has been created successfully. Start exploring and enjoy your shopping experience with us! -ZAKHEC";


                Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6Inpha2hfYm90IiwiZW1haWwiOiJ6YWtoQG1haWxpbmF0b3IuY29tIiwidGltZXN0YW1wIjoiMjAyNS0xMC0zMVQwNjozNTozMS4zNTJaIiwiY2hhbm5lbCI6IndoYXRzYXBwIiwiaWF0IjoxNzYxODkyNTMxfQ.gmwdMS60eECJ9Mdx3VUq9KvMMnBZXQET2U6PVpfDHeI')
                    ->post('https://api.helloyubo.com/v3/whatsapp/notification', [
                        "clientId" => "zakh_bot",
                        "channel" => "whatsapp",
                        "send_to" => "91$mobile",
                        "templateName" => "account_create",
                        "parameters" => ["$name"],
                        "msg_type" => "TEXT",
                        "header" => "",
                        "footer" => "-ZAKHEC",
                        "buttonUrlParam" => null,
                        "button" => "false",
                        "media_url" => "",
                        "lang" => "en",
                        "msg" => null,
                        "userName" => null,
                        "parametersArrayObj" => null,
                        "headerParam" => null
                    ]);
            }


            $smsParams = [
                'user' => 'zakh',
                'password' => 'zakh2025', // Consider moving to .env file
                'senderid' => 'ZAKHEC',
                'channel' => 'Trans',
                'DCS' => 0,
                'flashsms' => 0,
                'route ' => 42,
                'number' => '91' . $mobile, // Changed from 'mobile' to 'number', removed '+'
                'text' => $message,
                'Peid' => '1701175853936286545', // Changed from 'entityid' to 'Peid'
                'DLTTemplateId' => '1707175991916328698' // Changed from 'tempid' to 'DLTTemplateId'
            ];
            $smsAdminParams = [
                'user' => 'zakh',
                'password' => 'zakh2025', // Consider moving to .env file
                'senderid' => 'ZAKHEC',
                'channel' => 'Trans',
                'DCS' => 0,
                'route ' => 42,
                'flashsms' => 0,
                'number' => '917979068408', // Changed from 'mobile' to 'number', removed '+'
                'text' => $admin_message,
                'Peid' => '1701175853936286545', // Changed from 'entityid' to 'Peid'
                'DLTTemplateId' => '1707176035173592997' // Changed from 'tempid' to 'DLTTemplateId'
            ];
            $smsTestParams = [
                'user' => 'zakh',
                'password' => 'zakh2025', // Consider moving to .env file
                'senderid' => 'ZAKHEC',
                'channel' => 'Trans',
                'DCS' => 0,
                'route ' => 42,
                'flashsms' => 0,
                'number' => '917979068408', // Changed from 'mobile' to 'number', removed '+'
                'text' => $message,
                'Peid' => '1701175853936286545', // Changed from 'entityid' to 'Peid'
                'DLTTemplateId' => '1707175992012497279' // Changed from 'tempid' to 'DLTTemplateId'
            ];

            // Updated API endpoint
            $response = Http::get('http://www.admagister.net/api/mt/SendSMS', $smsParams);
            $AdminResponse = Http::get('http://www.admagister.net/api/mt/SendSMS', $smsAdminParams);
            $TestResponse = Http::get('http://www.admagister.net/api/mt/SendSMS', $smsTestParams);



            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'mobile' => $mobile,
                    'order_number' => $orderNumber,
                    'response' => $response->body(),
                    'admin_response' => $AdminResponse->body(),
                    'test_response' => $TestResponse->body()
                ]);
                return true;
            } else {
                Log::error('SMS sending failed', [
                    'mobile' => $mobile,
                    'order_number' => $orderNumber,
                    'response' => $response->body(),
                    'admin_response' => $AdminResponse->body(),
                    'test_response' => $TestResponse->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('SMS API Error: ' . $e->getMessage(), [
                'mobile' => $mobile,
                'order_number' => $orderNumber
            ]);
            return false;
        }
    }

    /**
     * Send order success email notification
     * Call this method when payment is successful from PayGlocal callback
     */
    public function sendOrderSuccessEmail($orderId)
    {
        try {
            // Find the order with all necessary relationships
            $order = Orders::with([
                'orderItems.variation.variation_attributes.attribute.attribute',
                'orderItems.variation.product',
                'address',
                'coupon',
                'user'
            ])->findOrFail($orderId);

            // Create payment record if needed (you might already have this from PayGlocal callback)
            $payment = OrderPayments::where('order_id', $orderId)->latest()->first();

            if (
                strtolower($order->payment_method) == 'cod' ||
                (strtolower($order->payment_method) != 'cod' && (strtolower($order->payment_status)  == 'paid'))
            ) {

                // Send the order success email

                // dd($order->payment_status , $order->payment_method);
                //   $this->sendOrderSuccessSMS($order->address->receiver_phone, $order->address->receiver_name, $order->cart_id, $order->total_price);
            }
            SendOrderPlacedMailEvent::dispatch($order);


            // Also send admin notification if needed
            // $adminEmails = config('mail.admin_emails', ['admin@yourstore.com']);
            // foreach ($adminEmails as $adminEmail) {
            //     \Mail::to($adminEmail)->send(new \App\Mail\AdminOrderNotificationMail($order, $payment));
            // }

            Log::info('Order success email sent successfully', [
                'order_id' => $orderId,
                'user_email' => $order->user->email,
                'payment_id' => $payment->id ?? null
            ]);

            return [
                'success' => true,
                'message' => 'Order success email sent successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send order success email', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle successful payment notification from PayGlocal
     * This method can be called from your PayGlocal webhook/callback
     */
    public function handlePaymentSuccess($orderId, $paymentData = [])
    {
        try {
            // Update order status to paid
            $order = Orders::findOrFail($orderId);
            $order->update([
                'payment_status' => 'Paid',
                'order_status' => 'Confirmed',
                'paid_at' => now()
            ]);

            // Send success email
            $emailResult = $this->sendOrderSuccessEmail($orderId);

            // Send SMS notification
            if ($order->address && $order->address->receiver_phone) {
                $this->sendOrderSuccessSMS(
                    $order->address->receiver_phone,
                    $order->address->receiver_name,
                    $order->cart_id,
                    $order->total_price
                );
            }

            Log::info('Payment success handled successfully', [
                'order_id' => $orderId,
                'email_sent' => $emailResult['success'],
                'payment_data' => $paymentData
            ]);

            return [
                'success' => true,
                'message' => 'Payment success handled successfully',
                'email_result' => $emailResult
            ];
        } catch (\Exception $e) {
            Log::error('Failed to handle payment success', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);

            return [
                'success' => false,
                'message' => 'Failed to handle payment success: ' . $e->getMessage()
            ];
        }
    }
    public function render()
    {
        // if ($this->address_id) {
        //     $this->addressForm = false;
        // }

        return view('livewire.checkout');
    }
}
