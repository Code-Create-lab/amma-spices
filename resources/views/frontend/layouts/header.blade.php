{{-- Add this CSS in your <head> or in a @push('styles') section --}}
{{-- @push('styles') --}}
<style>
    /* ── Clean Transparent Header — Everest style ── */
    .header-28 {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
        background: transparent !important;
        box-shadow: none !important;
        border-bottom: none !important;
    }

    .sticky-wrapper {
        background: transparent !important;
        box-shadow: none !important;
    }

    .header-top {
        background: transparent !important;
    }

    .header-middle {
        background: transparent !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        border-bottom: none !important;
    }

    /* ── Bold white nav links with strong shadow for readability ── */
    .main-nav .menu>li>a {
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 1.0rem;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        text-shadow:
            0 1px 4px rgba(0, 0, 0, 0.85),
            0 0 10px rgba(0, 0, 0, 0.5);
        padding: 10px 18px;
        transition: color 0.2s ease;
    }

    .main-nav .menu>li>a:hover {
        color: #f0c36d !important;
    }

    /* Active page — gold underline */
    .main-nav .menu>li.active>a {
        color: #f0c36d !important;
    }

    .main-nav .menu>li.active>a::after {
        content: '';
        display: block;
        margin: 3px auto 0;
        width: 50%;
        height: 2px;
        background: #f0c36d;
        border-radius: 2px;
    }

    /* ── Logo shadow to pop against any banner ── */
    .header-28 .logo img {
        filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.45));
    }
    
</style>
<style>
    .header-28 {
        background: rgba(255, 255, 255, 0.94) !important;
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        box-shadow: 0 14px 34px rgba(75, 59, 46, 0.08) !important;
        border-bottom: 1px solid rgba(75, 59, 46, 0.08) !important;
    }

    .sticky-wrapper,
    .header-top,
    .header-middle {
        background: transparent !important;
        box-shadow: none !important;
    }

    .header-middle {
        border-bottom: none !important;
    }

    .main-nav .menu > li > a {
        color: #433327 !important;
        text-shadow: none !important;
    }

    .main-nav .menu > li > a:hover,
    .main-nav .menu > li.active > a {
        color: #b9910d !important;
    }

    .main-nav .menu > li.active > a::after {
        background: #e7c840;
    }

    .header-28 .header-right .icon i,
    .header-28 .wishlist-link,
    .header-28 .cart-dropdown .dropdown-toggle,
    .header-28 .cart-txt,
    .header-28 .mobile-menu-toggler,
    .header-28 .mobile-menu-toggler i {
        color: #433327 !important;
        text-shadow: none !important;
    }

    .header-28 .header-right .icon i:hover,
    .header-28 .wishlist-link:hover,
    .header-28 .cart-dropdown:hover .dropdown-toggle,
    .header-28 .mobile-menu-toggler:hover i {
        color: #b9910d !important;
    }

    .header-28 .cart-count,
    .header-28 .wishlist-count {
        background: linear-gradient(135deg, #f6e28e 0%, #e7c840 100%) !important;
        color: #433327 !important;
        box-shadow: 0 8px 18px rgba(231, 200, 64, 0.26);
    }

    .header-28 .dropdown-menu,
    .header-28 ul.sub-menu-l {
        background: #ffffff !important;
        border: 1px solid rgba(75, 59, 46, 0.12) !important;
        box-shadow: 0 14px 30px rgba(75, 59, 46, 0.12);
    }

    .header-28 .dropdown-cart-products .product {
        border-bottom-color: rgba(75, 59, 46, 0.08);
    }

    .header-28 .cart-dropdown .product-title a,
    .header-28 .cart-dropdown .product-cart-details,
    .header-28 .cart-dropdown .product_details_para,
    .header-28 .dropdown-cart-total,
    .header-28 .dropdown-cart-total .cart-total-price,
    .header-28 ul.sub-menu-l li a {
        color: #433327 !important;
    }

    .header-28 .cart-dropdown .product-title a:hover,
    .header-28 .cart-dropdown .product-title a:focus,
    .header-28 ul.sub-menu-l li a:hover {
        color: #b9910d !important;
    }

    .header-28 .cart-dropdown .btn-remove {
        color: #9b8f82 !important;
    }

    .header-28 .cart-dropdown .btn-remove:hover,
    .header-28 .cart-dropdown .btn-remove:focus {
        color: #b9910d !important;
    }

    .header-28 .logo img {
        filter: none;
    }
</style>
{{-- @endpush --}}

{{-- ── HEADER MARKUP (unchanged structure, classes preserved) ── --}}
<header class="header header-28 sticky-header">

    <div class="sticky-wrapper">

        <div class="header-top">
            @livewire('nav-cart')
        </div>

        <div class="header-middle">
            <div class="container">
                <div class="header-left">

                    <button class="mobile-menu-toggler" id="mobile-bar">
                        <span class="sr-only">Toggle mobile menu</span>
                        <i class="icon-bars"></i>
                    </button>

                    <a href="{{ route('index') }}" class="logo">
                        <img src="{{ asset('assets/img/logo-bodhi.png') }}" alt="Logo" width="50"
                            height="25">
                    </a>

                    <nav class="main-nav">
                        <ul class="menu sf-arrows">

                            <li
                                class="megamenu-container megamenu-list {{ Route::currentRouteName() == 'index' ? 'active' : '' }}">
                                <a href="{{ route('index') }}">Home</a>
                            </li>

                            <li
                                class="megamenu-list {{ Route::currentRouteName() == 'shop.page.index' ? 'active' : '' }}">
                                <a href="{{ route('shop.page.index') }}">Shop</a>
                            </li>

                            <li
                                class="megamenu-list {{ Route::currentRouteName() == 'frontend.gallery' ? 'active' : '' }}">
                                <a href="{{ route('frontend.gallery') }}">Gallery</a>
                            </li>

                            <li
                                class="megamenu-list {{ Route::currentRouteName() == 'frontend.about-us' ? 'active' : '' }}">
                                <a href="{{ route('frontend.about-us') }}">About</a>
                            </li>

                            <li
                                class="megamenu-list {{ Route::currentRouteName() == 'contact-us.index' ? 'active' : '' }}">
                                <a href="{{ route('contact-us.index') }}">Contact Us</a>
                            </li>

                        </ul>
                    </nav>

                    <div class="header-st-bar">
                        {{-- @livewire('nav-cart') --}}
                    </div>

                </div>
            </div>
        </div>

    </div>
</header>

{{-- ── Scroll darkening script ── --}}
{{-- @push('scripts') --}}
<script>
    (function() {
        const header = document.querySelector('.header-28');
        if (!header) return;
        window.addEventListener('scroll', function() {
            header.classList.toggle('scrolled', window.scrollY > 50);
        }, {
            passive: true
        });
    })();
</script>
{{-- @endpush --}}
