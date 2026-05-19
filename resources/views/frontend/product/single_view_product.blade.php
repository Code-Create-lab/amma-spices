@extends('frontend.layouts.app', ['title' => ''])

<style>
    .product-details-quantity .input-group.input-spinner {
        width: 50%;
    }

    <style>.product-details-quantity .input-group.input-spinner {
        width: 50%;
    }

    /* ═══════════════════════════════════════════════════
       PRODUCT GALLERY  (PGC — custom, no Owl dependency)
    ═══════════════════════════════════════════════════ */
    .pgc {
        display: flex;
        flex-direction: row-reverse;
        gap: 12px;
        width: 100%;
    }

    /* ── Stage ───────────────────────────────────────── */
    .pgc__stage {
        position: relative;
        flex: 1 1 0%;
        min-width: 0;
        border-radius: 12px;
        overflow: hidden;
        background: #ffffffff;
        aspect-ratio: 1 / 1;
    }

    .pgc__slide {
        display: none;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
    }

    .pgc__slide.is-active {
        display: flex;
    }

    .pgc__slide img,
    .pgc__slide video {
        max-width: 100%;
        max-height: 100%;
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        border-radius: 8px;
    }

    /* ── Nav arrows ──────────────────────────────────── */
    .pgc__nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .2s, opacity .2s, transform .15s;
        font-size: 15px;
        color: #333;
        padding: 0;
    }

    .pgc__nav:hover {
        background: #fff;
        transform: translateY(-50%) scale(1.08);
    }

    .pgc__nav--prev {
        left: 10px;
    }

    .pgc__nav--next {
        right: 10px;
    }

    .pgc__nav.is-hidden {
        opacity: 0;
        pointer-events: none;
    }

    /* Slide counter badge */
    .pgc__counter {
        position: absolute;
        bottom: 10px;
        right: 14px;
        background: rgba(0, 0, 0, .45);
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 20px;
        letter-spacing: .4px;
        pointer-events: none;
        z-index: 5;
    }

    /* ── Thumbnails ──────────────────────────────────── */
    .pgc__thumbs {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 74px;
        flex-shrink: 0;
        overflow-y: auto;
        max-height: 480px;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .pgc__thumbs::-webkit-scrollbar {
        display: none;
    }

    .pgc__thumb {
        width: 74px;
        height: 74px;
        flex-shrink: 0;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #e8e8e8;
        transition: border-color .2s, box-shadow .2s;
        position: relative;
        background: #f4f4f4;
    }

    .pgc__thumb:hover {
        border-color: #bbb;
    }

    .pgc__thumb.is-active {
        border-color: #c96;
        /* match your brand accent */
        box-shadow: 0 0 0 1px #c96;
    }

    .pgc__thumb img,
    .pgc__thumb video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        pointer-events: none;
    }

    /* Video thumb overlay */
    .pgc__play {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, .28);
        color: #fff;
        font-size: 17px;
    }

    /* ── Mobile ──────────────────────────────────────── */
    @media (max-width: 575px) {
        .pgc {
            flex-direction: column;
            gap: 10px;
        }

        .pgc__thumbs {
            flex-direction: row;
            width: 100%;
            max-height: none;
            overflow-x: auto;
            overflow-y: visible;
            padding-bottom: 2px;
        }

        .pgc__thumb {
            width: 62px;
            height: 62px;
        }
    }
