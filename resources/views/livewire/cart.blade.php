<div>
    <main class="main-cart-page">
        <div class="page-header">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">Cart</h2>
                        <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                    </div>
                </div>
            </div><!-- End .container -->
        </div><!-- End .page-header -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shop.page.index') }}">Shop</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> <a href="{{ route('getCartItems') }}">
                            Cart</a>
                    </li>

                </ol>
            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->

        <div class="page-content">
            <div class="cart">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-9">
                            <table class="table table-cart table-mobile">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $totalPriceProduct = 0;
                                    @endphp
                                    @forelse ($cart_items as $item)
                                        {{-- @dd($item) --}}
                                        <tr>
                                            @php

                                                if (auth()->user()) {
                                                    $mrp = $item['variation']->mrp ?? $item->product->base_mrp;
                                                    $price = $item['variation']->price ?? $item->product->base_price;
                                                    // $shippingCharge = $getShippingCharge->shipping_charge ?? 0;
                                                    $percentOff =
                                                        $mrp > 0 && $price > 0
                                                            ? round((($mrp - $price) / $mrp) * 100)
                                                            : 0;
                                                } else {
                                                    $mrp = $item['variation']->mrp ?? $item['product']->base_mrp;
                                                    $price = $item['variation']->price ?? $item['product']->base_price;
                                                    // $shippingCharge = $getShippingCharge->shipping_charge ?? 0;
                                                    $percentOff =
                                                        $mrp > 0 && $price > 0
                                                            ? round((($mrp - $price) / $mrp) * 100)
                                                            : 0;
                                                }
                                            @endphp

                                            @php
                                                $attrs = [];
                                                foreach ($item['variation']->variation_attributes as $attr) {
                                                    $attrName = $attr->attribute->attribute->name ?? '';
                                                    $attrValue = $attr->attribute->name ?? '';
                                                    if ($attrName && $attrValue) {
                                                        $attrs[] = $attrName . ': ' . $attrValue;
                                                    }
                                                }
                                                $variation_image = $item['variation']->image ?? null;
                                            @endphp
                                        <tr>

                                            <td class="product-col">
                                                <div class="product">
                                                    @if (count($attrs) > 0)
                                                        <figure class="product-media">
                                                            <a
                                                                href="{{ route('single.product.view', $item['product']->slug) }}">
                                                                <img src="{{ asset('storage/' . $variation_image ?? $item['product']->product_image) }}"
                                                                    alt="{{ $item['product']->product_name }}">
                                                            </a>
                                                        </figure>
                                                    @else
                                                        <figure class="product-media">
                                                            <a
                                                                href="{{ route('single.product.view', $item['product']->slug) }}">
                                                                <img src="{{ asset('storage/' . $item['product']->product_image) }}"
                                                                    alt="{{ $item['product']->product_name }}">
                                                            </a>
                                                        </figure>
                                                    @endif

                                                    @php
                                                        $attrs = [];
                                                    @endphp
                                                    @foreach ($item['variation']->variation_attributes as $attr)
                                                        @php
                                                            $attrName = $attr->attribute->attribute->name ?? '';
                                                            $attrValue = $attr->attribute->name ?? '';
                                                            $attrs[] = $attrName . ': ' . $attrValue;
                                                        @endphp
                                                    @endforeach
                                                    <h3 class="product-title">
                                                        <a
                                                            href="{{ route('single.product.view', $item['product']->slug) }}">{{ $item['product']->product_name }}</a>
                                                    </h3><!-- End .product-title -->
                                                    <span class="fs-14">
                                                        @if (count($attrs) > 0)
                                                            ({{ implode(', ', $attrs) }})
                                                        @endif
                                                    </span>
                                                </div><!-- End .product -->
                                            </td>
                                            <td class="price-col">
                                                ₹{{ number_format($price == 0 ? $mrp : $price, 0, '.', ',') }}</td>
                                            <td class="quantity-col">
                                                <div class="cart-product-quantity quantity">
                                                    <button type="button" class="qty-minus"
                                                        wire:click="updateQuantityFromJS({{ $item['variation_id'] }},{{ $item['product']->product_id }}, {{ $item['quantity'] - 1 }})">-</button>
                                                    <input type="number" class="form-control"
                                                        value="{{ $item['quantity'] }}" min="1"
                                                        max="{{ $item['variation']->stock }}" step="1"
                                                        data-decimals="0" required>
                                                    @if ($item['quantity'] + 1 > $item['variation']->stock)
                                                        <button class="qty-plus"
                                                            wire:click="stockNotAvailable">+</button>
                                                    @else
                                                        <button type="button" class="qty-plus"
                                                            wire:click="updateQuantityFromJS({{ $item['variation_id'] }},{{ $item['product']->product_id }}, {{ $item['quantity'] + 1 }})">+</button>
                                                    @endif
                                                </div><!-- End .cart-product-quantity -->
                                            </td>
                                            <td class="total-col">
                                                ₹{{ number_format((int) ($price == 0 ? $mrp : $price) * (int) $item['quantity'], 0, '.', ',') }}
                                            </td>
                                            <td class="remove-col"><button
                                                    data-product_id="{{ $item['product']['product_id'] }}"
                                                    wire:click='removeProduct("{{ $item['variation_id'] }}", "{{ $item['product']['product_id'] }}")'
                                                    data-id="{{ $item['variation_id'] }}" class="btn-remove"><i
                                                        class="icon-close"></i></button></td>
                                        </tr>

                                        @php
                                            $totalPriceProduct +=
                                                (int) ($price == 0 ? $mrp : $price) * (int) $item['quantity'];
                                        @endphp
                                    @empty
                                        <tr>
                                            <td align="center" colspan="4">No Product</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table><!-- End .table table-wishlist -->

                            @php
                                $shippingMessage = '';
                                $nextTarget = null;

                                if ($totalPriceProduct > 0 && $totalPriceProduct < 500) {
                                    $nextTarget = 500;
                                    $shippingMessage =
                                        'Spend ₹' .
                                        ($nextTarget - $totalPriceProduct) .
                                        ' more and pay just to ₹50 for shipping';
                                } elseif ($totalPriceProduct >= 500 && $totalPriceProduct < 700) {
                                    $nextTarget = 700;
                                    $shippingMessage =
                                        'Spend ₹' . ($nextTarget - $totalPriceProduct) . ' more and enjoy FREE shipping';
                                } elseif ($totalPriceProduct >= 700) {
                                    $shippingMessage = 'Congrats! You’ve unlocked FREE shipping 🎉';
                                }
                            @endphp

                            @if ($shippingMessage)
                                <div class="alert alert-info mb-3 text-center" style="font-size:14px;">
                                    {{ $shippingMessage }}
                                </div>
                            @endif
                            <div class="cart-bottom">
                                <div class="cart-discount">
                                    <form wire:submit="apply_coupon">
                                        <div class="input-group">
                                            <input type="text" wire:model="coupon_code" class="form-control" required
                                                placeholder="coupon code">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-primary-2" type="submit"><i
                                                        class="icon-long-arrow-right"></i></button>
                                            </div><!-- .End .input-group-append -->
                                        </div><!-- End .input-group -->
                                    </form>
                                </div><!-- End .cart-discount -->

                                {{-- <a href="#" class="btn btn-outline-dark-2"><span>UPDATE CART</span><i
                                        class="icon-refresh"></i></a> --}}
                            </div><!-- End .cart-bottom -->
                        </div><!-- End .col-lg-9 -->
                        <aside class="col-lg-3">
                            <div class="summary summary-cart">
                                <h3 class="summary-title">Cart Total</h3><!-- End .summary-title -->

                                <table class="table table-summary">
                                    <tbody>
                                        <tr class="summary-subtotal">
                                            <td>Subtotal:</td>
                                            <td>₹{{ number_format($totalPriceProduct, 0, '.', ',') }}</td>
                                        </tr><!-- End .summary-subtotal -->
                                        <tr class="summary-subtotal">
                                            <td>Coupon</th>
                                            <td data-title="coupon">

                                                @if ($coupon_code)
                                                    ({{ $coupon_code }})

                                                    <span class="remove-b" wire:click="removeCoupon"><i
                                                            class="icon-close"></i></span>
                                                @endif
                                                &nbsp;<span class="text-success">-₹{{ $discount ?? 0 }} </span>

                                            </td>
                                        </tr>
                                        <tr class="summary-shipping">
                                            @if ($shippingAmount == 0)
                                                <td>Shipping:</td>
                                                <td>FREE</td>
                                            @else
                                                <td>Shipping:</td>
                                                <td>&nbsp;
                                                    @if (count($cart_items) != 0)
                                                        ₹{{ count($cart_items) ? number_format($shippingAmount) : '0' }}
                                                    @else
                                                        ₹0
                                                    @endif
                                                </td>
                                            @endif

                                        </tr>

                                        {{-- <tr class="summary-shipping-row">
                                            <td>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="free-shipping" name="shipping"
                                                        class="custom-control-input" checked>
                                                    <label class="custom-control-label" for="free-shipping">
                                                        Shipping</label>
                                                </div><!-- End .custom-control -->
                                            </td>
                                            <td>₹100.00</td>
                                        </tr><!-- End .summary-shipping-row --> --}}




                                        <tr class="summary-total">
                                            <td>Total:</td>
                                            <td>
                                                {{-- @dd($subTotal) --}}
                                                @if (count($cart_items) != 0)
                                                    ₹{{ number_format($subTotal, 0, '.', ',') }}
                                                @else
                                                    ₹0
                                                @endif
                                            </td>
                                        </tr><!-- End .summary-total -->
                                    </tbody>
                                </table><!-- End .table table-summary -->

                                {{-- <a wire:click="checkout" class="btn btn-outline-primary-2 btn-order btn-block">PROCEED
                                    TO
                                    CHECKOUT</a> --}}
                                <a wire:click="checkout" class="btn btn-outline-primary-2 btn-order btn-block">
                                    PROCEED TO CHECKOUT
                                </a>


                            </div><!-- End .summary -->

                            <a href="{{ route('shop.page.index') }}"
                                class="btn btn-outline-dark-2 btn-block mb-3"><span>CONTINUE
                                    SHOPPING</span><i class="icon-refresh"></i></a>
                        </aside><!-- End .col-lg-3 -->
                    </div><!-- End .row -->
                </div><!-- End .container -->
            </div><!-- End .cart -->
        </div><!-- End .page-content -->
    </main><!-- End .main -->
    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
            <div class="modal-content p-0 border-0 bg-transparent">
                @livewire('auth-page')
            </div>
        </div>
    </div>

    <style>
        /* .modal-dialog {
            display: flex;
            justify-content: center;
        }

        .modal-content {
            width: auto;
        } */
    </style>
    <script>
        function changeQuantity(button, change) {
            const quantityDiv = button.closest('.quantity');
            const input = quantityDiv.querySelector('.qty-text');
            const currentValue = parseInt(input.value) || 1;
            const newValue = Math.max(1, currentValue + change);

            input.value = newValue;
            console.log('newValue', newValue)
            updateLivewireQuantity(input);
        }

        function updateLivewireQuantity(el) {
            const variationId = el.dataset.id;
            const product_id = el.dataset.product_id;
            const value = parseInt(el.value);

            console.log({
                id: variationId,
                product_id: product_id,
                value: value
            });

            if (isNaN(value) || value < 1) {
                // Force the input to update
                el.value = 1;
                el.setAttribute('value', 1);

                // Force a re-render by triggering change event
                el.dispatchEvent(new Event('input'));

                console.log('Reset value to:', el.value);

                Livewire.dispatch('toast', {
                    type: 'error',
                    message: 'Quantity cannot be less than 1'
                });
                return;
            }

            Livewire.dispatch('updateQuantityFromJS', {
                variation_id: variationId,
                product_id: product_id,
                quantity: value
            });
        }

        function removeProduct(el) {
            const variationId = el.dataset.id;
            const product_id = el.dataset.product_id;
            console.log({
                id: variationId
            })
            Livewire.dispatch('removeProductFromJS', {
                variation_id: variationId,
                product_id: product_id
            });
            // Livewire.dispatch('main-cart-updated');
            $(el).closest('tr').hide();

            setTimeout(function() {
                var getTotalPrice = $('.cart-total-price').html();
                $("#sub-total").html(getTotalPrice);
                console.log("getTotalPrice", getTotalPrice);
            }, 800);
        }

        function showLoginModal(event) {
            event.preventDefault();
            $('#loginModal').modal('show');
        }
    </script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-login-modal', () => {
                $('#loginModal').modal('show');
            });
        });
    </script>

</div>
