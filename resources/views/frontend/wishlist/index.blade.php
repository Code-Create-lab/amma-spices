@extends('frontend.layouts.app', ['title' => ''])

@section('content')
    <main class="main">
        <div class="page-header">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">Wishlist</h2>
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
                    <li class="breadcrumb-item active" aria-current="page">Wishlist</li>
                </ol>
            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->
        @if ($wishlists->isNotEmpty())
            <div class="page-content">
                <div class="container">
                    <table class="table table-wishlist table-mobile">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Stock Status</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($wishlists as $wishlist)
                                {{-- @dd($wishlist) --}}
                                @php
                                    $isInWishlist = false;
                                    $sessionWishlistData = session('wishlist') ?? [];
                                    if (auth()->user()) {
                                        $isInWishlist =
                                            auth()->check() &&
                                            in_array($wishlist->product->product_id, $wishlistProductIds);
                                    } else {
                                        foreach ($sessionWishlistData as $key => $value) {
                                            // dd($value['product_id'],$product->product_id);
                                            if ($value['product_id'] == $wishlist->product_id) {
                                                $isInWishlist = true;
                                            }
                                        }
                                    }
                                @endphp
                                @if (auth()->user())
                                    @if ($wishlist->product)
                                        <tr>
                                            <td class="product-col">
                                                <div class="product">
                                                    <figure class="product-media">
                                                        <a
                                                            href="{{ route('single.product.view', $wishlist->product->slug) }}">
                                                            <img src="{{ asset('storage/' . $wishlist->product->product_image) }}"
                                                                alt="Product image">
                                                        </a>
                                                    </figure>

                                                    <h3 class="product-title">
                                                        <a
                                                            href="{{ route('single.product.view', $wishlist->product->slug) }}">{{ $wishlist->product_name }}</a>
                                                    </h3><!-- End .product-title -->
                                                </div><!-- End .product -->
                                            </td>
                                            <td class="price-col">
                                                &#8377;{{ number_format($wishlist->product->base_price ?? $wishlist->product->base_mrp, 0, '.', ',') }}
                                            </td>
                                            <td class="stock-col">
                                                @if (count($wishlist->product->variations) > 0 &&
                                                        ($wishlist->product->variations[0]->stock == 0 || $wishlist->product->variations[0]->stock == '0'))
                                                    <span class="out-of-stock">
                                                        Out of stock
                                                    </span>
                                                @else
                                                    <span class="in-stock">
                                                        In stock
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="action-col">
                                                <div class="cart-box-btn d-flex gap-10 align-items-center justify-content-center"
                                                    data-product-id="{{ $wishlist->product_id }}">
                                                    @if (count($wishlist->product->variations) > 0 &&
                                                            ($wishlist->product->variations[0]->stock == 0 || $wishlist->product->variations[0]->stock == '0'))
                                                        <button data-product-id="{{ $wishlist->product_id }}"
                                                            class="btn btn-block btn-outline-primary-2 add-to-cart"><i
                                                                class="icon-cart-plus"></i>Out of
                                                            stock</button>
                                                    @else
                                                        <button data-product-id="{{ $wishlist->product_id }}"
                                                            class="btn btn-block btn-outline-primary-2 add-to-cart"><i
                                                                class="icon-cart-plus"></i>
                                                            @if ($wishlist->product->is_in_cart)
                                                                Added to cart
                                                            @else
                                                                Add to cart
                                                            @endif
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="remove-col">

                                                <a data-id="{{ $wishlist->product->product_id }}"
                                                    onclick="addTowishListClass(this)"
                                                    title="{{ $isInWishlist ? ' Added to wishlist' : ' Add to wishlist' }}"
                                                    class="  {{ $isInWishlist ? 'remove-from-wishlist liked' : 'add-to-wishlist' }}"><svg
                                                        class="wish-icon" title="Like Safebox SVG File" width="21"
                                                        height="21" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path
                                                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                                        </path>
                                                    </svg></a>
                                            </td>
                                        </tr>
                                    @endif
                                @else
                                    <tr>
                                        <td class="product-col">
                                            <div class="product">
                                                <figure class="product-media">
                                                    <a href="{{ route('single.product.view', $wishlist->slug) }}">
                                                        <img src="{{ asset('storage/' . $wishlist->product_image) }}"
                                                            alt="Product image">
                                                    </a>
                                                </figure>

                                                <h3 class="product-title">
                                                    <a
                                                        href="{{ route('single.product.view', $wishlist->slug) }}">{{ $wishlist->product_name }}</a>
                                                </h3><!-- End .product-title -->
                                            </div><!-- End .product -->
                                        </td>
                                        <td class="price-col">&#8377;{{ $wishlist->base_price ?? $wishlist->base_mrp }}
                                        </td>
                                        <td class="stock-col"><span class="in-stock">In stock</span></td>
                                        <td class="action-col">
                                            @if (count($wishlist->variations) > 0 &&
                                                    ($wishlist->variations[0]->stock == 0 || $wishlist->variations[0]->stock == '0'))
                                                <button data-id="{{ $wishlist->product_id }}"
                                                    class="btn btn-block btn-outline-primary-2 add-to-cart"><i
                                                        class="icon-cart-plus"></i>Out of
                                                    stock</button>
                                            @else
                                                <div class="cart-box-btn d-flex gap-10 align-items-center justify-content-center"
                                                    data-product-id="{{ $wishlist->product_id }}">
                                                    <a
                                                        class="btn btn-block btn-outline-primary-2 add-to-cart-home add-to-cart">
                                                        <span>Add to cart</span>

                                                    </a>
                                                    <div class="product-details-quantity">
                                                        @php
                                                            $cartQty = 1;

                                                            if (auth()->check()) {
                                                                // DB cart
                                                                $cartQty = $wishlist->cart->quantity ?? 1;
                                                            } else {
                                                                // Session cart
                                                                $cartKey =
                                                                    $wishlist->product_id .
                                                                    '-' .
                                                                    $wishlist->variation->id;
                                                                $cartQty = session("cart.$cartKey.quantity", 1);
                                                            }
                                                        @endphp

                                                        {{-- <input type="number" class="form-control qty product_quantity"
                                                            value="{{ $cartQty }}" min="1" max="10"
                                                            step="1" data-decimals="0" required> --}}

                                                    </div>
                                                </div>
                                                {{-- <button data-id="{{ $wishlist->product_id }}"
                                                    class="btn btn-block btn-outline-primary-2 add-to-cart"><i
                                                        class="icon-cart-plus"></i>
                                                    @if ($wishlist->is_in_cart)
                                                        Added to cart
                                                    @else
                                                        Add to cart
                                                    @endif
                                                </button> --}}
                                            @endif
                                        </td>
                                        <td class="remove-col"><a data-id="{{ $wishlist->product_id }}"
                                                onclick="addTowishListClass(this)"
                                                title="{{ $isInWishlist ? ' Remove from wishlist' : ' Add to wishlist' }}"
                                                class=" {{ $isInWishlist ? 'remove-from-wishlist liked' : 'add-to-wishlist' }} remove-row-btn"><svg
                                                    class="wish-icon" title="Like Safebox SVG File" width="21"
                                                    height="21" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path
                                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                                    </path>
                                                </svg></a>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                            @endforelse
                        </tbody>
                    </table><!-- End .table table-wishlist -->
                </div><!-- End .container -->
            </div><!-- End .page-content -->
        @endif
    </main><!-- End .main -->
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-from-wishlist')) {
                const row = e.target.closest('tr');
                if (row) row.remove();
                setTimeout(() => {

                    // location.reload();
                }, 1000);
            }
        });
    </script>
@endsection