</style>
</style>

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
            <div class="container-fluid d-flex align-items-center">
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
                            <div class="pgc" id="productGallery">

                                {{-- ── Thumbnails ────────────────────────────────── --}}
                                <div class="pgc__thumbs" id="pgcThumbs">
                                    @if(!empty($response['images']) && count($response['images']) > 0)
                                        @foreach ($response['images'] as $index => $media)
                                            @php
                                                $ext = strtolower(pathinfo($media['image'], PATHINFO_EXTENSION));
                                                $isVid = in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
                                                $src = asset('storage/' . $media['image']);
                                            @endphp
                                            <div class="pgc__thumb {{ $index === 0 ? 'is-active' : '' }}"
                                                data-pgc-index="{{ $index }}" data-pgc-src="{{ $src }}"
                                                data-pgc-type="{{ $isVid ? 'video' : 'image' }}" data-pgc-ext="{{ $ext }}">
                                                @if($isVid)
                                                    <video src="{{ $src }}" muted preload="metadata"></video>
                                                    <span class="pgc__play"><i class="icon-play"></i></span>
                                                @else
                                                    <img src="{{ $src }}" alt="Thumb {{ $index + 1 }}" loading="lazy">
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="pgc__thumb is-active" data-pgc-index="0"
                                            data-pgc-src="{{ asset('storage/' . $response['image']) }}" data-pgc-type="image">
                                            <img src="{{ asset('storage/' . $response['image']) }}"
                                                alt="{{ $response['name'] }}" loading="lazy">
                                        </div>
                                    @endif
                                </div>

                                {{-- ── Main Stage ─────────────────────────────────── --}}
                                <div class="pgc__stage" id="pgcStage">

                                    {{-- Slides --}}
                                    @if(!empty($response['images']) && count($response['images']) > 0)
                                        @foreach ($response['images'] as $index => $media)
                                            @php
                                                $ext = strtolower(pathinfo($media['image'], PATHINFO_EXTENSION));
                                                $isVid = in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
                                                $src = asset('storage/' . $media['image']);
                                            @endphp
                                            <div class="pgc__slide {{ $index === 0 ? 'is-active' : '' }}"
                                                data-pgc-index="{{ $index }}">
                                                @if($isVid)
                                                    <video controls preload="metadata">
                                                        <source src="{{ $src }}" type="video/{{ $ext }}">
                                                    </video>
                                                @else
                                                    <img src="{{ $src }}" alt="{{ $response['name'] }} – image {{ $index + 1 }}">
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="pgc__slide is-active" data-pgc-index="0">
                                            <img src="{{ asset('storage/' . $response['image']) }}"
                                                alt="{{ $response['name'] }}">
                                        </div>
                                    @endif

                                    {{-- Navigation --}}
                                    <button class="pgc__nav pgc__nav--prev is-hidden" id="pgcPrev"
                                        aria-label="Previous image">
                                        <i class="icon-angle-left"></i>
                                    </button>
                                    <button class="pgc__nav pgc__nav--next" id="pgcNext" aria-label="Next image">
                                        <i class="icon-angle-right"></i>
                                    </button>

                                    {{-- Slide counter --}}
                                    <span class="pgc__counter" id="pgcCounter"></span>
                                </div>

                            </div>
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

                                <div class="product-price" id="product-price">
                                    {{-- @dd($response['variations'][0]['price'] ?? $response['base_price'] ) --}}
                                    {{-- @if ($response['variations'][0]['price'] ?? $response['base_price'] == 0)
                                    <span class="new-price">
                                        ₹{{ number_format($price, 0, '.', ',') }}</span>
                                    @else --}}
                                    @if ($price != 0 || $price)
                                        <span class="old-price 1"> ₹{{ $mrp }}</span>
                                    @endif

                                    <span class="new-price" id="product-price-current">
                                        ₹{{ number_format($price == 0 ? $mrp : $price, 0, '.', ',') }}</span>
                                    @if ($price != 0)
                                        <span id="product-price-discount" @if ($price == 0 || $mrp == $price) style="display: none;"
                                        @endif>
                                            <span class="discount">{{ (int) $percentOff }}%off</span>
                                        </span>
                                    @endif
                                    {{-- @endif --}}

                                </div><!-- End .product-price -->
                                <span class="gst-n-text">(GST Included)</span>

                                {{-- ────────────────────────────────────────────────
                                VARIATION ATTRIBUTES (Color · Size · Weight/any)
                                ──────────────────────────────────────────────── --}}
                                @if (!empty($response['variation_attributes']))
                                    @foreach ($response['variation_attributes'] as $attributeName => $attributeValues)
                                        @php
                                            /* ── classify the attribute ── */
                                            $sizeKeywords = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Size', 'size'];
                                            $colorKeywords = ['Color', 'color', 'Colour', 'colour'];

                                            $isSize = in_array($attributeName, $sizeKeywords);
                                            $isColor = in_array($attributeName, $colorKeywords);
                                            $isWeight = !$isSize && !$isColor; // weight / volume / any custom

                                            $attrType = $isSize ? 'Size' : ($isColor ? 'Color' : $attributeName);
                                            $displayName = $attrType;
                                        @endphp

                                        <div class="variation-group mb-3" data-attr-type="{{ $attrType }}">
                                            <div class="variation-label-row">
                                                <span class="variation-label">{{ $displayName }}</span>
                                                @if ($isSize || $isWeight)
                                                    <span class="variation-selected-val"
                                                        id="selected-{{ Str::slug($attrType) }}"></span>
                                                @endif
                                            </div>

                                            {{-- ── COLOR SWATCHES ── --}}
                                            @if ($isColor)
                                                <ul class="variation-swatch-list attribute-options">
                                                    @foreach ($attributeValues as $index => $attribute)
                                                        @php
                                                            $hasStock = false;
                                                            foreach ($response['variations'] ?? [] as $variation) {
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
                                                            $colorHex = strtolower(
                                                                str_replace(' ', '', $attribute['value']),
                                                            );
                                                            $uniqueId = 'color-' . ($index + 1);
                                                        @endphp
                                                        <li>
                                                            <button type="button"
                                                                class="swatch-color attribute-btn {{ $index === 0 ? 'selected attribute_seletion' : '' }} {{ !$hasStock ? 'oos' : '' }}"
                                                                data-attr="{{ $attrType }}" data-value="{{ $colorHex }}"
                                                                data-label="{{ $attribute['value'] }}" data-id="{{ $attribute['id'] }}"
                                                                style="--swatch-color: {{ $colorHex }};" {{ !$hasStock ? 'disabled title=Out of Stock' : '' }}
                                                                aria-label="{{ $attribute['value'] }}{{ !$hasStock ? ' – Out of Stock' : '' }}">
                                                                <span class="swatch-inner"></span>
                                                                @if (!$hasStock)
                                                                    <span class="oos-slash"></span>
                                                                @endif
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                {{-- ── SIZE PILLS ── --}}
                                            @elseif ($isSize)
                                                <ul class="variation-pill-list attribute-options">
                                                    @foreach ($attributeValues as $index => $attribute)
                                                        @php
                                                            $hasStock = false;
                                                            foreach ($response['variations'] ?? [] as $variation) {
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
                                                            $sizeVal = strtolower(
                                                                str_replace(' ', '', $attribute['value']),
                                                            );
                                                            $uniqueId = 'size-' . ($index + 1);
                                                        @endphp
                                                        <li>
                                                            <button type="button"
                                                                class="pill-btn attribute-btn {{ $index === 0 ? 'selected attribute_seletion' : '' }} {{ !$hasStock ? 'oos' : '' }}"
                                                                data-attr="{{ $attrType }}" data-value="{{ $sizeVal }}"
                                                                data-label="{{ $attribute['value'] }}" data-id="{{ $attribute['id'] }}" {{ !$hasStock ? 'disabled title=Out of Stock' : '' }}>
                                                                {{ strtoupper($attribute['value']) }}
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                {{-- ── WEIGHT / CUSTOM PILLS ── --}}
                                            @else
                                                <ul class="variation-pill-list attribute-options">
                                                    @foreach ($attributeValues as $index => $attribute)
                                                        @php
                                                            $hasStock = false;
                                                            foreach ($response['variations'] ?? [] as $variation) {
                                                                if ($variation['stock'] > 0) {
                                                                    foreach ($variation['attributes'] as $attr) {
                                                                        if (
                                                                            strtolower($attr['name']) ===
                                                                            strtolower($attributeName) &&
                                                                            strtolower($attr['value']) ===
                                                                            strtolower($attribute['value'])
                                                                        ) {
                                                                            $hasStock = true;
                                                                            break 2;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            $attrVal = strtolower(
                                                                str_replace(' ', '', $attribute['value']),
                                                            );
                                                            $attrLabel = trim((string) $attribute['value']);
                                                            if (strtolower($attrType) === 'quantity' && preg_match('/^\d+(\.\d+)?$/', $attrLabel)) {
                                                                $attrLabel .= 'g';
                                                            }
                                                        @endphp
                                                        <li>
                                                            <button type="button"
                                                                class="pill-btn pill-btn--weight attribute-btn {{ $index === 0 ? 'selected attribute_seletion' : '' }} {{ !$hasStock ? 'oos' : '' }}"
                                                                data-attr="{{ $attrType }}" data-value="{{ $attrVal }}"
                                                                data-label="{{ $attrLabel }}" data-id="{{ $attribute['id'] }}" {{ !$hasStock ? 'disabled title=Out of Stock' : '' }}>
                                                                {{ $attrLabel }}
                                                                @if (!$hasStock)
                                                                    <span class="oos-tag">Out of stock</span>
                                                                @endif
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>{{-- /.variation-group --}}
                                    @endforeach
                                @endif

                                <div class="product-content">
                                    {!! $response['description'] !!}
                                </div><!-- End .product-content -->


                                <div class="details-filter-row details-row-size">
                                    <label for="qty">Qty:</label>
                                    <div class="product-details-quantity">
                                        <input type="number" id="qty"
                                            class="form-control qty product-card__qty  product_quantity" value="1" min="1"
                                            max="10" step="1" data-decimals="0" required>
                                    </div><!-- End .product-details-quantity -->
                                </div><!-- End .details-filter-row -->


                                <div class="product-details-action">
                                    <a class="btn-product btn-cart add-to-cartJS"><span>add to cart</span></a>
                                    {{-- <a class="btn-product btn-cart buy_nowJS"><span>Buy now</span></a> --}}

                                    <div class="details-action-wrapper">
                                        <a data-id="{{ $response['id'] }}"
                                            class="{{ $isInWishlist ? 'remove-from-wishlist liked' : 'add-to-wishlist' }} wishlist  btn-product btn-wishlist"
                                            title="Wishlist">
                                            <svg class="wish-icon" title="Like Safebox SVG File" width="21" height="21"
                                                viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
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
                            <a class="nav-link" id="product-shipping-link" data-toggle="tab" href="#product-shipping-tab"
                                role="tab" aria-controls="product-shipping-tab" aria-selected="false">Shipping &
                                Returns</a>
                        </li>
                        @endif --}}
                        <li class="nav-item">
                            <a class="nav-link" id="product-review-link" data-toggle="tab" href="#product-review-tab"
                                role="tab" aria-controls="product-review-tab" aria-selected="false">Reviews
                                ({{ $total_reviews }})</a>
                        </li>
                    </ul>
                    <div class="tab-content mb-5">
                        {{-- <img src="https://bodhi-bliss.yugasa.org/assets/img/bg-feture.png" class="bg-feture-sec"> --}}
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
                                                <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
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

            @if ($recentlyViewedProducts->where('product_id', '!=', $response['id'])->isNotEmpty())
                <div class="featured-list-slider realted_pro">
                    <div class="container">
                        <div class="row" style="display: block;">
                            <div class="heading">
                                <h2 class="title  text-center">Recently Viewed products</h2>
                                <span class="seprater-img"><img src="{{ asset('assets/img/seprater.png') }}"></span>
                            </div>
                        </div>
                        <div class="row">
                            @if ($recentlyViewedProducts->isNotEmpty())
                                <x-product-list :products="$recentlyViewedProducts->where('product_id', '!=', $response['id'])->reverse()" />
                            @endif

                        </div>
                    </div>
                    <img src="{{ asset('assets/img/related_img.png') }}" class="bg-feture8-sec">
                </div>
            @endif
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
                                <x-product-list :products="$related_products->where('product_id', '!=', $response['id'])->reverse()" />
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
        $(document).ready(function () {


            $('.copy-coupon-code').on('click', function () {
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

            // Variant handling is reinitialized in the dedicated script below.
            return;
            var variations = window.variationsData;
            if (!variations) {
                console.error('Variations not found!');
                return;
            }
            console.log(variations, 'variations');

            $('.product-gallery-item').on('click', function (e) {
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
            $(".cart-plus-btn-single").click(function () {
                let $input = $(this).siblings("input.qty");
                let currentVal = parseInt($input.val()) || 0;
                $input.val(currentVal + 1).change();
            });

            $('.cart-minus-btn-single').click(function () {
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
                    value: firstSizeBtn.data('value')
                    // .toLowerCase().replace(/\s+/g, '')
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
            $('[data-attr="Balck"]').each(function () {
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
            /*  $(document).on('click', '.attribute-btn', function() {
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

              */
            $(document).on('click', '.attribute-btn', function () {
                if ($(this).prop('disabled') || $(this).hasClass('oos')) return;

                var attrName = $(this).data('attr');
                var attrValue = $(this).data('value');
                var attrId = $(this).data('id');

                // Deselect siblings in same group, select clicked
                $(this).closest('.attribute-options')
                    .find('.attribute-btn')
                    .removeClass('selected attribute_seletion');
                $(this).addClass('selected attribute_seletion');

                // Store selection
                selectedAttributes[attrName] = {
                    id: attrId,
                    value: attrValue //.toLowerCase().replace(/\s+/g, '')
                };

                // Update the "selected value" label next to the variation heading
                var $labelEl = $(this).closest('.variation-group').find('.variation-selected-val');
                $labelEl.text($(this).data('value')); // show original readable value

                // If size changed, re-filter colour options
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

                $('[data-attr="colour"]').each(function () {
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

                console.log("variations", variations)
                var foundVariation = variations.find(function (variation) {
                    return variation.attributes.every(attr => {
                        var selectedAttr = selectedAttributes[attr.name];
                        console.log("selectedAttributes", selectedAttributes, attr)
                        if (!selectedAttr) return false;
                        return selectedAttr.value === parseInt(attr
                            .value) //.toLowerCase().replace(/\s+/g, '');
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
                    console.log("Else foundVariation", foundVariation);
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
            $('.add-to-cartJS').click(function () {
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
                    success: function (response) {
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
                    error: function (error) {
                        toastr.error(error.responseJSON?.message ||
                            'Something went wrong.');
                    }
                });
                // } else {
                //     toastr.error("Please select all attributes.");
                // }
            });

            // Image Change on Hover
            $(document).on('mouseenter', '.thumbnail', function () {
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
        $(document).ready(function () {
            var variations = Array.isArray(window.variationsData) ? window.variationsData : [];
            var $variantGroups = $('.variation-group');
            var attributeGroupCount = $variantGroups.length;
            var selectedAttributes = {};
            var selectedVariationId = null;
            var $addToCartButton = $('.product-details-action .add-to-cartJS').first();
            var $priceContainer = $('#product-price');
            var $priceCurrent = $('#product-price-current');
            var $priceMrp = $priceContainer.find('.old-price').first();
            var $priceDiscountWrap = $('#product-price-discount');
            var $priceDiscountText = $priceDiscountWrap.find('.discount').first();
            var $mainGallery = $('.product-img-list');
            var $thumbGallery = $('.product-thumb-list');
            var storageBaseUrl = @json(asset('storage'));
            var basePrice = Number(@json((float) ($response['base_price'] ?? 0)));
            var baseMrp = Number(@json((float) ($response['base_mrp'] ?? 0)));
            var fallbackImage = @json(!empty($response['image']) ? asset('storage/' . $response['image']) : '');
            var defaultGalleryImage = $mainGallery.find('.items-list img').first().attr('src') || fallbackImage;
            var gallerySyncTimer = null;

            if (!$priceContainer.length || !$addToCartButton.length) {
                return;
            }

            if (!$priceCurrent.length) {
                $priceCurrent = $('<span class="new-price" id="product-price-current"></span>');
                $priceContainer.append($priceCurrent);
            }

            if (!$priceMrp.length) {
                $priceMrp = $('<span class="old-price 1" style="display: none;"></span>');
                $priceContainer.prepend($priceMrp);
            }

            if (!$priceDiscountWrap.length) {
                $priceDiscountWrap = $(
                    '<span id="product-price-discount" style="display: none;"><span class="discount"></span></span>'
                );
                $priceContainer.append($priceDiscountWrap);
            }

            if (!$priceDiscountText.length) {
                $priceDiscountText = $priceDiscountWrap.find('.discount').first();
                if (!$priceDiscountText.length) {
                    $priceDiscountText = $('<span class="discount"></span>');
                    $priceDiscountWrap.append($priceDiscountText);
                }
            }

            function normalizeValue(value) {
                return String(value === undefined || value === null ? '' : value)
                    .trim()
                    .toLowerCase()
                    .replace(/\s+/g, '');
            }

            function normalizeAttributeName(name) {
                var normalizedName = String(name === undefined || name === null ? '' : name).trim();
                var lowerName = normalizedName.toLowerCase();

                if (lowerName === 'color' || lowerName === 'colour') {
                    return 'Color';
                }

                if (lowerName === 'size') {
                    return 'Size';
                }

                return normalizedName;
            }

            variations = variations.map(function (variation) {
                variation._normalizedAttributes = {};

                (variation.attributes || []).forEach(function (attr) {
                    variation._normalizedAttributes[normalizeAttributeName(attr.name)] =
                        normalizeValue(attr.value);
                });

                return variation;
            });

            function formatPrice(value) {
                return '\u20B9' + Number(value || 0).toLocaleString('en-IN');
            }

            function isSelectable($button) {
                return !$button.prop('disabled') && !$button.hasClass('oos');
            }

            function getButtonLabel($button) {
                return String($button.data('label') || $.trim($button.text()) || $button.data('value') || '')
                    .trim();
            }

            function cloneSelection(source) {
                var copy = {};

                Object.keys(source).forEach(function (key) {
                    copy[key] = {
                        id: source[key].id,
                        value: source[key].value
                    };
                });

                return copy;
            }

            function setSelectedButton($button) {
                var attrName = normalizeAttributeName($button.data('attr'));
                var $group = $button.closest('.variation-group');

                $group.find('.attribute-btn').removeClass('selected attribute_seletion');
                $button.addClass('selected attribute_seletion');

                selectedAttributes[attrName] = {
                    id: $button.data('id'),
                    value: normalizeValue($button.data('value'))
                };

                $group.find('.variation-selected-val').text(getButtonLabel($button));
            }

            function hasMatchingVariation(selection, requireInStock) {
                return variations.some(function (variation) {
                    if (requireInStock && Number(variation.stock || 0) <= 0) {
                        return false;
                    }

                    return Object.keys(selection).every(function (attrName) {
                        return variation._normalizedAttributes[attrName] === selection[attrName]
                            .value;
                    });
                });
            }

            function refreshOptionStates() {
                var selectionChanged = false;

                $variantGroups.each(function () {
                    var $group = $(this);
                    var attrName = normalizeAttributeName($group.data('attr-type'));

                    $group.find('.attribute-btn').each(function () {
                        var $button = $(this);
                        var candidateSelection = cloneSelection(selectedAttributes);

                        candidateSelection[attrName] = {
                            id: $button.data('id'),
                            value: normalizeValue($button.data('value'))
                        };

                        var isAvailable = hasMatchingVariation(candidateSelection, true);

                        $button.toggleClass('oos', !isAvailable);
                        $button.prop('disabled', !isAvailable);

                        if (!isAvailable) {
                            $button.attr('title', 'Out of Stock');
                        } else if ($button.attr('title') === 'Out of Stock') {
                            $button.removeAttr('title');
                        }
                    });

                    var $selected = $group.find('.attribute-btn.selected').first();
                    if (!$selected.length || !isSelectable($selected)) {
                        var $fallback = $group.find('.attribute-btn').filter(function () {
                            return isSelectable($(this));
                        }).first();

                        $group.find('.attribute-btn').removeClass('selected attribute_seletion');
                        delete selectedAttributes[attrName];
                        $group.find('.variation-selected-val').text('');

                        if ($fallback.length) {
                            setSelectedButton($fallback);
                            selectionChanged = true;
                        }
                    }
                });

                return selectionChanged;
            }

            function getExactVariation() {
                if (!variations.length) {
                    return null;
                }

                if (!attributeGroupCount) {
                    return variations[0];
                }

                return variations.find(function (variation) {
                    var variationAttributeCount = Array.isArray(variation.attributes) ? variation.attributes
                        .length : 0;

                    if (Object.keys(selectedAttributes).length !== variationAttributeCount) {
                        return false;
                    }

                    return Object.keys(selectedAttributes).every(function (attrName) {
                        return variation._normalizedAttributes[attrName] === selectedAttributes[
                            attrName].value;
                    });
                }) || null;
            }

            function renderPrice(price, mrp) {
                var numericPrice = Number(price || 0);
                var numericMrp = Number(mrp || 0);
                var effectivePrice = numericPrice === 0 ? numericMrp : numericPrice;
                var hasDiscount = numericPrice > 0 && numericMrp > numericPrice;

                $priceCurrent.text(formatPrice(effectivePrice));

                if (hasDiscount) {
                    var percentOff = Math.round(((numericMrp - numericPrice) / numericMrp) * 100);
                    $priceMrp.text(formatPrice(numericMrp)).show();
                    $priceDiscountText.text(percentOff + '%off');
                    $priceDiscountWrap.show();
                } else {
                    $priceMrp.hide();
                    $priceDiscountWrap.hide();
                }
            }

            var isVariationImageActive = false;

            function renderGalleryImage(imageUrl) {
                var resolvedImage = imageUrl || defaultGalleryImage;
                if (!resolvedImage) {
                    return;
                }

                var isVariation = (resolvedImage !== defaultGalleryImage);

                if ($mainGallery.hasClass('owl-loaded')) {
                    if (isVariation) {
                        if (!isVariationImageActive) {
                            // Add variant image at index 0 safely
                            var newMainHtml = '<div class="items-list"><img src="' + resolvedImage + '" style="width: 100%; height: auto; max-width: 100%; object-fit: contain; pointer-events: none;" alt="Variant"></div>';
                            var newThumbHtml = '<div class="thumb-item"><img src="' + resolvedImage + '" alt="Variant Thumb"></div>';

                            $mainGallery.trigger('add.owl.carousel', [newMainHtml, 0]).trigger('refresh.owl.carousel');
                            $thumbGallery.trigger('add.owl.carousel', [newThumbHtml, 0]).trigger('refresh.owl.carousel');

                            isVariationImageActive = true;
                        } else {
                            // Update the currently added variant image (which is at non-cloned index 0)
                            // Owl Carousel keeps the original non-cloned items first in DOM inside .owl-stage
                            $mainGallery.find('.owl-item:not(.cloned) .items-list img').first().attr('src', resolvedImage);
                            $thumbGallery.find('.owl-item:not(.cloned) .thumb-item img').first().attr('src', resolvedImage);

                            // Also update cloned ones just in case
                            $mainGallery.find('.owl-item.cloned .items-list img[alt="Variant"]').attr('src', resolvedImage);
                            $thumbGallery.find('.owl-item.cloned .thumb-item img[alt="Variant Thumb"]').attr('src', resolvedImage);
                        }

                        $mainGallery.trigger('to.owl.carousel', [0, 300]);

                        clearTimeout(gallerySyncTimer);
                        gallerySyncTimer = setTimeout(function () {
                            $thumbGallery.find('.thumb-item').removeClass('active');
                            $thumbGallery.find('.owl-item:not(.cloned)').first().find('.thumb-item').addClass('active');
                        }, 325);

                    } else {
                        // User deselected variant or picked one with no image; fall back to default
                        if (isVariationImageActive) {
                            // Safe removal pattern: snap to next slide before destroying the current focal slide
                            $mainGallery.trigger('to.owl.carousel', [1, 0]);
                            $thumbGallery.trigger('to.owl.carousel', [1, 0]);

                            setTimeout(function () {
                                $mainGallery.trigger('remove.owl.carousel', [0]).trigger('refresh.owl.carousel');
                                $thumbGallery.trigger('remove.owl.carousel', [0]).trigger('refresh.owl.carousel');

                                $mainGallery.trigger('to.owl.carousel', [0, 0]);
                                $thumbGallery.trigger('to.owl.carousel', [0, 0]);
                            }, 50);

                            isVariationImageActive = false;
                        } else {
                            $mainGallery.trigger('to.owl.carousel', [0, 300]);
                        }

                        clearTimeout(gallerySyncTimer);
                        gallerySyncTimer = setTimeout(function () {
                            $thumbGallery.find('.thumb-item').removeClass('active');
                            $thumbGallery.find('.owl-item:not(.cloned)').first().find('.thumb-item').addClass('active');
                        }, 325);
                    }
                } else {
                    // Fallback if carousel isn't initialized yet
                    var $firstMainImage = $mainGallery.find('.items-list img').first();
                    var $firstThumbImage = $thumbGallery.find('.thumb-item img').first();
                    if ($firstMainImage.length) $firstMainImage.attr('src', resolvedImage);
                    if ($firstThumbImage.length) $firstThumbImage.attr('src', resolvedImage);
                }
            }

            function renderCartState(variation) {
                if (!attributeGroupCount && !variation) {
                    $addToCartButton.prop('disabled', false).removeClass('disabled').html(
                        '<span>add to cart</span>');
                    return;
                }

                if (!variation) {
                    $addToCartButton.prop('disabled', true).addClass('disabled').html(
                        '<span>Select options</span>');
                    return;
                }

                if (Number(variation.stock || 0) <= 0) {
                    $addToCartButton.prop('disabled', true).addClass('disabled').html('<span>Out of stock</span>');
                    return;
                }

                $addToCartButton.prop('disabled', false).removeClass('disabled').html('<span>add to cart</span>');
            }

            function syncVariantState() {
                var attempts = 0;

                while (refreshOptionStates() && attempts < 5) {
                    attempts += 1;
                }

                var matchedVariation = getExactVariation();
                selectedVariationId = matchedVariation ? matchedVariation.id : null;

                if (matchedVariation) {
                    renderPrice(matchedVariation.price, matchedVariation.mrp);
                    renderGalleryImage(matchedVariation.image ? (storageBaseUrl + '/' + matchedVariation.image) :
                        defaultGalleryImage);
                } else {
                    renderPrice(basePrice, baseMrp);
                    renderGalleryImage(defaultGalleryImage);
                }

                renderCartState(matchedVariation);
            }

            function initializeSelections() {
                $variantGroups.each(function () {
                    var $group = $(this);
                    var $preferred = $group.find('.attribute-btn.selected').filter(function () {
                        return isSelectable($(this));
                    }).first();

                    if (!$preferred.length) {
                        $preferred = $group.find('.attribute-btn').filter(function () {
                            return isSelectable($(this));
                        }).first();
                    }

                    $group.find('.attribute-btn').removeClass('selected attribute_seletion');
                    $group.find('.variation-selected-val').text('');

                    if ($preferred.length) {
                        setSelectedButton($preferred);
                    }
                });

                syncVariantState();
            }

            $(document).on('click', '.variation-group .attribute-btn', function () {
                var $button = $(this);

                if (!isSelectable($button)) {
                    return;
                }

                setSelectedButton($button);
                syncVariantState();
            });

            initializeSelections();

            $addToCartButton.on('click', function (e) {
                if ($(this).prop('disabled') || $(this).hasClass('disabled')) {
                    e.preventDefault();
                    return;
                }

                if (attributeGroupCount && !selectedVariationId) {
                    e.preventDefault();
                    toastr.error('Please select an available variant.');
                    return;
                }

                var cartData = {
                    product_id: '{{ $response['id'] }}',
                    variation_id: selectedVariationId || '',
                    attributes: Object.values(selectedAttributes),
                    quantity: $('#qty').val() || 1
                };

                $.ajax({
                    url: '{{ route('addToCart') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: cartData,
                    success: function (response) {
                        if (response.success) {
                            $('#cart-count-span').html('(' + response.cart_count + ' Items)');
                            $('.cart-total-span').html(response.cart_total);

                            if (response.cart_count > 0) {
                                if ($('#cart_count_header').length > 0) {
                                    $('#cart_count_header').html(response.cart_count).show();
                                } else {
                                    $('a.cart__btn').append(
                                        '<ins id="cart_count_header"></ins>');
                                    $('#cart_count_header').html(response.cart_count).show();
                                }
                            } else {
                                $('#cart_count_header').hide();
                            }

                            toastr.options = {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 800,
                                extendedTimeOut: 500,
                                showDuration: 200,
                                hideDuration: 200,
                            };
                            toastr.success(response.message);

                            window.dispatchEvent(new CustomEvent('cart-updated'));
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (error) {
                        toastr.error(error.responseJSON?.message || 'Something went wrong.');
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
                    mainSwiper.swiper.on('slideChange', function () {
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


        $(document).ready(function () {


            // Get elements
            const qtyInput = document.getElementById('qty');
            const minusBtn = document.querySelector('.qty-minus');
            const plusBtn = document.querySelector('.qty-plus');

            // Decrease quantity
            if (minusBtn) {
                minusBtn.addEventListener('click', function () {
                    let currentValue = parseInt(qtyInput.value) || 1;
                    if (currentValue > 1) {
                        qtyInput.value = currentValue - 1;
                    }
                });
            }

            // Increase quantity
            if (plusBtn) {
                plusBtn.addEventListener('click', function () {
                    let currentValue = parseInt(qtyInput.value) || 1;
                    qtyInput.value = currentValue + 1;
                });
            }

            // Optional: Prevent non-numeric input
            if (qtyInput) {
                qtyInput.addEventListener('input', function () {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value === '' || parseInt(this.value) < 1) {
                        this.value = 1;
                    }
                });
            }

            // Update total price when checkboxes change
            $('.fbt-checkbox').on('change', function () {
                updateFbtTotalPrice();
            });

            function updateFbtTotalPrice() {
                let total = 0;
                $('.fbt-product-item').each(function () {
                    const checkbox = $(this).find('.fbt-checkbox');
                    if (checkbox.is(':checked')) {
                        const price = parseFloat($(this).data('product-price')) || 0;
                        total += price;
                    }
                });
                $('.fbt-total-amount').text('₹' + total.toLocaleString('en-IN'));
            }

            // Add all checked products to cart
            $('.fbt-add-to-cart-btn').on('click', function () {
                const $button = $(this);
                const $btnText = $button.find('.btn-text');
                const $btnLoading = $button.find('.btn-loading');

                // Get all checked products
                const selectedProducts = [];
                $('.fbt-product-item').each(function () {
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
                    success: function (response) {
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
                    error: function (error) {
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
        $(document).ready(function () {
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
                    0: {
                        items: 1
                    },
                    600: {
                        items: 1
                    },
                    1000: {
                        items: 1
                    }
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
                    0: {
                        items: 3
                    },
                    600: {
                        items: 3
                    },
                    768: {
                        items: 4
                    },
                    1000: {
                        items: 4
                    }
                }
            });

            // Set first thumbnail as active
            $('.product-thumb-list .owl-item:not(.cloned)').first().find('.thumb-item').addClass('active');

            // Click on thumbnail to change main image
            $('.product-thumb-list').on('click', '.thumb-item', function () {
                // Use native Owl structural index to avoid caching bugs when slides are dynamically added
                var index = $(this).closest('.owl-item').index();

                // Remove active class from all thumbnails
                $('.product-thumb-list .thumb-item').removeClass('active');

                // Add active class to clicked thumbnail
                $(this).addClass('active');

                // Go to corresponding slide in main carousel
                $mainCarousel.trigger('to.owl.carousel', [index, 300]);

                // Stop autoplay when user manually clicks
                $mainCarousel.trigger('stop.owl.autoplay');

                // Optionally restart autoplay after 5 seconds
                setTimeout(function () {
                    $mainCarousel.trigger('play.owl.autoplay', [3000]);
                }, 5000);
            });

            // Sync thumbnails when main carousel changes (including autoplay)
            $mainCarousel.on('changed.owl.carousel', function (event) {
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

    <script>
        /* ═══════════════════════════════════════════════════════════
           PGC – Product Gallery Controller
           Zero Owl-carousel dependency. Fixes the variant-active-
           border-on-wrong-slide bug by managing state directly.
        ═══════════════════════════════════════════════════════════ */
        window.PGC = (function ($) {
            'use strict';

            var $stage = $('#pgcStage');
            var $thumbsWrap = $('#pgcThumbs');
            var $prevBtn = $('#pgcPrev');
            var $nextBtn = $('#pgcNext');
            var $counter = $('#pgcCounter');

            var currentIndex = 0;
            var variantSlotActive = false;    // is the prepended variant slide present?
            var VARIANT_ATTR = 'data-pgc-variant';

            /* ─── helpers ────────────────────────────────────────── */
            function slides() { return $stage.find('.pgc__slide'); }
            function thumbs() { return $thumbsWrap.find('.pgc__thumb'); }
            function total() { return slides().length; }

            function updateCounter() {
                if (total() <= 1) { $counter.hide(); return; }
                $counter.text((currentIndex + 1) + ' / ' + total()).show();
            }

            function updateNav() {
                $prevBtn.toggleClass('is-hidden', currentIndex === 0);
                $nextBtn.toggleClass('is-hidden', currentIndex >= total() - 1);
            }

            function scrollThumbIntoView(index) {
                var $t = thumbs().eq(index);
                if (!$t.length) return;
                var el = $t[0];
                // horizontal (mobile) or vertical (desktop) scroll
                var parent = $thumbsWrap[0];
                var isHorizontal = getComputedStyle(parent).flexDirection === 'row';
                if (isHorizontal) {
                    parent.scrollTo({ left: el.offsetLeft - parent.offsetWidth / 2 + el.offsetWidth / 2, behavior: 'smooth' });
                } else {
                    parent.scrollTo({ top: el.offsetTop - parent.offsetHeight / 2 + el.offsetHeight / 2, behavior: 'smooth' });
                }
            }

            /* ─── goTo ────────────────────────────────────────────── */
            function goTo(index, instant) {
                var t = total();
                index = Math.max(0, Math.min(index, t - 1));
                if (index === currentIndex && instant !== true) {
                    /* already here – still refresh active states for safety */
                }
                currentIndex = index;

                slides().removeClass('is-active').eq(index).addClass('is-active');
                thumbs().removeClass('is-active').eq(index).addClass('is-active');

                scrollThumbIntoView(index);
                updateNav();
                updateCounter();
            }

            /* ─── variant image slot (index 0) ───────────────────── */
            /* Called by renderGalleryImage in the variant-selection JS */
            function setVariantImage(imageUrl) {
                if (!imageUrl) {
                    /* Remove variant slot if present */
                    if (variantSlotActive) {
                        $stage.find('[' + VARIANT_ATTR + ']').remove();
                        $thumbsWrap.find('[' + VARIANT_ATTR + ']').remove();
                        variantSlotActive = false;

                        /* Ensure we don't jump to a now-invalid index */
                        goTo(0, true);
                    }
                    return;
                }

                var slideHtml =
                    '<div class="pgc__slide" ' + VARIANT_ATTR + '="1">' +
                    '<img src="' + imageUrl + '" alt="Variant Image"></div>';

                var thumbHtml =
                    '<div class="pgc__thumb" ' + VARIANT_ATTR + '="1">' +
                    '<img src="' + imageUrl + '" alt="Variant Thumb" loading="lazy"></div>';

                if (variantSlotActive) {
                    /* Just update the image src — no DOM insertion, no index shift */
                    $stage.find('[' + VARIANT_ATTR + '] img').attr('src', imageUrl);
                    $thumbsWrap.find('[' + VARIANT_ATTR + '] img').attr('src', imageUrl);
                } else {
                    /* Prepend once */
                    $stage.prepend(slideHtml);
                    $thumbsWrap.prepend(thumbHtml);
                    variantSlotActive = true;
                }

                /* Navigate to the variant slot (always index 0 after prepend) */
                goTo(0, true);
            }

            /* ─── touch / swipe ──────────────────────────────────── */
            var touchStartX = 0, touchStartY = 0;

            $stage[0].addEventListener('touchstart', function (e) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
            }, { passive: true });

            $stage[0].addEventListener('touchend', function (e) {
                var dx = e.changedTouches[0].clientX - touchStartX;
                var dy = e.changedTouches[0].clientY - touchStartY;
                if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 40) {
                    goTo(dx < 0 ? currentIndex + 1 : currentIndex - 1);
                }
            }, { passive: true });

            /* ─── events ─────────────────────────────────────────── */
            $thumbsWrap.on('click', '.pgc__thumb', function () {
                goTo($(this).index());
            });

            $prevBtn.on('click', function () { goTo(currentIndex - 1); });
            $nextBtn.on('click', function () { goTo(currentIndex + 1); });

            /* Keyboard arrow support when gallery is focused */
            $stage.attr('tabindex', '0').on('keydown', function (e) {
                if (e.key === 'ArrowLeft') goTo(currentIndex - 1);
                if (e.key === 'ArrowRight') goTo(currentIndex + 1);
            });

            /* Pause any playing video when navigating away */
            slides().on('transitionend', function () {
                slides().not('.is-active').find('video').each(function () {
                    this.pause();
                });
            });

            /* ─── init ───────────────────────────────────────────── */
            (function init() {
                goTo(0, true);
            })();

            /* ─── public API ─────────────────────────────────────── */
            return { goTo: goTo, setVariantImage: setVariantImage };

        }(jQuery));
    </script>

    <script>
        /* ═══════════════════════════════════════════════════════════
           QTY controls + FBT + share (unchanged logic, cleaned up)
        ═══════════════════════════════════════════════════════════ */
        $(document).ready(function () {

            /* ── Qty input ─────────────────────────────────────── */
            var qtyInput = document.getElementById('qty');
            var minusBtn = document.querySelector('.qty-minus');
            var plusBtn = document.querySelector('.qty-plus');

            if (minusBtn) {
                minusBtn.addEventListener('click', function () {
                    var v = parseInt(qtyInput.value) || 1;
                    if (v > 1) qtyInput.value = v - 1;
                });
            }
            if (plusBtn) {
                plusBtn.addEventListener('click', function () {
                    qtyInput.value = (parseInt(qtyInput.value) || 1) + 1;
                });
            }
            if (qtyInput) {
                qtyInput.addEventListener('input', function () {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (!this.value || parseInt(this.value) < 1) this.value = 1;
                });
            }

            /* ── Coupon copy ───────────────────────────────────── */
            $('.copy-coupon-code').on('click', function () {
                var code = $(this).data('coupon-code');
                var $span = $(this).find('.button-text');
                $span.text('Copying…');
                navigator.clipboard.writeText(code).then(function () {
                    $span.text('Copied!');
                    setTimeout(function () { $span.text('Copy'); }, 1500);
                });
            });

            /* ── FBT total ─────────────────────────────────────── */
            $('.fbt-checkbox').on('change', updateFbtTotal);
            function updateFbtTotal() {
                var total = 0;
                $('.fbt-product-item').each(function () {
                    if ($(this).find('.fbt-checkbox').is(':checked')) {
                        total += parseFloat($(this).data('product-price')) || 0;
                    }
                });
                $('.fbt-total-amount').text('₹' + total.toLocaleString('en-IN'));
            }

            /* ── FBT add-all ───────────────────────────────────── */
            $('.fbt-add-to-cart-btn').on('click', function () {
                var $btn = $(this);
                var selected = [];
                $('.fbt-product-item').each(function () {
                    if ($(this).find('.fbt-checkbox').is(':checked')) {
                        selected.push({
                            product_id: $(this).data('product-id'),
                            product_name: $(this).data('product-name'),
                            variation_id: $(this).data('variation-id') || '',
                            quantity: 1
                        });
                    }
                });
                if (!selected.length) { toastr.warning('Please select at least one product.'); return; }
                $btn.prop('disabled', true).find('.btn-text').hide();
                $btn.find('.btn-loading').show();
                addProductsSequentially(selected, 0, $btn);
            });

            function addProductsSequentially(products, i, $btn) {
                if (i >= products.length) {
                    $btn.prop('disabled', false).find('.btn-text').show();
                    $btn.find('.btn-loading').hide();
                    toastr.success('Products added to cart!');
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                    return;
                }
                $.ajax({
                    url: '{{ route('addToCart') }}',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { product_id: products[i].product_id, variation_id: products[i].variation_id, attributes: [], quantity: 1 },
                    complete: function (res) {
                        var r = res.responseJSON || {};
                        if (r.success) updateCartUI(r);
                        addProductsSequentially(products, i + 1, $btn);
                    }
                });
            }

            function updateCartUI(r) {
                $('#cart-count-span').html('(' + r.cart_count + ' Items)');
                $('.cart-total-span').html(r.cart_total);
                if (r.cart_count > 0) {
                    if ($('#cart_count_header').length) {
                        $('#cart_count_header').html(r.cart_count).show();
                    } else {
                        $('a.cart__btn').append('<ins id="cart_count_header"></ins>');
                        $('#cart_count_header').html(r.cart_count).show();
                    }
                } else {
                    $('#cart_count_header').hide();
                }
            }
        });

        /* ── iOS video fix ───────────────────────────────────────── */
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            document.querySelectorAll('video').forEach(function (v) {
                v.setAttribute('playsinline', '');
                v.setAttribute('webkit-playsinline', '');
            });
        }
    </script>
@endsection
