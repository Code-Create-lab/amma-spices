<div class="container he-right">
    <div class="header-right">

        @php
            $ua = strtolower(request()->header('User-Agent'));

            $isMobile = preg_match('/iphone|android|ipad|mobile|blackberry|opera mini/', $ua);
        @endphp
        @if (!$isMobile)
            @livewire('search-product')
        @endif



        @if (!auth()->user())
            <a wire:click="loginWindow" class="wishlist-link">
                <div class="icon position-relative">
                    <i class="icon-user"></i>

                </div>
            </a>
            {{-- <a  href="{{ route('login.index') }}" class="wishlist-link">
                <div class="icon position-relative">
                    <i class="icon-user"></i>

                </div>
            </a> --}}
        @else
        <nav class="main-navc">
            <ul class="menu sf-arrows sf-js-enabled mobil-n-u-p">
                <li class="">
                    <a class="mobil-n-u-pp wishlist-link sf-with-ul">
                        <div class="icon position-relative">
                            <i class="icon-user"></i>

                        </div>
                    </a>
                    <ul class="sub-menu-l">
                        <li><img src="{{ asset('assets/img/my-addres.png') }}"><a href="{{ route('profile.index') }}">My
                                Account</a></li>
                        <li><img src="{{ asset('assets/img/My-order.png') }}"><a
                                href="{{ route('customer.orders.index') }}">My
                                Orders</a>
                        </li>
                        <li><img src="{{ asset('assets/img/Wishlist-p.png') }}"><a
                                href="{{ route('wishlist') }}">Wishlist</a></li>
                        <li><img src="{{ asset('assets/img/my-addres.png') }}"><a
                                href="{{ route('dashboard_my_addresses') }}">My
                                Address</a>
                        </li>
                        <li><img src="{{ asset('assets/img/Logout1.png') }}"><a
                                href="{{ route('customer.logout') }}">Logout</a></li>
                    </ul>

                </li>
            </ul>
        </nav>
        @endif

        <a href="{{ route('wishlist') }}" class="wishlist-link">
            <div class="icon position-relative">
                <i class="icon-heart-o"></i>
                <span class="wishlist-count">{{ $wishlist_items_count }}</span>
            </div>
        </a>

        <div class="dropdown cart-dropdown">
            <a href="#" class="dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false" data-display="static">
                <div class="icon position-relative">
                    <i class="icon-shopping-cart"></i>
                    <span class="cart-count">{{ $cart_items_count }}</span>
                </div>
                <span class="cart-txt font-weight-normal">₹{{ number_format($cart_total, 0, '.', ',') }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-cart-products">
                    @foreach ($cart_items as $item)
                        @php
                            $mrp = $item['variation']->mrp;
                            $price = $item['variation']->price;
                            $percentOff = $mrp > 0 && $price > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
                        @endphp

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
                        <div class="product mb-0 rounded-0 w-100">
                            <div class="product-cart-details">
                                <h4 class="product-title overflow-hidden letter-spacing-normal">
                                    <a
                                        href="{{ route('single.product.view', $item['product']->slug) }}">{{ $item['product']->product_name }}</a>
                                </h4>

                                <span class="cart-product-info">
                                    <span class="cart-product-qty">{{ $item['quantity'] }}</span>
                                    x ₹{{ number_format($price == 0 ? $mrp : $price, 0, '.', ',') }}
                                </span>
                                @if (count($attrs) > 0)
                                    <p class="product_details_para">({{ implode(', ', $attrs) }})</p>
                                @endif
                            </div><!-- End .product-cart-details -->

                            <figure class="product-image-container">
                                <a href="{{ route('single.product.view', $item['product']->slug) }}"
                                    class="product-image">
                                    <img src="{{ asset('storage/' . $item['product']->product_image) }}"
                                        alt="product mb-0 rounded-0 w-100">
                                </a>
                            </figure>
                            <a href="javascript:void(0);" onclick="removeProduct(this)"
                                data-id="{{ $item['variation_id'] }}"
                                data-product_id="{{ $item['product']['product_id'] }}" class="btn-remove"
                                title="Remove Product"><i class="icon-close"></i></a>
                        </div><!-- End .product -->
                    @endforeach

                    <script>
                        function removeProduct(el) {
                            const variationId = el.dataset.id;
                            const product_id = el.dataset.product_id;
                            console.log({
                                id: variationId
                            })
                            //   window.dispatchEvent(
                            //     new CustomEvent('removeProductFromJS',  { variation_id: variationId, product_id : product_id })
                            // );
                            Livewire.dispatch('removeProductFromJS', {
                                variation_id: variationId,
                                product_id: product_id
                            });
                        }
                    </script>

                </div><!-- End .cart-product -->

                <div class="dropdown-cart-total">
                    <span>Total</span>

                    <span class="cart-total-price">₹{{ number_format($cart_total, 0, '.', ',') }}</span>
                </div><!-- End .dropdown-cart-total -->

                <div class="dropdown-cart-action">
                    <a href="{{ route('getCartItems') }}" class="btn btn-primary">View Cart</a>
                    {{-- <a href="#" class="btn btn-outline-primary-2"><span>Checkout</span><i
                            class="icon-long-arrow-right"></i></a> --}}
                </div><!-- End .dropdown-cart-total -->
            </div><!-- End .dropdown-menu -->
        </div><!-- End .cart-dropdown -->
    </div>

    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
            <div class="modal-content p-0 border-0 bg-transparent">
                @livewire('auth-page')
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-login-modal', () => {
                $('#loginModal').modal('show');
            });
        });
    </script>

</div>
