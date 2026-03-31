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
