<div>

    @php
        $user = auth()->user();
    @endphp

    <style>
        label {
            color: #000000;
            font-weight: bold;
        }
    </style>
    <main class="main">
        <div class="page-header">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">Checkout</h2>
                        <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                    </div>
                </div>
            </div><!-- End .container -->
        </div><!-- End .page-header -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('getCartItems') }}">Shopping Cart</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                </ol>
            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->
        <!-- end section -->
        <!-- start section -->
        <section class="checkout-page">
            <img src="{{ asset('assets/img/checkout-top-img.png') }}" class="bg-feture-check-top">
            <img src="{{ asset('assets/img/bg-feture.png') }}" class="bg-feture-check">
            <div class="container">
                <form wire:submit="storeOrder">
                    <div class="row justify-content-center coupon-box-in">
                        @if (!auth()->user())
                        @endif
                        <div class="col-auto">
                            <div class="feature-box feature-box-left-icon coupon_code">
                                <div class="feature-box-icon me-5px">
                                    <i
                                        class="feather icon-feather-scissors top-9px position-relative text-dark-gray icon-small"></i>
                                </div>
                                <div class="feature-box-content">
                                    <!-- Button trigger modal -->
                                    <!-- Button trigger modal -->
                                    <!-- Modal -->
                                    <div class="modal fade " id="exampleModal" tabindex="-1"
                                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Coupon List</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="container-iner">
                                                        @foreach ($getCouponList as $coupon)
                                                            <div class="modal-content-area-in">
                                                                <p class="coupon-text">
                                                                    {{ $coupon['coupon_description'] ?? 'Coupon Description' }}
                                                                </p>
                                                                <div class="CouponList-text-po">
                                                                    <div class="List-text-b">
                                                                        <input type="text"
                                                                            value="{{ $coupon['coupon_code'] }}"
                                                                            class="coupon-input {{ $coupon_code == $coupon['coupon_code'] ? 'coupoun_applied' : '' }}"
                                                                            required placeholder="coupon code" readonly>
                                                                    </div>
                                                                    <div class="CouponList-btn">
                                                                        <button type="button"
                                                                            class="appl-bt-pop lose btn apply-coupon-btn"
                                                                            data-coupon-code="{{ $coupon['coupon_code'] }}"
                                                                            data-dismiss="modal" aria-label="Close">
                                                                            <span class="button-text">
                                                                                @if ($coupon_code == $coupon['coupon_code'])
                                                                                    Applied
                                                                                @else
                                                                                    Apply
                                                                                @endif
                                                                            </span>
                                                                            <span class="loading-text"
                                                                                style="display: none;">
                                                                                <i class="fas fa-spinner fa-spin"></i>
                                                                                Applying...
                                                                            </span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <span class="d-inline-block c-b-l" data-toggle="modal"
                                        data-target="#exampleModal">Have a
                                        coupon?
                                        <a href="#modal-popup2"class="coupon-box  popup-with-zoom-anim">Click
                                            here to enter your code</a></span>
                                    <!-- start modal pop-up -->
                                    <div id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                        aria-hidden="true" wire:ignore.self id="modal-popup2"
                                        class="modal fade zoom-anim-dialog mfp-hide  col-md-4  mx-auto bg-white text-center modal-popup-main p-20px">
                                        <span class="text-dark-gray fw-600 fs-24 mb-10px d-block">Coupon List</span>
                                        <div class="modal-content-area">
                                            @foreach ($getCouponList as $coupon)
                                                <div class="modal-content-area-in">
                                                    <div class="CouponList-text">
                                                        <p class="coupon-text">
                                                            {{ $coupon['coupon_description'] ?? 'Coupon Description' }}
                                                        </p>
                                                        <div class="CouponList-text-po">
                                                            <div class="List-text-b">
                                                                <input type="text"
                                                                    value="{{ $coupon['coupon_code'] }}"
                                                                    class="coupon-input {{ $coupon_code == $coupon['coupon_code'] ? 'coupoun_applied' : '' }}"
                                                                    required placeholder="coupon code" readonly>
                                                            </div>
                                                            <div class="CouponList-btn">
                                                                <button type="button"
                                                                    class="appl-bt-pop lose btn apply-coupon-btn"
                                                                    data-coupon-code="{{ $coupon['coupon_code'] }}"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                    <span class="button-text">
                                                                        @if ($coupon_code == $coupon['coupon_code'])
                                                                            Applied
                                                                        @else
                                                                            Apply
                                                                        @endif
                                                                    </span>
                                                                    <span class="loading-text" style="display: none;">
                                                                        <i class="fas fa-spinner fa-spin"></i>
                                                                        Applying...
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>
                                    <!-- end modal pop-up -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Handle coupon apply button clicks
                            document.addEventListener('click', function(e) {
                                if (e.target.closest('.apply-coupon-btn')) {
                                    e.preventDefault();
                                    const button = e.target.closest('.apply-coupon-btn');
                                    const couponCode = button.getAttribute('data-coupon-code');

                                    console.log('asdadasdasd', couponCode)
                                    // Show loading state
                                    const buttonText = button.querySelector('.button-text');
                                    const loadingText = button.querySelector('.loading-text');

                                    buttonText.style.display = 'none';
                                    loadingText.style.display = 'inline';
                                    button.disabled = true;

                                    // Dispatch custom event
                                    window.dispatchEvent(
                                        new CustomEvent('applyCoupon', {
                                            detail: {
                                                coupon_code: couponCode,
                                                type: 1
                                            }
                                        })
                                    );

                                    //   setTimeout(  $('.mfp-close').trigger('click'), 5000);
                                }
                            });

                            // Listen for response from Livewire component
                            window.addEventListener('couponApplied', function(event) {
                                // Reset all buttons
                                const buttons = document.querySelectorAll('.apply-coupon-btn');
                                buttons.forEach(button => {
                                    const buttonText = button.querySelector('.button-text');
                                    const loadingText = button.querySelector('.loading-text');

                                    buttonText.style.display = 'inline';
                                    loadingText.style.display = 'none';
                                    button.disabled = false;
                                });
                                // console.log("coupon applied",event.detail, event.detail[0].success, buttons);
                                // Close modal if needed
                                if (event.detail[0].type == 'error') {
                                    console.log(buttons);
                                    button.disabled = false;
                                    // You can add modal close logic here
                                    // $('.mfp-close').trigger('click');
                                }

                                if (event.detail[0].success) {
                                    // You can add modal close logic here
                                    $('.mfp-close').trigger('click');
                                }
                            });
                        });
                    </script>
                    <div class="row align-items-start">

                        <div class="col-lg-8">

                            {{-- @dd($this->addresses) --}}
                            @foreach ($this->addresses as $address)
                                <div class="address-item address_page">
                                    <input name="address_id" wire:model="address_id" type="radio"
                                        id="{{ $address->uuid }}" value="{{ $address->uuid }}"
                                        {{ $loop->first ? 'checked=checked' : '' }} class="me-2 mt-1">

                                    <div class="address-dt-all">
                                        <h4>{{ $address->type }}</h4>
                                        <div class="address-f">
                                            <div class="address-icon1">
                                                <i class="fa fa-map-marker-alt"></i>
                                            </div>
                                            <p>
                                                {{ $address->house_no }},
                                                {{ $address->society }},
                                                {{ $address->city }},
                                                {{ $address->state }},
                                                {{ $address->pincode }}
                                            </p>
                                        </div>
                                        <ul class="action-btns">
                                            <li>
                                                <a class="action-btn edit-address"
                                                    wire:click="showAddress('{{ $address->uuid }}')"
                                                    wire:loading.attr="disabled" wire:loading.class="disabled"
                                                    wire:target="showAddress">
                                                    <span wire:loading.class.remove="d-inline"
                                                        wire:loading.class.add="d-none" wire:target="showAddress"
                                                        class="d-inline">
                                                        <i class="uil uil-edit"></i>Edit
                                                    </span>
                                                    <span wire:loading.class.remove="d-none"
                                                        wire:loading.class.add="d-inline" wire:target="showAddress"
                                                        class="d-none">
                                                        <i class="fas fa-spinner fa-spin"></i>
                                                    </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('orders.address.delete', $address->uuid) }}"
                                                    class="action-btn">
                                                    <i class="uil uil-trash-alt"></i>Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach


                            @if (count($this->addresses) > 0)
                                <a wire:click="showAddressForm"
                                    class="btn btn-outline-primary-2  btn-rounded showAddressForm"
                                    wire:loading.attr="disabled" wire:loading.class="disabled"
                                    wire:target="showAddressForm">
                                    <span wire:loading.class.remove="d-inline" wire:loading.class.add="d-none"
                                        wire:target="showAddressForm" class="d-inline">Add New Address</span>
                                    <span wire:loading.class.remove="d-none" wire:loading.class.add="d-inline"
                                        wire:target="showAddressForm" class="d-none">
                                        <i class="fas fa-spinner fa-spin"></i> Loading...
                                    </span>
                                </a>
                            @endif

                            @if ($addressForm || count($this->addresses) == 0)
                                <div class="row mr-b">
                                    <div class="col-md-12 Address-type">
                                        <label>Address Type *</label>
                                        <i class="fa-solid fa-angle-down"></i>
                                        <select wire:model="addressData.type" class="form-control">

                                            <option value="">Select Address</option>
                                            <option value="Home">Home</option>
                                            <option value="Office">Office</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        @if (session()->has('error_addressData.type'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.type') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="row mr-b">
                                    <div class="col-sm-6">
                                        <label>First Name *</label>
                                        <input wire:model.defer="addressData.firstName" type="text"
                                            class="form-control">
                                        @if (session()->has('error_addressData.firstName'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.firstName') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-sm-6">
                                        <label>Last Name *</label>
                                        <input wire:model.defer="addressData.lastName" type="text"
                                            class="form-control">
                                        @if (session()->has('error_addressData.lastName'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.lastName') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mr-b">
                                    <div class="col-sm-12">
                                        <label>Email address {{ $user->email ? '' : '*' }}</label>
                                        <input wire:model.defer="addressData.receiver_email" type="email"
                                            value="{{ $user->email }}" class="form-control">
                                        @if (session()->has('error_addressData.receiver_email'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.receiver_email') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mr-b">
                                    <div class="col-sm-6">
                                        <label>Phone {{ $user->user_phone ? '' : '*' }}</label>
                                        <input wire:model.defer="addressData.receiver_phone" type="tel"
                                            value="{{ $user->user_phone }}" class="form-control">
                                        @if (session()->has('error_addressData.receiver_phone'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.receiver_phone') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-sm-6">
                                        <label>Alternate Phone No </label>
                                        <input wire:model.defer="addressData.alternate_phone" type="tel"
                                            class="form-control">
                                        @if (session()->has('error_addressData.alternate_phone'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.alternate_phone') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mr-b">
                                    <div class="col-md-12">
                                        <label>Street address *</label>
                                        <input type="text" wire:model.defer="addressData.house_no"
                                            class="form-control" placeholder="House number and Street name"
                                            oninput="
                                                this.value = this.value
                                               // .replace(/[^A-Za-z0-9\s]/g, '')     // remove special chars
                                                ;                       // limit to 50 chars   .slice(0, 50)
                                        ">
                                        @if (session()->has('error_addressData.house_no'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.house_no') }}</span>
                                        @endif

                                        <input type="text" wire:model.defer="addressData.society"
                                            class="form-control" placeholder="Apartments, suite, unit etc ..."
                                            oninput="
                                                this.value = this.value
                                               // .replace(/[^A-Za-z0-9\s]/g, '')     // remove special chars
                                                ;                       // limit to 50 chars .slice(0, 50)
                                        ">
                                        @if (session()->has('error_addressData.society'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.society') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mr-b">
                                    <div class="col-sm-4">
                                        <label>Town / City *</label>
                                        <input wire:model.defer="addressData.city" type="text"
                                            class="form-control"
                                            oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')">
                                        @if (session()->has('error_addressData.city'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.city') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-sm-4">
                                        <label>State *</label>
                                        <input wire:model.defer="addressData.state" type="text"
                                            class="form-control"
                                            oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')">
                                        @if (session()->has('error_addressData.state'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.state') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-sm-4">
                                        <label>Postcode / ZIP *</label>
                                        <input wire:model.defer="addressData.pincode" type="text"
                                            class="form-control">
                                        @if (session()->has('error_addressData.pincode'))
                                            <span
                                                class="text-red-500 text-sm">{{ session('error_addressData.pincode') }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if ($address_id)
                                    <button type="button" class="btn btn-outline-primary-2 updateAddress-btn"
                                        wire:click="updateAddress" wire:loading.attr="disabled"
                                        wire:loading.class="disabled" wire:target="updateAddress">
                                        <span wire:loading.class.remove="d-inline" wire:loading.class.add="d-none"
                                            wire:target="updateAddress" class="d-inline">Update Address</span>
                                        <span wire:loading.class.remove="d-none" wire:loading.class.add="d-inline"
                                            wire:target="updateAddress" class="d-none">
                                            <i class="fas fa-spinner fa-spin"></i> Updating...
                                        </span>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-outline-primary-2 updateAddress-btn"
                                        wire:click="addAddress" wire:loading.attr="disabled"
                                        wire:loading.class="disabled" wire:target="addAddress">
                                        <span wire:loading.class.remove="d-inline" wire:loading.class.add="d-none"
                                            wire:target="addAddress" class="d-inline">Add Address</span>
                                        <span wire:loading.class.remove="d-none" wire:loading.class.add="d-inline"
                                            wire:target="addAddress" class="d-none">
                                            <i class="fas fa-spinner fa-spin"></i> Adding...
                                        </span>
                                    </button>
                                @endif
                            @endif

                        </div>
                        <!-- Coupon Modal -->
                        <div wire:ignore.self class="modal bd-example-modal-lg fade" id="exampleModal" tabindex="-1"
                            role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Coupon Lists</h5>
                                        <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body apply-box-f">
                                        <div class="col-md-12 mt-1">
                                            @foreach ($getCouponList as $coupon)
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <p>{{ $coupon['coupon_description'] ?? 'Coupon Description' }}
                                                        </p>
                                                        <input type="text" value="{{ $coupon['coupon_code'] }}"
                                                            class="form-control {{ $coupon_code == $coupon['coupon_code'] ? 'coupoun_applied' : '' }}"
                                                            required placeholder="coupon code" readonly>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <a href="#" class="appl-bt-pop lose"
                                                            data-dismiss="modal" aria-label="Close"
                                                            wire:click="apply_coupon('{{ $coupon['coupon_code'] }}', 1)"
                                                            wire:loading.attr="disabled" wire:loading.class="disabled"
                                                            wire:target="apply_coupon">
                                                            <span wire:loading.class.remove="d-inline"
                                                                wire:loading.class.add="d-none"
                                                                wire:target="apply_coupon" class="d-inline">
                                                                @if ($coupon_code == $coupon['coupon_code'])
                                                                    Applied
                                                                @else
                                                                    Apply
                                                                @endif
                                                            </span>
                                                            <span wire:loading.class.remove="d-none"
                                                                wire:loading.class.add="d-inline"
                                                                wire:target="apply_coupon" class="d-none">
                                                                <i class="fas fa-spinner fa-spin"></i> Applying...
                                                            </span>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <aside class="col-lg-4">
                            <div class="summary">
                                <h3 class="summary-title">Your Order</h3><!-- End .summary-title -->

                                <table class="table table-summary">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php
                                            $subTotal1 = 0;
                                            $subTotal = 0;
                                            $shippingCharge = 0;
                                        @endphp
                                        {{-- @dd($cart_items_checkOut) --}}
                                        @foreach ($cart_items_checkOut as $item)
                                            @php
                                                $shippingCharge = number_format($shippingAmount) ?? 0.0;

                                                // dd($shippingAmount);
                                                $mrp = round($item['variation']['mrp'] ?? $item['product']['base_mrp']);
                                                $price = round(
                                                    $item['variation']['price'] ?? $item['product']['base_price'],
                                                );
                                                $percentOff =
                                                    $mrp > 0 && $price > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
                                                $quantity = (int) $item['quantity'];
                                                $lineTotal = ($price == 0 ? $mrp : $price) * $quantity;
                                                $subTotal1 += $lineTotal;
                                                // $subTotal += $lineTotal ;
                                                // dd($lineTotal,$item['quantity']);
                                            @endphp
                                            {{-- @dd($subTotal1) --}}
                                            @php
                                                $attrs = [];
                                            @endphp

                                            @if (isset($item['variation']['variation_attributes']))
                                                @foreach ($item['variation']['variation_attributes'] as $attr)
                                                    @php
                                                        $attrName =
                                                            $attr['attribute']['attribute']['attribute_name'] ?? '';
                                                        $attrValue =
                                                            $attr['attribute']['attribute']['attribute_value'] ?? '';
                                                        if ($attrName && $attrValue) {
                                                            $attrs[] = $attrName . ': ' . $attrValue;
                                                        }
                                                    @endphp
                                                    {{-- @dd($item['variation']) --}}
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td><a
                                                        href="{{ route('single.product.view', $item['product']['slug']) }}">{{ $item['product']['product_name'] }}
                                                        x
                                                        {{ $item['quantity'] }}
                                                    </a>

                                                    <span class="fs-14 d-block">
                                                        @if (count($attrs) > 0)
                                                            ({{ implode(', ', $attrs) }})
                                                        @endif
                                                    </span>
                                                </td>
                                                <td> ₹{{ number_format(round($lineTotal), 0, '.', ',') }}</td>
                                            </tr>
                                        @endforeach

                                        <tr class="summary-subtotal">
                                            <td>Subtotal:</td>
                                            <td>₹{{ number_format(round($subTotal1), 0, '.', ',') }}</td>
                                        </tr><!-- End .summary-subtotal -->

                                        <tr class="coupon">
                                            <td>COUPON</td>
                                            <td data-title="coupon">

                                                @if ($coupon_code)
                                                    (
                                                    <span class="text-success">
                                                        {{ $coupon_code }}
                                                    </span>)

                                                    <span class="remove-b" wire:click="removeCoupon"><i
                                                            class="icon-close"></i></span>
                                                @endif
                                                &nbsp;-₹{{ number_format($discount ?? 0, 2, '.', ',') }}

                                            </td>
                                        </tr>
                                        <tr>
                                            @if ($shippingAmount == 0)
                                                <td>Shipping:</td>
                                                <td> Free </td>
                                            @else
                                                {{-- Later updated from admin panel --}}
                                                <td>Shipping:</td>
                                                <td> ₹{{ number_format($shippingAmount, 2) ?? '0.00' }}
                                                </td>
                                            @endif
                                        </tr>

                                        <tr class="summary-total">
                                            <td>Total:</td>
                                            <td> ₹{{ number_format(round($subTotal1 - ($discount ?? 0) + $shippingCharge), 2, '.', ',') }}
                                            </td>
                                        </tr><!-- End .summary-total -->
                                    </tbody>
                                </table><!-- End .table table-summary -->


                                <div class="payment_methods">
                                    {{-- <div class="cod_payment">

                                        <input class="d-inline w-auto me-5px mb-0 p-0" type="radio"
                                            name="payment-option" wire:model="payment_option" value="COD">
                                        <span class="d-inline-block text-dark-gray fw-500">Cash On Delivery</span>
                                    </div> --}}
                                    <div class="online_payment">
                                        <input class="d-inline w-auto me-5px mb-0 p-0" type="radio"
                                            name="payment-option" checked wire:model="payment_option" id="online"
                                            value="ONLINE">
                                        <span class="d-inline-block text-dark-gray fw-500" id="online">Online
                                            Payment</span>
                                    </div>
                                </div>
                                {{-- <div class="accordion-summary" id="accordion-payment">
                                    <div class="card">
                                        <div class="card-header" id="heading-1">
                                            <h2 class="card-title">
                                                <a role="button" data-toggle="collapse" href="#collapse-1"
                                                    aria-expanded="true" aria-controls="collapse-1">
                                                    Direct bank transfer
                                                </a>
                                            </h2>
                                        </div><!-- End .card-header -->
                                        <div id="collapse-1" class="collapse show" aria-labelledby="heading-1"
                                            data-parent="#accordion-payment">
                                            <div class="card-body">
                                                Make your payment directly into our bank account. Please use your Order
                                                ID as the payment reference. Your order will not be shipped until the
                                                funds have cleared in our account.
                                            </div><!-- End .card-body -->
                                        </div><!-- End .collapse -->
                                    </div><!-- End .card -->

                                    <div class="card">
                                        <div class="card-header" id="heading-2">
                                            <h2 class="card-title">
                                                <a class="collapsed" role="button" data-toggle="collapse"
                                                    href="#collapse-2" aria-expanded="false"
                                                    aria-controls="collapse-2">
                                                    Check payments
                                                </a>
                                            </h2>
                                        </div><!-- End .card-header -->
                                        <div id="collapse-2" class="collapse" aria-labelledby="heading-2"
                                            data-parent="#accordion-payment">
                                            <div class="card-body">
                                                Ipsum dolor sit amet, consectetuer adipiscing elit. Donec odio. Quisque
                                                volutpat mattis eros. Nullam malesuada erat ut turpis.
                                            </div><!-- End .card-body -->
                                        </div><!-- End .collapse -->
                                    </div><!-- End .card -->

                                    <div class="card">
                                        <div class="card-header" id="heading-3">
                                            <h2 class="card-title">
                                               
                                                <a class="collapsed" role="button" data-toggle="collapse"
                                                    href="#collapse-3" aria-expanded="false"
                                                    aria-controls="collapse-3">
                                                    Cash on delivery
                                                </a>
                                            </h2>
                                        </div><!-- End .card-header -->
                                        <div id="collapse-3" class="collapse" aria-labelledby="heading-3"
                                            data-parent="#accordion-payment">
                                            <div class="card-body">Quisque volutpat mattis eros. Lorem ipsum dolor sit
                                                amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis
                                                eros.
                                            </div><!-- End .card-body -->
                                        </div><!-- End .collapse -->
                                    </div><!-- End .card -->

                                    <div class="card">
                                        <div class="card-header" id="heading-4">
                                            <h2 class="card-title">
                                                <a class="collapsed" role="button" data-toggle="collapse"
                                                    href="#collapse-4" aria-expanded="false"
                                                    aria-controls="collapse-4">
                                                    PayPal <small class="float-right paypal-link">What is
                                                        PayPal?</small>
                                                </a>
                                            </h2>
                                        </div><!-- End .card-header -->
                                        <div id="collapse-4" class="collapse" aria-labelledby="heading-4"
                                            data-parent="#accordion-payment">
                                            <div class="card-body">
                                                Nullam malesuada erat ut turpis. Suspendisse urna nibh, viverra non,
                                                semper suscipit, posuere a, pede. Donec nec justo eget felis facilisis
                                                fermentum.
                                            </div><!-- End .card-body -->
                                        </div><!-- End .collapse -->
                                    </div><!-- End .card -->

                                    <div class="card">
                                        <div class="card-header" id="heading-5">
                                            <h2 class="card-title">
                                                <a class="collapsed" role="button" data-toggle="collapse"
                                                    href="#collapse-5" aria-expanded="false"
                                                    aria-controls="collapse-5">
                                                    Credit Card (Stripe)
                                                    <img src="assets/images/payments-summary.png"
                                                        alt="payments cards">
                                                </a>
                                            </h2>
                                        </div><!-- End .card-header -->
                                        <div id="collapse-5" class="collapse" aria-labelledby="heading-5"
                                            data-parent="#accordion-payment">
                                            <div class="card-body"> Donec nec justo eget felis facilisis
                                                fermentum.Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                                                Donec odio. Quisque volutpat mattis eros. Lorem ipsum dolor sit ame.
                                            </div><!-- End .card-body -->
                                        </div><!-- End .collapse -->
                                    </div><!-- End .card -->
                                </div><!-- End .accordion --> --}}

                                <button type="submit" class="btn btn-outline-primary-2 btn-order btn-block"
                                    wire:loading.attr="disabled" wire:loading.class="disabled"
                                    wire:target="storeOrder">
                                    <span wire:loading.class.remove="d-inline" wire:loading.class.add="d-none"
                                        wire:target="storeOrder" class="d-inline">
                                        <span class="btn-text">Place Order</span>
                                        <span class="btn-hover-text">Proceed to Checkout</span>
                                    </span>
                                    <span wire:loading.class.remove="d-none" wire:loading.class.add="d-inline"
                                        wire:target="storeOrder" class="d-none">
                                        <i class="fas fa-spinner fa-spin"></i> Processing Order...
                                    </span>
                                </button>
                            </div><!-- End .summary -->
                        </aside><!-- End .col-lg-3 -->
                    </div>
                </form>
            </div>
            <img src="{{ asset('assets/img/checkout-bottom-img.png') }}" class="bg-feture-check-bot">
        </section>


        <script></script>
        <!-- end section -->
    </main>
</div>
