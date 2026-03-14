@extends('frontend.layouts.app', ['title' => ''])

@section('content')
    <!-- start section -->
    <div class="page-header">
        <div class="container">
            <div class="row" style="display: block;">
                <div class="heading">
                    <h2 class="title  text-center">{{ $category->title }}</h2>
                    <span class="seprater-img">
                        <img src="{{ asset('assets/img/seprater.png') }}">
                    </span>
                </div>
            </div>
        </div><!-- End .container -->
    </div><!-- End .page-header -->
    <nav aria-label="breadcrumb" class="breadcrumb-nav">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                {{-- @dd( $category->parentObj) --}}
                @if ($category->parent && $category->parentObj->parentObj)
                    <li class="breadcrumb-item">
                        <a href="{{ route('getCatList', $category->parentObj->parentObj->slug) }}">
                            {{ $category->parentObj->parentObj->slug }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('getCatList', $category->parentObj->slug) }}">
                            {{ $category->parentObj->slug }}
                        </a>
                    </li>
                @elseif($category->parentObj)
                    <li class="breadcrumb-item">
                        <a href="{{ route('getCatList', $category->parentObj->slug) }}">
                            {{ $category->parentObj->slug }}
                        </a>
                    </li>
                @else
                    {{-- <li class="breadcrumb-item"><a href="{{ route('shop.page.index') }}">Shop</a></li> --}}
                @endif

                <li class="breadcrumb-item active" aria-current="page">{{ $category->title }}</li>
            </ol>
        </div><!-- End .container -->
    </nav><!-- End .breadcrumb-nav -->
    <!-- end section -->

    <div class="cat-list-sl">
        <div class="container">
            {{-- @dd($categories, $category->products ) --}}
            @if ($categories->isNotEmpty())
                <div class="row">
                    @forelse ($categories as $category)
                        {{-- @dd($category->parentObj,$category) --}}
                        <div class="col-md-3">
                            <div class="container-full-s">
                                <div class="pro-ct-list">
                                    <div class="items-box">
                                        <a href="{{ route('getCatList', $category->fullRouteParams()) }}">


                                            <div class="items">
                                                <img src="{{ asset($category->image) }}">
                                            </div>
                                            <h3>{{ $category->title }}</h3>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                    @endforelse

                </div>
            @endif
            <div class="row">
                {{-- @dd($category->sub_categories,$category->parentObj,$categories->where('slug', $category->slug), $categories ) --}}
                @if (!$categories->where('slug', $category->slug)->first())
                    <!-- start section -->
                    @forelse ($category->products->take(10) as $product)
                        <!-- start shop item -->
                        @if ($product->category != null)
                            @php
                                $isInWishlist = false;
                                $sessionWishlistData = session('wishlist') ?? [];
                                if (auth()->user()) {
                                    $isInWishlist = $wishlist->contains('product_id', $product->product_id);
                                } else {
                                    foreach ($sessionWishlistData as $key => $value) {
                                        // dd($value['product_id'],$product->product_id);
                                        if ($value['product_id'] == $product->product_id) {
                                            $isInWishlist = true;
                                        }
                                    }
                                }
                            @endphp
                            {{-- @dd( session('wishlist')) --}}
                            @php
                                $mrp = $product->variations[0]->mrp ?? $product->base_mrp;
                                $price = $product->variations[0]->price ?? $product->base_price;
                                $percentOff = $mrp > 0 && $price > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
                            @endphp

                            <div class="col-md-3">
                                <div class="container-full-sf ">
                                    <div class="container-full-sf-in">
                                        <a data-id="{{ $product->product_id }}" onclick="addTowishListClass(this)"
                                            title="{{ $isInWishlist ? ' Added to wishlist' : ' Add to wishlist' }}"
                                            class="wishlist-link-product  {{ $isInWishlist ? 'remove-from-wishlist liked' : 'add-to-wishlist' }}"><svg
                                                class="wish-icon" title="Like Safebox SVG File" width="21"
                                                height="21" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path
                                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                                </path>
                                            </svg></a>
                                        <a href="{{ route('single.product.view', $product->slug) }}">
                                            <div class="prod-div">
                                                <img class="product-img"
                                                    src="{{ asset('storage/' . $product->product_image) }}">
                                            </div>
                                            <h3 class="product-title">{{ $product->product_name }}</h3>
                                        </a>
                                        <div class="prod-price">
                                            @if ($price == 0)
                                                <span class="new-price">
                                                    ₹{{ number_format($price == 0 ? $mrp : $price, 0, '.', ',') }}</span>
                                            @else
                                                <span class="old-price"> ₹{{ number_format($mrp, 0, '.', ',') }}</span>
                                                <span class="new-price">
                                                    ₹{{ number_format($price == 0 ? $mrp : $price, 0, '.', ',') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- @dd($product->variation) --}}
                                    @if ($product->variation->stock > 0)
                                        <div class="cart-box-btn d-flex gap-10 align-items-center justify-content-center"
                                            data-product-id="{{ $product->product_id }}">
                                            <a class="add-to-cart-home add-to-cart">
                                                <span>Add to cart</span>
                                            </a>
                                            <div class="product-details-quantity">

                                                @php
                                                    $cartQty = 1;

                                                    if (auth()->check()) {
                                                        // DB cart
                                                        $cartQty = $product->cart->quantity ?? 1;
                                                    } else {
                                                        // Session cart
                                                        $cartKey = $product->product_id . '-' . $product->variation->id;
                                                        $cartQty = session("cart.$cartKey.quantity", 1);
                                                    }
                                                @endphp
                                                <input type="number" class="form-control qty product_quantity"
                                                    value="{{ $cartQty }}" min="1" max="10"
                                                    step="1" data-decimals="0" required>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex gap-10 align-items-center justify-content-center">
                                            <a class="add-to-cart-home disabled">
                                                <span>Out of Stock</span>
                                            </a>
                                        </div>
                                    @endif


                                </div>
                            </div>
                        @endif

                    @empty

                        <div class="container">

                            <h5 class="text-center">No Product Found</h5>
                        </div>
                    @endforelse
                @else
                    {{-- <div class="container">

                        <h5 class="text-center">No Product Found</h5>
                    </div> --}}
                @endif


            </div>
        </div>
        <img src="{{ asset('assets/img/bg-feature2.png') }}" class="bg-feture2-sec">
    </div>
@endsection
