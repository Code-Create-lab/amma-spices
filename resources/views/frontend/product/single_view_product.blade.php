@extends('frontend.layouts.app', ['title' => ''])



@section('content')
    @php
        // $isInWishlist = $wishlist->contains('product_id', $response['id']);
        $isInWishlist = false;
        $sessionWishlistData = session('wishlist') ?? [];
        if (auth()->user()) {
            $isInWishlist = $wishlist->contains('product_id', $response['id']);
        } else {
            foreach ($sessionWishlistData as $key => $value) {
                // dd($value['product_id'],$response['id']);
                if ($value['product_id'] == $response['id']) {
                    $isInWishlist = true;
                }
            }
        }
    @endphp
    <main class="main">
        <nav aria-label="breadcrumb" class="breadcrumb-nav border-0 mb-0">
            <div class="container d-flex align-items-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>


                    @php
                        $previousUrl = url()->previous();
                        $currentUrl = url()->current();

                        // Define route-to-label mapping
                        $routeLabels = [
                            route('checkout.index') => 'Checkout',
                            route('getCartItems') => 'Shopping Cart',
                        ];

                        // Check if previous URL matches any of our defined routes
                        $showPrevious = false;
                        $previousLabel = '';
                        $previousLink = '';

                        foreach ($routeLabels as $url => $label) {
                            if (strpos($previousUrl, $url) !== false || $previousUrl === $url) {
                                $showPrevious = true;
                                $previousLabel = $label;
                                $previousLink = $url;
                                break;
                            }
                        }

                        // Check if coming from shop/category page and preserve full URL with filters
                        $categoryPagePattern = url('categories/' . $response['category']->slug);
                        if (
                            !$showPrevious &&
                            strpos($previousUrl, $categoryPagePattern) !== false &&
                            $previousUrl !== $currentUrl
                        ) {
                            $showPrevious = true;
                            $previousLabel = $response['category']->title;
                            $previousLink = $previousUrl; // Use full previous URL to preserve query params
                        }
                    @endphp
                    @if ($showPrevious && $previousUrl !== $currentUrl)
                        <li class="breadcrumb-item"><a href="{{ $previousLink }}">{{ $previousLabel }}</a></li>
                    @else
                        {{-- Default to category page --}}
                        <li class="breadcrumb-item"><a
                                href="{{ url('categories/' . $response['category']->slug) }}">{{ $response['category']->title }}</a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active">{{ $response['name'] }}</li>
                </ol>


            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->

        <div class="page-content">
            <div class="container">
                <div class="product-details-top">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="product-gallery product-gallery-vertical">
                                <div class="row">
                                    <div class="product-images-container">
                                        {{-- Main Image Carousel --}}
                                        <div class="owl-carousel owl-theme product-img-list owl-simple" data-toggle="owl" data-owl-options='{"nav": false, "autoplay":true, "autoplayTimeout":3000, "dots": false, "loop": true}'>
                                            @foreach ($response['images'] as $index => $media)
                                                @php
                                                    $extension = pathinfo($media['image'], PATHINFO_EXTENSION);
                                                    $videoExtensions = ['mp4', 'webm', 'ogg', 'mov'];
                                                    $isVideo = in_array(strtolower($extension), $videoExtensions);
                                                    $mediaSrc = asset('storage/' . $media['image']);
                                                @endphp

                                                <div class="items-list">
                                                    @if ($isVideo)
                                                        <video controls style="width: 100%; height: auto;">
                                                            <source src="{{ $mediaSrc }}"
                                                                type="video/{{ $extension }}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    @else
                                                        <img src="{{ $mediaSrc }}"
                                                            alt="Product Image {{ $index + 1 }}">
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Thumbnail Carousel --}}
                                        <div class="owl-carousel owl-theme product-thumb-list">
                                            @foreach ($response['images'] as $index => $media)
                                                @php
                                                    $extension = pathinfo($media['image'], PATHINFO_EXTENSION);
                                                    $videoExtensions = ['mp4', 'webm', 'ogg', 'mov'];
                                                    $isVideo = in_array(strtolower($extension), $videoExtensions);
                                                    $mediaSrc = asset('storage/' . $media['image']);
                                                @endphp

                                                <div class="thumb-item" data-index="{{ $index }}">
                                                    @if ($isVideo)
                                                        <div class="video-thumb">
                                                            <video
                                                                style="width: 100%; height: 80px; object-fit: cover; pointer-events: none;">
                                                                <source src="{{ $mediaSrc }}"
                                                                    type="video/{{ $extension }}">
                                                            </video>
                                                            <div class="play-icon">
                                                                <i class="icon-play"></i>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <img src="{{ $mediaSrc }}"
                                                            alt="Thumbnail {{ $index + 1 }}">
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div><!-- End .row -->
                            </div><!-- End .product-gallery -->
                        </div><!-- End .col-md-6 -->

                        <div class="col-md-6">
                            <div class="product-details">
                                <h1 class="product-title">{{ $response['name'] }}</h1>
                                <!-- End .product-title -->

                                @php
                                    $mrp = $response['variations'][0]['mrp'] ?? $response['base_mrp'];
                                    $price = $response['variations'][0]['price'] ?? $response['base_price'];

                                    $percentOff = $mrp > 0 && $price > 0 ? (($mrp - $price) / $mrp) * 100 : 0;
                                @endphp
                                <script>
                                    window.variationsData = @json($response['variations'] ?? []);
                                </script>


                                <div class="ratings-container">
                                    @if ($total_reviews > 0)
                                        @php
                                            $avg_rating = $avg_rating ?? 0;
                                            $ratingPercent = ($avg_rating / 5) * 100;
                                        @endphp

                                        <div class="ratings">
                                            <div class="ratings-val" style="width: {{ $ratingPercent }}%;"></div>
                                            <!-- End .ratings-val -->
                                        </div><!-- End .ratings -->
                                        <a class="ratings-text" id="review-link">
                                            ( {{ $total_reviews }} Reviews )
                                        </a>
                                    @endif
                                </div><!-- End .rating-container -->

                                <div class="product-price">
                                    {{-- @dd($response['variations'][0]['price'] ?? $response['base_price'] ) --}}
                                    {{-- @if ($response['variations'][0]['price'] ?? $response['base_price'] == 0)
                                        <span class="new-price">
                                            ₹{{ number_format($price, 0, '.', ',') }}</span>
                                    @else --}}
                                    @if ($price != 0)
                                        <span class="old-price 1"> ₹{{ $mrp }}</span>
                                    @endif

                                    <span class="new-price">
                                        ₹{{ number_format($price == 0 ? $mrp : $price, 0, '.', ',') }}</span>
                                    @if ($price != 0)
                                        <span class="discount">{{ (int) $percentOff }}%off</span>
                                    @endif
                                    {{-- @endif --}}

                                </div><!-- End .product-price -->
                                <span class="gst-n-text">(GST Included)</span>

                                @if (!empty($response['variation_attributes']))
                                    @foreach ($response['variation_attributes'] as $attributeName => $attributeValues)
                                        @php
                                            // Normalize attribute names
                                            $sizeVariants = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Size'];
                                            $isSize = in_array($attributeName, $sizeVariants);
                                            $displayName = $isSize ? 'Size' : 'Color';
                                            $attributeType = $isSize ? 'Size' : 'Color';
                                        @endphp

                                        <div
                                            class="d-flex align-items-center {{ $attributeType === 'Color' ? 'mb-20px color-options' : 'mb-35px size-options' }}">
                                            <label
                                                class="text-dark-gray alt-font me-15px fw-500">{{ $attributeType }}</label>

                                            @if ($attributeType === 'Color')
                                                {{-- Color Options --}}
                                                <ul class="shop-color mb-0">
                                                    @foreach ($attributeValues as $index => $attribute)
                                                        @php
                                                            // Check if this color option has any stock
                                                            $hasStock = false;
                                                            if (!empty($response['variations'])) {
                                                                foreach ($response['variations'] as $variation) {
                                                                    if ($variation['stock'] > 0) {
                                                                        foreach ($variation['attributes'] as $attr) {
                                                                            if (
                                                                                $attr['name'] === 'Color' &&
                                                                                strtolower($attr['value']) ===
                                                                                    strtolower($attribute['value'])
                                                                            ) {
                                                                                $hasStock = true;
                                                                                break 2;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            $stockClass = $hasStock ? '' : 'out-of-stock-variation';
                                                            $colorValue = strtolower(
                                                                str_replace(' ', '', $attribute['value']),
                                                            );
                                                            $uniqueId = 'color-' . ($index + 1);
                                                        @endphp
                                                        <li>
                                                            <input class="d-none " type="radio" id="{{ $uniqueId }}"
                                                                name="color" {{ $index === 0 ? 'checked' : '' }}>
                                                            <label for="{{ $uniqueId }}">
                                                                <span {{ !$hasStock ? 'title=Out-of-Stock' : '' }}
                                                                    data-attr="{{ $attributeType }}"
                                                                    data-value="{{ $colorValue }}"
                                                                    data-id="{{ $attribute['id'] }}"
                                                                    class="attribute-btn {{ $index === 0 ? 'selected attribute_seletion' : '' }} {{ $stockClass }}"
                                                                    style="background-color: {{ $colorValue }};"></span>
                                                            </label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {{-- Size Options --}}
                                                <ul class="shop-size mb-0">
                                                    @foreach ($attributeValues as $index => $attribute)
                                                        @php
                                                            // Check if this size option has any stock
                                                            $hasStock = false;
                                                            if (!empty($response['variations'])) {
                                                                foreach ($response['variations'] as $variation) {
                                                                    if ($variation['stock'] > 0) {
                                                                        foreach ($variation['attributes'] as $attr) {
                                                                            if (
                                                                                $attr['name'] === 'Size' &&
                                                                                strtolower($attr['value']) ===
                                                                                    strtolower($attribute['value'])
                                                                            ) {
                                                                                $hasStock = true;
                                                                                break 2;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            $stockClass = $hasStock ? '' : 'out-of-stock-variation';
                                                            $sizeValue = strtolower(
                                                                str_replace(' ', '', $attribute['value']),
                                                            );
                                                            $uniqueId = 'size-' . ($index + 1);
                                                        @endphp
                                                        <li>
                                                            <input class="d-none " type="radio" id="{{ $uniqueId }}"
                                                                name="size" {{ $index === 0 ? 'checked' : '' }}>
                                                            <label for="{{ $uniqueId }}">
                                                                <span {{ !$hasStock ? 'title=Out-of-Stock' : '' }}
                                                                    data-attr="{{ $attributeType }}"
                                                                    data-value="{{ $sizeValue }}"
                                                                    data-id="{{ $attribute['id'] }}"
                                                                    class=" attribute-btn {{ $index === 0 ? 'selected attribute_seletion' : '' }} {{ $stockClass }}">{{ strtoupper($attribute['value']) }}</span>
                                                            </label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                <div class="product-content">
                                    {!! $response['description'] !!}
                                </div><!-- End .product-content -->


                                <div class="details-filter-row details-row-size">
                                    <label for="qty">Qty:</label>
                                    <div class="product-details-quantity">
                                        <input type="number" id="qty" class="form-control qty" value="1"
                                            min="1" max="10" step="1" data-decimals="0" required>
                                    </div><!-- End .product-details-quantity -->
                                </div><!-- End .details-filter-row -->


                                <div class="product-details-action">
                                    <a class="btn-product btn-cart add-to-cartJS"><span>add to cart</span></a>
                                    {{-- <a class="btn-product btn-cart buy_nowJS"><span>Buy now</span></a> --}}

                                    <div class="details-action-wrapper">
                                        <a data-id="{{ $response['id'] }}"
                                            class="{{ $isInWishlist ? 'remove-from-wishlist liked' : 'add-to-wishlist' }} wishlist  btn-product btn-wishlist"
                                            title="Wishlist">
                                            <svg class="wish-icon" title="Like Safebox SVG File" width="21"
                                                height="21" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path
                                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                                </path>
                                            </svg>
                                        </a>

                                    </div><!-- End .details-action-wrapper -->
                                </div><!-- End .product-details-action -->


                            </div><!-- End .product-details -->
                        </div><!-- End .col-md-6 -->
                    </div><!-- End .row -->
                </div><!-- End .product-details-top -->

                <div class="product-details-tab">

                    <ul class="nav nav-pills justify-content-center" role="tablist">
                        {{-- <li class="nav-item">
                            <a class="nav-link active" id="product-desc-link" data-toggle="tab" href="#product-desc-tab"
                                role="tab" aria-controls="product-desc-tab" aria-selected="true">Description</a>
                        </li> --}}
                        {{-- @if ($response['info'] != '') --}}
                        <li class="nav-item">
                            <a class="nav-link active" id="product-info-link" data-toggle="tab" href="#product-info-tab"
                                role="tab" aria-controls="product-info-tab" aria-selected="false">Description</a>
                        </li>
                        {{-- @endif --}}
                        {{-- @if ($response['shipping'] != '')
                            <li class="nav-item">
                                <a class="nav-link" id="product-shipping-link" data-toggle="tab"
                                    href="#product-shipping-tab" role="tab" aria-controls="product-shipping-tab"
                                    aria-selected="false">Shipping &
                                    Returns</a>
                            </li>
                        @endif --}}
                        <li class="nav-item">
                            <a class="nav-link" id="product-review-link" data-toggle="tab" href="#product-review-tab"
                                role="tab" aria-controls="product-review-tab" aria-selected="false">Reviews
                                ({{ $total_reviews }})</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <img src="https://bodhi-bliss.yugasa.org/assets/img/bg-feture.png" class="bg-feture-sec">
                        {{-- <div class="tab-pane fade show active" id="product-desc-tab" role="tabpanel"
                            aria-labelledby="product-desc-link">
                            <div class="product-desc-content">
                                {!! $response['description'] !!}
                            </div><!-- End .product-desc-content -->
                        </div><!-- .End .tab-pane --> --}}
                        <div class="tab-pane fade  show active" id="product-info-tab" role="tabpanel"
                            aria-labelledby="product-info-link">
                            <div class="product-desc-content">
                                {!! $response['info'] !!}
                            </div><!-- End .product-desc-content -->
                        </div><!-- .End .tab-pane -->
                        {{-- <div class="tab-pane fade" id="product-shipping-tab" role="tabpanel"
                            aria-labelledby="product-shipping-link">
                            <div class="product-desc-content">
                                {{ $response['shipping'] }} </div><!-- End .product-desc-content -->
                        </div><!-- .End .tab-pane --> --}}
                        <div class="tab-pane fade" id="product-review-tab" role="tabpanel"
                            aria-labelledby="product-review-link">
                            <div class="reviews">
                                <h3>Reviews ({{ count($top_reviews) }})</h3>
                                @foreach ($top_reviews as $review)
                                    <div class="review">
                                        <div class="row no-gutters">
                                            <div class="col-auto">
                                                <h4><a href="#">{{ $review->user?->name }}</a></h4>
                                                <div class="ratings-container">
                                                    <div class="ratings">
                                                        <div class="ratings-val" style="width: 80%;"></div>
                                                        <!-- End .ratings-val -->
                                                    </div><!-- End .ratings -->
                                                </div><!-- End .rating-container -->
                                                <span
                                                    class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                                            </div><!-- End .col -->
                                            <div class="col">
                                                {{-- <h4>{{$review->title}}</h4> --}}

                                                <div class="review-content">
                                                    <p>{{ $review->comment }}</p>
                                                </div><!-- End .review-content -->


                                            </div><!-- End .col-auto -->
                                        </div><!-- End .row -->
                                    </div><!-- End .review -->
                                @endforeach

                            </div><!-- End .reviews -->
                        </div><!-- .End .tab-pane -->
                    </div><!-- End .tab-content -->
                </div><!-- End .product-details-tab -->





            </div><!-- End .container -->
            @if ($related_products->where('product_id', '!=', $response['id'])->isNotEmpty())
                <div class="featured-list-slider realted_pro">
                    <div class="container">
                        <div class="row" style="display: block;">
                            <div class="heading">
                                <h2 class="title  text-center">Related products</h2>
                                <span class="seprater-img"><img src="{{ asset('assets/img/seprater.png') }}"></span>
                            </div>
                        </div>
                        <div class="row">
                            @if ($related_products->isNotEmpty())
                                @foreach ($related_products->where('product_id', '!=', $response['id'])->reverse() as $product)
                                    @php
                                        $isInWishlistLoopRelatedProd = $wishlist->contains(
                                            'product_id',
                                            $product->product_id,
                                        );
                                    @endphp
                                    @if ($product->variation != null)
                                        <div class="col-md-3" data-product-id="{{ $product->product_id }}">
                                            <div class="container-full-sf">
                                                <div class="container-full-sf-in">
                                                    <a data-id="{{ $product->product_id }}"
                                                        title="{{ $isInWishlistLoopRelatedProd ? ' Added to wishlist' : ' Add to wishlist' }}"
                                                        class=" {{ $isInWishlistLoopRelatedProd ? 'remove-from-wishlist liked' : 'add-to-wishlist' }} wishlist-link-product"><svg
                                                            class="wish-icon" title="Like Safebox SVG File"
                                                            width="21" height="21" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path
                                                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                                            </path>
                                                        </svg></a>
                                                    <a href="{{ route('single.product.view', $product->slug) }}">
                                                        <div class="prod-div">
                                                            <img class="product-img"
                                                                src="{{ asset('storage/' . $product->product_image) }}"
                                                                alt="{{ $product->product_name }}">
                                                        </div>
                                                        <h3 class="product-title">{{ $product->product_name }}</h3>
                                                    </a>
                                                    <div class="prod-price">

                                                        @if ($price != 0)
                                                            <span class="old-price">
                                                                ₹{{ number_format($product->base_mrp, 0) }}</span>
                                                        @endif

                                                        <span class="new-price">
                                                            ₹{{ number_format($product->base_price == 0 ? $product->base_mrp : $product->base_price, 0) }}</span>
                                                    </div>
                                                </div>

                                                @if ($product->variation->stock > 0)
                                                    <div class="cart-box-btn d-flex gap-10 align-items-center justify-content-center"
                                                        data-product-id="{{ $product->product_id }}">
                                                        <a class="add-to-cart-home add-to-cart">
                                                            <span>Add to cart</span>
                                                        </a>
                                                        <div class="product-details-quantity">
                                                            <input type="number"
                                                                class="form-control qty product_quantity" value="1"
                                                                min="1" max="10" step="1"
                                                                data-decimals="0" required>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="cart-box-btn d-flex gap-10 align-items-center justify-content-center">
                                                        <a class="add-to-cart-home disabled">
                                                            <span>Out of Stock</span>
                                                        </a>
                                                    </div>
                                                @endif
                                                {{-- @if ($product->variation->stock > 0)
                                                    <a class="add-to-cart-home add-to-cartJS"><span>Add To Cart</span></a>
                                                @else
                                                    <a class="add-to-cart-home add-to-cartJS"><span>Out of stock</span></a>
                                                @endif --}}
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                        </div>
                    </div>
                    <img src="{{ asset('assets/img/related_img.png') }}" class="bg-feture8-sec">
                </div>
            @endif
        </div>
        </div><!-- End .page-content -->
    </main><!-- End .main -->


    <script src="{{ asset('assets/js/bootstrap-input-spinner.js') }}"></script>
    <script>
        $(document).ready(function() {


            $('.copy-coupon-code').on('click', function() {
                const code = $(this).data('coupon-code');
                const span = $(this).find('.button-text');

                span.text("Copying...");

                navigator.clipboard.writeText(code).then(() => {
                    span.text("Copied!");
                    setTimeout(() => {
                        span.text("Copy");
                    }, 1500);
                });
            });


            $('.related-product-carousel').owlCarousel({
                loop: false,
                margin: 30,
                nav: true,
                dots: false,
                autoplay: false,
                autoplayTimeout: 4000,
                autoplayHoverPause: true,
                navText: ['<i class="feather icon-feather-arrow-left"></i>',
                    '<i class="feather icon-feather-arrow-right"></i>'
                ],
                responsive: {
                    0: {
                        items: 1
                    },
                    576: {
                        items: 2
                    },
                    768: {
                        items: 3
                    },
                    992: {
                        items: 4
                    },
                    1200: {
                        items: 5
                    }
                }
            });

            $('.recently-viewed-product-carousel').owlCarousel({
                loop: false,
                margin: 30,
                nav: true,
                dots: false,
                autoplay: false,
                autoplayTimeout: 4000,
                autoplayHoverPause: true,
                navText: ['<i class="feather icon-feather-arrow-left"></i>',
                    '<i class="feather icon-feather-arrow-right"></i>'
                ],
                responsive: {
                    0: {
                        items: 1
                    },
                    576: {
                        items: 2
                    },
                    768: {
                        items: 3
                    },
                    992: {
                        items: 4
                    },
                    1200: {
                        items: 5
                    }
                }
            });
            var variations = window.variationsData;
            if (!variations) {
                console.error('Variations not found!');
                return;
            }
            console.log(variations, 'variations');

            $('.product-gallery-item').on('click', function(e) {
                e.preventDefault();

                // Get current main image data
                var currentMainSrc = $('#product-zoom').attr('src');
                var currentMainZoom = $('#product-zoom').attr('data-zoom-image');

                // Get clicked thumbnail data
                var newImageSrc = $(this).attr('data-image');
                var newZoomImage = $(this).attr('data-zoom-image');

                // Update main product image with clicked thumbnail data
                $('#product-zoom').attr('src', newImageSrc).attr('data-zoom-image', newZoomImage);

                // Update clicked thumbnail with previous main image data
                $(this).attr('data-image', currentMainSrc).attr('data-zoom-image', currentMainZoom);
                $(this).find('img').attr('src', currentMainSrc);

                // Update active class
                $('.product-gallery-item').removeClass('active');
                $(this).addClass('active');
            });

            // Quantity Increase / Decrease
            $(".cart-plus-btn-single").click(function() {
                let $input = $(this).siblings("input.qty");
                let currentVal = parseInt($input.val()) || 0;
                $input.val(currentVal + 1).change();
            });

            $('.cart-minus-btn-single').click(function() {
                let $qty = $(this).siblings('.qty');
                let currentVal = parseInt($qty.val()) || 1;
                if (currentVal > 1) {
                    $qty.val(currentVal - 1);
                } else {
                    $qty.val(1); // Reset to 1 if it's 1 or less
                }
            });

            // Main Variables
            var selectedAttributes = {};
            var selectedVariationId = null;
            var selectedPrice = null;
            var selectedMrp = null;


            // Preselect First Size and Color (if needed)
            const firstSizeBtn = $('[data-attr="Size"]:visible').first();
            const firstColorBtn = $('[data-attr="Color"]:visible').first();

            if (firstSizeBtn.length) {
                firstSizeBtn.addClass('attribute_seletion').removeClass('btn-outline-primary selected');
                $('.size-btn-attribute').addClass('btn-outline-primary');
                $('.attribute_seletion').removeClass('btn-outline-primary');
                selectedAttributes['Size'] = {
                    id: firstSizeBtn.data('id'),
                    value: firstSizeBtn.data('value').toLowerCase().replace(/\s+/g, '')
                };
            }



            if (firstColorBtn.length) {


                firstColorBtn.addClass('attribute_seletion').removeClass('btn-outline-primary selected');
                selectedAttributes['Color'] = {
                    id: firstColorBtn.data('id'),
                    value: firstColorBtn.data('value').toLowerCase().replace(/\s+/g, '')
                };
            }

            console.log(firstColorBtn, 'firstColorBtn', selectedAttributes);
            $('[data-attr="Balck"]').each(function() {
                const btnColor = $(this).data('value').toLowerCase().replace(/\s+/g, '');

                console.log("btnColor", btnColor);
                // if (availableColors.includes(btnColor)) {

                $(this).css('background-color', btnColor);
                $(this).html(' ');
                $(this).show();
                // } else {
                //     $(this).html(' ');
                //     $(this).hide();
                // }
            });
            // Call filter colors based on selected size after preselect
            filterColorsBasedOnSize();
            checkSelection();
            updateProductDetails();

            // Attribute Button Click
            $(document).on('click', '.attribute-btn', function() {
                var attrName = $(this).data('attr');
                var attrValue = $(this).data('value');
                var attrId = $(this).data('id');

                $(this).closest('.attribute-options').find('.attribute-btn')
                    .removeClass('attribute_seletion btn-dark')
                    .addClass('btn-outline-primary');
                $(this).addClass('attribute_seletion').removeClass('btn-outline-primary');

                selectedAttributes[attrName] = {
                    id: attrId,
                    value: attrValue.toLowerCase().replace(/\s+/g, '')
                };

                if (attrName === 'Size') {
                    filterColorsBasedOnSize();
                    autoSelectFirstVisibleColor();
                }

                updateProductDetails();
                checkSelection();
            });

            function filterColorsBasedOnSize() {
                var size = selectedAttributes['Size']?.value;
                if (!size) return;

                let availableColors = [];



                variations.forEach(variation => {
                    const sizeAttr = variation.attributes.find(attr => attr.name == 'Size');
                    const colorAttr = variation.attributes_options.Color || variation.attributes_options
                        .colour; // This is already the color object

                    console.log(colorAttr, 'colorAttr', variation.attributes_options);

                    if (sizeAttr && colorAttr &&
                        sizeAttr.value.toLowerCase().replace(/\s+/g, '') === size) {

                        // Use colorAttr.name instead of colorAttr.value
                        const normalizedColor = colorAttr.name.toLowerCase().replace(/\s+/g, '');

                        console.log(availableColors, 'normalizedColor', normalizedColor, colorAttr);
                        if (!availableColors.includes(normalizedColor)) {
                            availableColors.push(normalizedColor);
                        }
                    }
                });

                $('[data-attr="colour"]').each(function() {
                    const btnColor = $(this).data('value').toLowerCase().replace(/\s+/g, '');

                    console.log("btnColor", btnColor);
                    if (availableColors.includes(btnColor)) {

                        $(this).css('background-color', btnColor);
                        $(this).html(' ');
                        $(this).show();
                    } else {
                        $(this).html(' ');
                        $(this).hide();
                    }
                });

            }

            function autoSelectFirstVisibleColor() {
                const firstVisibleColor = $('[data-attr="Color"]:visible').first();
                if (firstVisibleColor.length) {
                    firstVisibleColor.trigger('click'); // Simulate click
                }
            }

            function updateProductDetails() {
                var foundVariation = variations.find(function(variation) {
                    return variation.attributes.every(attr => {
                        var selectedAttr = selectedAttributes[attr.name];
                        if (!selectedAttr) return false;
                        return selectedAttr.value === attr.value.toLowerCase().replace(/\s+/g, '');
                    });
                });
                console.log('foundVariation', variations, foundVariation)
                if (foundVariation?.stock == 0) {

                    $('.add-to-cartJS').html(
                        '<span> <i class="feather icon-feather-shopping-bag"></i><span class="quick-view-text button-text">Out of stock</span> </span>'
                    );
                    $('.add-to-cartJS').attr('disabled', true);
                    // $('.add-to-cartJS').hide();
                    $('.out-of-stock').show();

                    // $('[data--value=""]')

                } else {

                    $('.add-to-cartJS').html(
                        '<span> <i class="feather icon-feather-shopping-bag"></i><span class="quick-view-text button-text">Add to cart</span> </span> '
                    );
                    $('.add-to-cartJS').attr('disabled', false);
                    $('.add-to-cartJS').show();
                    $('.out-of-stock').hide();


                }

                console.log("oundVariation", foundVariation);
                if (foundVariation && foundVariation.image) {
                    console.log("foundVariation", foundVariation, "{{ $response['image'] }}");
                    selectedVariationId = foundVariation.id;
                    selectedPrice = foundVariation.price;
                    selectedMrp = foundVariation.mrp;
                    var percentOff = (selectedMrp > 0 && selectedPrice > 0) ?
                        Math.round(((selectedMrp - selectedPrice) / selectedMrp) * 100) : 0;

                    var imageUrl = `{{ asset('storage') }}/${foundVariation.image}`;
                    $('.product-image').attr('src', imageUrl);
                    $('.product-image').attr('data-zoom-image', imageUrl);
                    $('#product-price').html(
                        `<del class="text-medium-gray me-10px fw-400">₹${selectedMrp}</del>₹${selectedPrice}`);
                    $('.price-regular').text(`₹${selectedMrp}`);

                    console.log("percent-off", selectedPrice, selectedMrp)
                    $('#product-price-mrp').html(`MRP<del>₹${selectedMrp.toLocaleString('en-IN')}</del>`);
                    $('#percentage-span').text(`${percentOff}% off`);


                    // $(document).on('click', '.swiper-slide', function() {
                    // Get clicked slide's image src
                    var imgSrc = $(this).find('.swiper-slide-active').attr('src');

                    // Update main image
                    $('.slider_images .swiper-slide-active img').attr('src', imageUrl);
                    // });
                    console.log(".slider_images .swiper-slide-active img", imgSrc)

                } else {
                    selectedVariationId = foundVariation.id;;
                    selectedPrice = foundVariation.price;
                    selectedMrp = foundVariation.mrp;
                    let defaultPrice = "{{ $response['base_price'] }}";
                    let defaultMrp = "{{ $response['base_mrp'] }}";
                    var percentOff = (selectedMrp > 0 && selectedPrice > 0) ?
                        Math.round(((selectedMrp - selectedPrice) / selectedMrp) * 100) : 0;

                    $('.product-image').attr('src', `{{ asset('storage/' . $response['image']) }}`);
                    $('#product-price').html(
                        `<del class="text-medium-gray me-10px fw-400">₹${selectedMrp}</del>₹${selectedPrice}`);
                    $('.price-regular').text(`₹${selectedMrp}`);

                    console.log("percent-off", selectedPrice, selectedMrp)
                    $('#product-price-mrp').html(`MRP<del>₹${selectedMrp.toLocaleString('en-IN')}</del>`);
                    $('#percentage-span').text(`${percentOff}% off`);

                    var imgSrc = $(this).find('.swiper-slide-active').attr('src');

                    // Update main image
                    $('.slider_images .swiper-slide-active img').attr('src', imageUrl);
                }
            }

            function checkSelection() {
                var totalAttributes = $('.attribute-options').length;
                if (Object.keys(selectedAttributes).length === totalAttributes) {
                    $('.add-to-cartJS').prop('disabled', false);
                } else {
                    $('.add-to-cartJS').prop('disabled', true);
                }
            }

            // Add to Cart Click
            $('.add-to-cartJS').click(function() {
                // if (selectedVariationId) {

                console.log("add-to-cartJS", selectedAttributes)
                var cartData = {
                    product_id: '{{ $response['id'] }}',
                    variation_id: selectedVariationId || "",
                    attributes: Object.values(selectedAttributes),
                    quantity: $('.qty').val() ?? 1
                };

                $.ajax({
                    url: '{{ route('addToCart') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: cartData,
                    success: function(response) {
                        if (response.success) {
                            $('#cart-count-span').html('(' + response.cart_count +
                                ' Items)');
                            $('.cart-total-span').html(response.cart_total);

                            if (response.cart_count > 0) {
                                if ($('#cart_count_header').length > 0) {
                                    $('#cart_count_header').html(response.cart_count)
                                        .show();
                                } else {
                                    $('a.cart__btn').append(
                                        '<ins id="cart_count_header"></ins>');
                                    $('#cart_count_header').html(response.cart_count)
                                        .show();
                                }
                            } else {
                                $('#cart_count_header').hide();
                            }

                            toastr.options = {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',

                                timeOut: 800, // 👈 visible for 1.5 seconds
                                extendedTimeOut: 500, // 👈 after hover
                                showDuration: 200, // 👈 fade in speed
                                hideDuration: 200, // 👈 fade out speed
                            };
                            toastr.success(response.message);



                            const productId = $(this).data('id');

                            // This sends a global event to all mounted Livewire components
                            window.dispatchEvent(
                                new CustomEvent('cart-updated')
                            );
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(error) {
                        toastr.error(error.responseJSON?.message ||
                            'Something went wrong.');
                    }
                });
                // } else {
                //     toastr.error("Please select all attributes.");
                // }
            });

            // Image Change on Hover
            $(document).on('mouseenter', '.thumbnail', function() {
                changeImage(this);
            });

            function changeImage(elem) {
                const mainImage = document.getElementById('mainImage');
                mainImage.src = elem.src;
            }

        });

        $("button.size-btn-attribute").css({
            "border": "0.1rem solid rgb(243 240 240)"
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize main swiper after DOM is ready
            setTimeout(() => {
                // Get all video elements in main slider
                const mainVideos = document.querySelectorAll('.main-slider-video');

                // Force load videos
                mainVideos.forEach(video => {
                    video.load();
                });

                // Handle swiper slide change to pause videos
                const mainSwiper = document.querySelector('.product-image-slider');
                if (mainSwiper && mainSwiper.swiper) {
                    mainSwiper.swiper.on('slideChange', function() {
                        // Pause all videos
                        mainVideos.forEach(video => {
                            video.pause();
                        });
                    });
                }

                // Generate video thumbnails dynamically (optional)
                generateVideoThumbs();
            }, 500);
        });

        // Optional: Generate video thumbnails dynamically
        function generateVideoThumbs() {
            const thumbVideos = document.querySelectorAll('.video-thumb-wrapper');

            thumbVideos.forEach((wrapper, index) => {
                // You can add dynamic thumbnail generation here if needed
                // For now, using static placeholder
            });
        }

        // Fix for iOS video playback
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            document.querySelectorAll('video').forEach(video => {
                video.setAttribute('playsinline', '');
                video.setAttribute('webkit-playsinline', '');
            });
        }


        $(document).ready(function() {


            // Get elements
            const qtyInput = document.getElementById('qty');
            const minusBtn = document.querySelector('.qty-minus');
            const plusBtn = document.querySelector('.qty-plus');

            // Decrease quantity
            if (minusBtn) {
                minusBtn.addEventListener('click', function() {
                    let currentValue = parseInt(qtyInput.value) || 1;
                    if (currentValue > 1) {
                        qtyInput.value = currentValue - 1;
                    }
                });
            }

            // Increase quantity
            if (plusBtn) {
                plusBtn.addEventListener('click', function() {
                    let currentValue = parseInt(qtyInput.value) || 1;
                    qtyInput.value = currentValue + 1;
                });
            }

            // Optional: Prevent non-numeric input
            if (qtyInput) {
                qtyInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value === '' || parseInt(this.value) < 1) {
                        this.value = 1;
                    }
                });
            }

            // Update total price when checkboxes change
            $('.fbt-checkbox').on('change', function() {
                updateFbtTotalPrice();
            });

            function updateFbtTotalPrice() {
                let total = 0;
                $('.fbt-product-item').each(function() {
                    const checkbox = $(this).find('.fbt-checkbox');
                    if (checkbox.is(':checked')) {
                        const price = parseFloat($(this).data('product-price')) || 0;
                        total += price;
                    }
                });
                $('.fbt-total-amount').text('₹' + total.toLocaleString('en-IN'));
            }

            // Add all checked products to cart
            $('.fbt-add-to-cart-btn').on('click', function() {
                const $button = $(this);
                const $btnText = $button.find('.btn-text');
                const $btnLoading = $button.find('.btn-loading');

                // Get all checked products
                const selectedProducts = [];
                $('.fbt-product-item').each(function() {
                    const $item = $(this);
                    const $checkbox = $item.find('.fbt-checkbox');

                    if ($checkbox.is(':checked')) {
                        selectedProducts.push({
                            product_id: $item.data('product-id'),
                            product_name: $item.data('product-name'),
                            variation_id: $item.data('variation-id') || '',
                            quantity: 1
                        });
                    }
                });

                if (selectedProducts.length === 0) {
                    toastr.warning('Please select at least one product.');
                    return;
                }

                // Disable button and show loading
                $button.prop('disabled', true);
                $btnText.hide();
                $btnLoading.show();

                // Add products sequentially
                addProductsToCart(selectedProducts, 0, $button, $btnText, $btnLoading);
            });

            function addProductsToCart(products, index, $button, $btnText, $btnLoading) {
                if (index >= products.length) {
                    // All products added successfully
                    $button.prop('disabled', false);
                    $btnText.show();
                    $btnLoading.hide();
                    toastr.success('products added to cart successfully!');

                    // Dispatch cart updated event
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                    return;
                }

                const product = products[index];
                const cartData = {
                    product_id: product.product_id,
                    variation_id: product.variation_id,
                    attributes: [],
                    quantity: product.quantity
                };

                $.ajax({
                    url: '{{ route('addToCart') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: cartData,
                    success: function(response) {
                        if (response.success) {
                            // Update cart count and total
                            $('#cart-count-span').html('(' + response.cart_count + ' Items)');
                            $('.cart-total-span').html(response.cart_total);

                            if (response.cart_count > 0) {
                                if ($('#cart_count_header').length > 0) {
                                    $('#cart_count_header').html(response.cart_count).show();
                                } else {
                                    $('a.cart__btn').append('<ins id="cart_count_header"></ins>');
                                    $('#cart_count_header').html(response.cart_count).show();
                                }
                            } else {
                                $('#cart_count_header').hide();
                            }

                            // Show individual product success message
                            if (products.length > 1) {
                                // toastr.success(product.product_name + ' added to cart');
                            }

                            // Add next product
                            addProductsToCart(products, index + 1, $button, $btnText, $btnLoading);
                        } else {
                            toastr.error(response.message || 'Failed to add ' + product.product_name);
                            // Continue with next product even if one fails
                            addProductsToCart(products, index + 1, $button, $btnText, $btnLoading);
                        }
                    },
                    error: function(error) {
                        toastr.error('Error adding ' + product.product_name);
                        // Continue with next product even if one fails
                        addProductsToCart(products, index + 1, $button, $btnText, $btnLoading);
                    }
                });
            }
        });
    </script>



    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('productShare');
            if (!container) return;

            const productUrl = container.dataset.url;
            const title = container.dataset.title || document.title;
            const text = container.dataset.text || '';

            const nativeShareBtn = document.getElementById('nativeShareBtn');
            const copyLinkBtn = document.getElementById('copyLinkBtn');
            const whatsappShare = document.getElementById('whatsappShare');
            const facebookShare = document.getElementById('facebookShare');
            const twitterShare = document.getElementById('twitterShare');
            const telegramShare = document.getElementById('telegramShare');
            const emailShare = document.getElementById('emailShare');
            const shareToast = document.getElementById('shareToast');

            function showToast(msg) {
                if (!shareToast) return;
                shareToast.style.display = 'block';
                shareToast.textContent = msg;
                // make visible
                requestAnimationFrame(() => {
                    shareToast.style.opacity = '1';
                    shareToast.setAttribute('aria-hidden', 'false');
                });

                clearTimeout(showToast._t);
                showToast._t = setTimeout(() => {
                    shareToast.style.opacity = '0';
                    shareToast.setAttribute('aria-hidden', 'true');
                    // hide after transition
                    setTimeout(() => {
                        shareToast.style.display = 'none';
                    }, 180);
                }, 2000);
            }

            // Native Web Share API (mobile & supported desktop)
            if (navigator.share) {
                nativeShareBtn.addEventListener('click', async () => {
                    try {
                        await navigator.share({
                            title,
                            text,
                            url: productUrl
                        });
                        showToast('Shared successfully');
                    } catch (err) {
                        // user cancelled or other error
                        console.warn('Share API error', err);
                    }
                });
            } else {
                // if not supported, make native button copy the link
                nativeShareBtn.title = 'Copy link';
                nativeShareBtn.addEventListener('click', () => {
                    doCopy(productUrl);
                });
            }

            // Copy-to-clipboard
            async function doCopy(textToCopy) {
                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(textToCopy);
                    } else {
                        const ta = document.createElement('textarea');
                        ta.value = textToCopy;
                        ta.style.position = 'fixed';
                        ta.style.left = '-9999px';
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        document.body.removeChild(ta);
                    }
                    showToast('Link copied to clipboard');
                } catch (e) {
                    console.error('Copy failed', e);
                    showToast('Unable to copy');
                }
            }

            copyLinkBtn.addEventListener('click', () => doCopy(productUrl));

            // Build social links
            const encodedUrl = encodeURIComponent(productUrl);
            const encodedTitle = encodeURIComponent(title);
            const encodedText = encodeURIComponent(text);

            whatsappShare.href = `https://wa.me/?text=${encodedTitle}%20-%20${encodedUrl}`;
            facebookShare.href = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
            twitterShare.href = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`;
            telegramShare.href = `https://t.me/share/url?url=${encodedUrl}&text=${encodedTitle}`;
            emailShare.href = `mailto:?subject=${encodedTitle}&body=${encodedText}%0A%0A${encodedUrl}`;

            // optional: open social links in a small popup to keep user on product page
            [whatsappShare, facebookShare, twitterShare, telegramShare].forEach(a => {
                a.addEventListener('click', (e) => {
                    // allow ctrl/cmd/middle-click to open in new tab normally
                    if (e.ctrlKey || e.metaKey || e.button === 1) return;
                    e.preventDefault();
                    window.open(a.href, '_blank',
                        'toolbar=0,location=0,status=0,menubar=0,width=650,height=520');
                    showToast('Opening share dialog');
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            var $mainEl = $('.product-img-list');
            var $thumbEl = $('.product-thumb-list');

            if (!$mainEl.length || typeof $.fn.owlCarousel !== 'function') return;

            // Destroy any existing carousel instance (main.js may have initialized it first)
            if ($mainEl.hasClass('owl-loaded')) {
                $mainEl.trigger('destroy.owl.carousel');
                $mainEl.html($mainEl.find('.owl-stage-outer').html()).removeClass('owl-loaded owl-hidden');
            }
            if ($thumbEl.hasClass('owl-loaded')) {
                $thumbEl.trigger('destroy.owl.carousel');
                $thumbEl.html($thumbEl.find('.owl-stage-outer').html()).removeClass('owl-loaded owl-hidden');
            }

            var itemCount = $mainEl.find('.items-list').length;
            var enableLoop = itemCount > 1;

            // Initialize main carousel with autoplay
            var $mainCarousel = $mainEl.owlCarousel({
                loop: enableLoop,
                margin: 20,
                nav: true,
                items: 1,
                dots: true,
                autoplay: enableLoop,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                navText: ['<i class="icon-angle-left"></i>', '<i class="icon-angle-right"></i>'],
                responsive: {
                    0: { items: 1 },
                    600: { items: 1 },
                    1000: { items: 1 }
                }
            });

            // Initialize thumbnail carousel (3-4 items visible)
            var $thumbCarousel = $thumbEl.owlCarousel({
                loop: false,
                margin: 10,
                nav: false,
                dots: false,
                items: 4,
                responsive: {
                    0: { items: 3 },
                    600: { items: 3 },
                    768: { items: 4 },
                    1000: { items: 4 }
                }
            });

            // Set first thumbnail as active
            $('.product-thumb-list .thumb-item').first().addClass('active');

            // Click on thumbnail to change main image
            $('.product-thumb-list .thumb-item').on('click', function() {
                var index = $(this).data('index');

                // Remove active class from all thumbnails
                $('.product-thumb-list .thumb-item').removeClass('active');

                // Add active class to clicked thumbnail
                $(this).addClass('active');

                // Go to corresponding slide in main carousel
                $mainCarousel.trigger('to.owl.carousel', [index, 300]);

                // Stop autoplay when user manually clicks
                $mainCarousel.trigger('stop.owl.autoplay');

                // Optionally restart autoplay after 5 seconds
                setTimeout(function() {
                    $mainCarousel.trigger('play.owl.autoplay', [3000]);
                }, 5000);
            });

            // Sync thumbnails when main carousel changes (including autoplay)
            $mainCarousel.on('changed.owl.carousel', function(event) {
                var currentIndex = event.item.index;
                var itemCount = event.item.count;

                // Calculate actual index (accounting for clones in loop)
                var actualIndex = (currentIndex - event.relatedTarget._clones.length / 2) % itemCount;
                var normalizedIndex = actualIndex < 0 ? itemCount + actualIndex : actualIndex;

                // Update active thumbnail
                $('.product-thumb-list .thumb-item').removeClass('active');
                $('.product-thumb-list .thumb-item').eq(normalizedIndex).addClass('active');
            });
        });
    </script>
@endsection
