{{-- =============================================
     PRODUCT LIST — Blog-card style
     Dark theme: #181818 card | #e7c840 gold accent
     ─────────────────────────────────────────────
     CONTROLLER — eager-load images to avoid N+1:
       $products = Product::with(['category','images','variations','cart'])
                          ->get();
     ============================================= --}}

<style>
    /* ─────────────────────────────────────────────
       PRODUCT CARD  (mirrors .blog-card exactly)
    ───────────────────────────────────────────── */
    .product-card {
        background: #181818;
        border: 1px solid #2a2a2a;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        text-decoration: none !important;
        color: inherit !important;
        margin: 6px 4px 16px;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-6px);
        border-color: #e7c840;
        box-shadow: 0 12px 40px rgba(231, 200, 64, 0.12);
    }

    /* ── Wishlist ── */
    .product-card__wishlist {
        position: absolute;
        top: 13px;
        right: 13px;
        z-index: 10;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(24, 24, 24, 0.75);
        backdrop-filter: blur(4px);
        border: 1px solid #333;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
        text-decoration: none !important;
    }

    .product-card__wishlist:hover,
    .product-card__wishlist.liked {
        border-color: #e7c840;
        background: rgba(231, 200, 64, 0.12);
    }

    .product-card__wishlist svg {
        stroke: #888;
        fill: none;
        transition: stroke 0.2s, fill 0.2s;
    }

    .product-card__wishlist.liked svg {
        stroke: #e7c840;
        fill: #e7c840;
    }

    .product-card__wishlist:hover svg {
        stroke: #e7c840;
    }

    /* ── Thumbnail ── */
    .product-card__thumb {
        position: relative;
        height: 195px;
        overflow: hidden;
        flex-shrink: 0;
        background: #111;
    }

    /* Image slider track */
    .product-card__track {
        display: flex;
        height: 100%;
        transition: transform 0.5s ease;
        will-change: transform;
    }

    .product-card__slide {
        min-width: 100%;
        flex-shrink: 0;
        height: 100%;
    }

    .product-card__slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        filter: brightness(0.88);
        transition: transform 0.5s ease, filter 0.3s ease;
    }

    .product-card:hover .product-card__slide img {
        transform: scale(1.06);
        filter: brightness(1);
    }

    /* Bottom gradient fade — same as blog card */
    .product-card__thumb::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 55px;
        background: linear-gradient(to top, #181818, transparent);
        pointer-events: none;
        z-index: 1;
    }

    /* Category pill — top left, gold, same as blog-card__cat */
    .product-card__cat {
        position: absolute;
        top: 13px;
        left: 13px;
        background: #e7c840;
        color: #0d0d0d;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 20px;
        z-index: 2;
        font-family: sans-serif;
    }

    /* ── Prev / Next Arrow Buttons ── */
    .product-card__arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(24, 24, 24, 0.78);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.22s ease, background 0.2s, border-color 0.2s, transform 0.2s;
        /* override the card-level translateY so we can tweak independently */
        will-change: opacity;
        text-decoration: none !important;
        color: #ccc;
    }

    .product-card__arrow--prev { left: 9px; }
    .product-card__arrow--next { right: 9px; }

    /* Show arrows only when the card is hovered */
    .product-card:hover .product-card__arrow {
        opacity: 1;
        pointer-events: auto;
    }

    .product-card__arrow:hover {
        background: rgba(231, 200, 64, 0.88);
        border-color: #e7c840;
        color: #0d0d0d;
        /* keep the translateY(-50%) but add a tiny scale pop */
        transform: translateY(-50%) scale(1.1);
    }

    .product-card__arrow svg {
        display: block;
        flex-shrink: 0;
    }

    /* Dot indicators */
    .product-card__dots {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 5px;
        z-index: 3;
    }

    .product-card__dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: background 0.25s, width 0.25s;
    }

    .product-card__dot.active {
        background: #e7c840;
        border-color: #e7c840;
        width: 16px;
        border-radius: 3px;
    }

    /* ── Card Body ── */
    .product-card__body {
        padding: 18px 18px 14px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* Meta row: category name + discount badge */
    .product-card__meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .product-card__meta-cat {
        font-size: 10.5px;
        color: #e7c840;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-family: sans-serif;
    }

    .product-card__badge {
        font-size: 10px;
        font-weight: 800;
        font-family: sans-serif;
        color: #0d0d0d;
        background: #e7c840;
        padding: 2px 8px;
        border-radius: 10px;
        letter-spacing: 0.04em;
    }

    /* Title */
    .product-card__title {
        font-size: 15.5px;
        font-weight: 700;
        color: #f0f0f0;
        line-height: 1.45;
        margin: 0 0 12px;
        font-family: 'Georgia', serif;
        transition: color 0.2s ease;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-card:hover .product-card__title {
        color: #e7c840;
    }

    /* Price row */
    .product-card__price {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-top: auto;
    }

    .product-card__price-new {
        font-size: 18px;
        font-weight: 800;
        color: #f0f0f0;
        font-family: sans-serif;
    }

    .product-card__price-old {
        font-size: 13px;
        color: #555;
        text-decoration: line-through;
        font-family: sans-serif;
    }

    /* ── Card Footer ── */
    .product-card__footer {
        border-top: 1px solid #252525;
        padding: 12px 18px 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    /* Add to cart button */
    .product-card__cart-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 0;
        background: transparent;
        border: 1.5px solid #e7c840;
        border-radius: 8px;
        color: #e7c840;
        font-size: 11.5px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-family: sans-serif;
        cursor: pointer;
        transition: background 0.22s, color 0.22s, transform 0.15s;
        text-decoration: none !important;
    }

    .product-card__cart-btn:hover {
        background: #e7c840;
        color: #0d0d0d !important;
        transform: translateY(-1px);
    }

    .product-card__cart-btn.disabled {
        border-color: #333;
        color: #444;
        cursor: not-allowed;
    }

    .product-card__cart-btn.disabled:hover {
        background: transparent;
        color: #444 !important;
        transform: none;
    }

    /* Quantity input */
    .product-card__qty {
        width: 54px;
        flex-shrink: 0;
        background: #111 !important;
        border: 1.5px solid #2a2a2a !important;
        color: #f0f0f0 !important;
        border-radius: 8px !important;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        padding: 8px 4px !important;
        font-family: sans-serif;
        transition: border-color 0.2s;
    }

    .product-card__qty:focus {
        border-color: #e7c840 !important;
        outline: none;
        box-shadow: none !important;
    }

    .input-group {
        position: relative;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        -ms-flex-align: stretch;
        align-items: stretch;
        width: 30%;
    }
</style>
<style>
    .product-card {
        background: #ffffff !important;
        border: 1px solid rgba(75, 59, 46, 0.12) !important;
        box-shadow: 0 16px 36px rgba(75, 59, 46, 0.08);
    }

    .product-card:hover {
        border-color: rgba(231, 200, 64, 0.55) !important;
        box-shadow: 0 22px 44px rgba(75, 59, 46, 0.12);
    }

    .product-card__body {
        background: #ffffff;
    }

    .product-card__wishlist {
        background: rgba(255, 255, 255, 0.92);
        border-color: rgba(75, 59, 46, 0.12);
        box-shadow: 0 10px 24px rgba(75, 59, 46, 0.12);
    }

    .product-card__wishlist:hover,
    .product-card__wishlist.liked {
        background: #fff6dd;
        border-color: rgba(231, 200, 64, 0.55);
    }

    .product-card__wishlist svg {
        stroke: #7d6f63;
    }

    .product-card__thumb {
        background: #f7efe1;
    }

    .product-card__slide img {
        filter: brightness(0.97);
    }

    .product-card__thumb::after {
        background: linear-gradient(to top, #ffffff, transparent);
    }

    .product-card__arrow {
        background: rgba(255, 255, 255, 0.94);
        border-color: rgba(75, 59, 46, 0.12);
        color: #433327;
        box-shadow: 0 10px 24px rgba(75, 59, 46, 0.12);
    }

    .product-card__arrow:hover {
        background: linear-gradient(135deg, #f7e496 0%, #e7c840 100%);
        border-color: #e7c840;
        color: #433327;
    }

    .product-card__dot {
        background: rgba(67, 51, 39, 0.18);
        border-color: rgba(67, 51, 39, 0.12);
    }

    .product-card__meta-cat {
        color: #b9910d;
    }

    .product-card__title,
    .product-card__price-new {
        color: #433327;
    }

    .product-card:hover .product-card__title {
        color: #b9910d;
    }

    .product-card__price-old {
        color: #9b8f82;
    }

    .product-card__footer {
        background: #fffdfa;
        border-top: 1px solid rgba(75, 59, 46, 0.08);
    }

    .product-card__cart-btn {
        background: #ffffff;
        border-color: rgba(231, 200, 64, 0.55);
        color: #433327;
    }

    .product-card__cart-btn:hover {
        background: linear-gradient(135deg, #f7e496 0%, #e7c840 100%);
        color: #433327 !important;
    }

    .product-card__cart-btn.disabled {
        background: #faf6ef;
        border-color: rgba(75, 59, 46, 0.12);
        color: #a49687;
    }

    .product-card__cart-btn.disabled:hover {
        background: #faf6ef;
        color: #a49687 !important;
    }

    .product-card__qty {
        background: #ffffff !important;
        border-color: rgba(75, 59, 46, 0.14) !important;
        color: #433327 !important;
    }

    .product-card__qty:focus {
        border-color: rgba(231, 200, 64, 0.8) !important;
        box-shadow: 0 0 0 0.2rem rgba(231, 200, 64, 0.14) !important;
    }

    @media (max-width: 767.98px) {
        .product-card__thumb {
            height: auto;
            aspect-ratio: 5 / 4;
        }

        .product-card__slide img {
            object-position: center;
        }

        .product-card:hover .product-card__slide img {
            transform: none;
            filter: brightness(0.97);
        }
    }
</style>

@foreach ($products as $product)
    @if ($product->category != null)
        @php
            $isInWishlist = false;
            $sessionWishlistData = session('wishlist') ?? [];
            if (auth()->user()) {
                $isInWishlist = $wishlist->contains('product_id', $product->product_id);
            } else {
                foreach ($sessionWishlistData as $key => $value) {
                    if ($value['product_id'] == $product->product_id) {
                        $isInWishlist = true;
                    }
                }
            }
        @endphp

        @php
            $mrp = $product->variations[0]->mrp ?? $product->base_mrp;
            $price = $product->variations[0]->price ?? $product->base_price;
            $percentOff = $mrp > 0 && $price > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;

            // Build image list — access ->images directly so Laravel lazy-loads
            // if the controller didn't eager-load. Use ->loadMissing() in the
            // controller (see comment below) to avoid N+1 in production.
            $sliderImages = collect([$product->product_image]);
            foreach ($product->images as $img) {
                if (!empty($img->image)) {
                    $sliderImages->push($img->image);
                }
            }
            $sliderId = 'pslider-' . $product->product_id;
            $hasMultiple = $sliderImages->count() > 1;

            $categoryTitle = $product->category->title ?? null;
        @endphp

        <div class="col-md-3">
            <div class="product-card" >

                {{-- ── Wishlist ── --}}
                <a data-id="{{ $product->product_id }}" onclick="addTowishListClass(this)"
                    title="{{ $isInWishlist ? 'Added to wishlist' : 'Add to wishlist' }}"
                    class="product-card__wishlist {{ $isInWishlist ? 'remove-from-wishlist liked' : 'add-to-wishlist' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                </a>

                {{-- ── Thumbnail + Slider ── --}}
                <a href="{{ route('single.product.view', $product->slug) }}" style="text-decoration:none;">

                    <div class="product-card__thumb" id="{{ $sliderId }}" data-slider-id="{{ $sliderId }}"
                        data-count="{{ $sliderImages->count() }}">

                        {{-- Category pill --}}
                        @if ($categoryTitle)
                            <span class="product-card__cat">{{ $categoryTitle }}</span>
                        @endif

                        {{-- ── Prev / Next arrows (only rendered when multiple images) ── --}}
                        @if ($hasMultiple)
                            <button type="button"
                                class="product-card__arrow product-card__arrow--prev"
                                data-slider="{{ $sliderId }}"
                                data-dir="-1"
                                aria-label="Previous image">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </button>

                            <button type="button"
                                class="product-card__arrow product-card__arrow--next"
                                data-slider="{{ $sliderId }}"
                                data-dir="1"
                                aria-label="Next image">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        @endif

                        {{-- Image track --}}
                        <div class="product-card__track">
                            @foreach ($sliderImages as $imgPath)
                                <div class="product-card__slide">
                                    <img src="{{ asset('storage/' . $imgPath) }}" alt="{{ $product->product_name }}"
                                        loading="lazy">
                                </div>
                            @endforeach
                        </div>

                        {{-- Dots --}}
                        @if ($hasMultiple)
                            <div class="product-card__dots">
                                @foreach ($sliderImages as $i => $imgPath)
                                    <span class="product-card__dot {{ $i === 0 ? 'active' : '' }}"
                                        data-index="{{ $i }}"></span>
                                @endforeach
                            </div>
                        @endif

                    </div>{{-- /.product-card__thumb --}}

                    {{-- ── Body ── --}}
                    <div class="product-card__body">

                        {{-- Meta row --}}
                        <div class="product-card__meta">
                            <span class="product-card__meta-cat">
                                {{ $categoryTitle ?? 'Product' }}
                            </span>
                            @if ($percentOff > 0)
                                <span class="product-card__badge">{{ $percentOff }}% OFF</span>
                            @endif
                        </div>

                        {{-- Title --}}
                        <h3 class="product-card__title">{{ $product->product_name }}</h3>

                        {{-- Price --}}
                        <div class="product-card__price">
                            <span class="product-card__price-new">
                                ₹{{ number_format($price == 0 ? $mrp : $price, 0, '.', ',') }}
                            </span>
                            @if ($price > 0 && $mrp > $price)
                                <span class="product-card__price-old old-price">
                                    ₹{{ number_format($mrp, 0, '.', ',') }}
                                </span>
                            @endif
                        </div>

                    </div>{{-- /.product-card__body --}}

                </a>{{-- /product link --}}

                {{-- ── Footer: Cart + Qty ── --}}
                <div class="product-card__footer" >
                    @if ($product->variation->stock > 0)
                        @php
                            $cartQty = 1;
                            if (auth()->check()) {
                                $cartQty = $product->cart->quantity ?? 1;
                            } else {
                                $cartKey = $product->product_id . '-' . $product->variation->id;
                                $cartQty = session("cart.$cartKey.quantity", 1);
                            }
                        @endphp

                        <a class="product-card__cart-btn add-to-cart-home add-to-cart-component"  data-product-id="{{ $product->product_id }}">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5" >
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <path d="M16 10a4 4 0 01-8 0" />
                            </svg>
                            Add to Cart
                        </a>

                        <input type="number" class="product-card__qty form-control qty product_quantity" 
                            value="{{ $cartQty }}" min="1" max="10" step="1" data-decimals="0"
                            required>
                    @else
                        <a class="product-card__cart-btn disabled" style="width:100%;">
                            Out of Stock
                        </a>
                    @endif
                </div>

            </div>{{-- /.product-card --}}
        </div>
    @endif
@endforeach

{{-- ── Image Slider Script ── --}}
<script>
    (function() {
        var INTERVAL = 2500;

        var registry = {};

        function goTo(id, index) {
            var s = registry[id];
            if (!s) return;
            s.current = (index + s.count) % s.count;
            s.track.style.transform = 'translateX(-' + (s.current * 100) + '%)';
            s.dots.forEach(function(d, i) {
                d.classList.toggle('active', i === s.current);
            });
        }

        function init(el) {
            var id = el.getAttribute('data-slider-id');
            var count = parseInt(el.getAttribute('data-count'), 10);
            if (!id || count < 2) return; // single image — nothing to do

            var track = el.querySelector('.product-card__track');
            var dots = Array.from(el.querySelectorAll('.product-card__dot'));

            registry[id] = {
                el: el,
                track: track,
                dots: dots,
                count: count,
                current: 0,
                paused: false
            };

            // Dot clicks
            dots.forEach(function(dot) {
                dot.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    goTo(id, parseInt(dot.getAttribute('data-index'), 10));
                });
            });

            // ── Arrow button clicks ──
            // Buttons live inside the thumb, query from the thumb element itself
            el.querySelectorAll('.product-card__arrow').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // prevent navigating to product page
                    var dir = parseInt(btn.getAttribute('data-dir'), 10); // -1 or +1
                    goTo(id, registry[id].current + dir);
                });
            });

            // Pause auto-slide on card hover
            var card = el.closest('.product-card');
            if (card) {
                card.addEventListener('mouseenter', function() {
                    registry[id].paused = true;
                });
                card.addEventListener('mouseleave', function() {
                    registry[id].paused = false;
                });
            }

            // Auto-advance
            setInterval(function() {
                if (!registry[id].paused) goTo(id, registry[id].current + 1);
            }, INTERVAL);
        }

        function initAll() {
            document.querySelectorAll('[data-slider-id]').forEach(function(el) {
                if (!el.getAttribute('data-slider-ready')) {
                    el.setAttribute('data-slider-ready', '1');
                    init(el);
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }

        window.initProductSliders = initAll;
    })();
</script>
