@extends('frontend.layouts.app')

@section('content')
    {{-- @push('styles') --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --gold: #e7c840;
            --gold-dim: #c9a f20;
            --black: #000000;
            --off-black: #0e0e0e;
            --dark: #141414;
            --white: #ffffff;
            --grey: #888888;
            --light-grey: #1e1e1e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .gallery-page {
            background: var(--off-black);
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            color: var(--white);
            overflow-x: hidden;
        }

        /* ── HERO ── */
        .gallery-hero {
            position: relative;
            padding: 100px 40px 60px;
            text-align: center;
            overflow: hidden;
        }

        .gallery-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 60% at 50% 0%, rgba(231, 200, 64, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .gallery-hero-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--gold);
            border: 1px solid rgba(231, 200, 64, 0.35);
            padding: 6px 18px;
            border-radius: 100px;
            margin-bottom: 24px;
            animation: fadeUp 0.6s ease both;
        }

        .gallery-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(42px, 6vw, 78px);
            font-weight: 900;
            line-height: 1.05;
            color: var(--white);
            letter-spacing: -1px;
            animation: fadeUp 0.7s 0.1s ease both;
        }

        .gallery-hero h1 span {
            color: var(--gold);
        }

        .gallery-hero p {
            margin: 20px auto 0;
            max-width: 520px;
            font-size: 16px;
            font-weight: 300;
            color: var(--grey);
            line-height: 1.7;
            animation: fadeUp 0.7s 0.2s ease both;
        }

        /* ── FILTER TABS ── */
        .gallery-filters {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 40px 40px 0;
            animation: fadeUp 0.7s 0.3s ease both;
        }

        .filter-btn {
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 9px 22px;
            border-radius: 100px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: transparent;
            color: var(--grey);
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .filter-btn:hover {
            border-color: var(--gold);
            color: var(--gold);
        }

        .filter-btn.active {
            background: var(--gold);
            border-color: var(--gold);
            color: var(--black);
        }

        /* ── DIVIDER ── */
        .gallery-divider {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 40px 60px 0;
            max-width: 1400px;
            margin: 0 auto;
        }

        .gallery-divider span {
            font-size: 12px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--grey);
            white-space: nowrap;
        }

        .gallery-divider::after,
        .gallery-divider::before {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
        }

        /* ── MASONRY GRID ── */
        .gallery-grid {
            columns: 4 280px;
            column-gap: 14px;
            padding: 30px 40px 80px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .gallery-item {
            break-inside: avoid;
            margin-bottom: 14px;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            cursor: pointer;
            display: block;
            animation: fadeUp 0.6s ease both;
        }

        .gallery-item:nth-child(1) {
            animation-delay: 0.05s;
        }

        .gallery-item:nth-child(2) {
            animation-delay: 0.10s;
        }

        .gallery-item:nth-child(3) {
            animation-delay: 0.15s;
        }

        .gallery-item:nth-child(4) {
            animation-delay: 0.20s;
        }

        .gallery-item:nth-child(5) {
            animation-delay: 0.25s;
        }

        .gallery-item:nth-child(6) {
            animation-delay: 0.30s;
        }

        .gallery-item:nth-child(7) {
            animation-delay: 0.35s;
        }

        .gallery-item:nth-child(8) {
            animation-delay: 0.40s;
        }

        .gallery-item:nth-child(9) {
            animation-delay: 0.45s;
        }

        .gallery-item:nth-child(10) {
            animation-delay: 0.50s;
        }

        .gallery-item:nth-child(11) {
            animation-delay: 0.55s;
        }

        .gallery-item:nth-child(12) {
            animation-delay: 0.60s;
        }

        .gallery-item img {
            width: 100%;
            display: block;
            transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        /* Gold border on hover */
        .gallery-item::before {
            content: '';
            position: absolute;
            inset: 0;
            border: 2px solid transparent;
            border-radius: 10px;
            transition: border-color 0.3s ease;
            z-index: 2;
            pointer-events: none;
        }

        /* Dark overlay */
        .gallery-item-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top,
                    rgba(0, 0, 0, 0.85) 0%,
                    rgba(0, 0, 0, 0.2) 50%,
                    transparent 100%);
            opacity: 0;
            transition: opacity 0.35s ease;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
            border-radius: 10px;
        }

        .gallery-item:hover img {
            transform: scale(1.06);
        }

        .gallery-item:hover::before {
            border-color: var(--gold);
        }

        .gallery-item:hover .gallery-item-overlay {
            opacity: 1;
        }

        .overlay-tag {
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 4px;
        }

        .overlay-title {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--white);
            line-height: 1.3;
        }

        /* Expand icon */
        .overlay-icon {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(231, 200, 64, 0.15);
            border: 1px solid var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(0.7);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .overlay-icon svg {
            width: 14px;
            height: 14px;
            stroke: var(--gold);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .gallery-item:hover .overlay-icon {
            opacity: 1;
            transform: scale(1);
        }

        /* ── FEATURED STRIP ── */
        .featured-strip {
            background: var(--gold);
            padding: 22px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 40px;
            overflow: hidden;
        }

        .featured-strip p {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--black);
            white-space: nowrap;
        }

        .featured-strip-dot {
            width: 5px;
            height: 5px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── LIGHTBOX ── */
        .lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(6px);
        }

        .lightbox.open {
            display: flex;
        }

        .lightbox-inner {
            position: relative;
            max-width: 900px;
            width: 100%;
            animation: lightboxIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        .lightbox-inner img {
            width: 100%;
            border-radius: 12px;
            display: block;
            max-height: 80vh;
            object-fit: contain;
        }

        .lightbox-caption {
            margin-top: 16px;
            text-align: center;
        }

        .lightbox-caption .overlay-tag {
            display: block;
            margin-bottom: 4px;
        }

        .lightbox-caption .overlay-title {
            font-size: 22px;
        }

        .lightbox-close {
            position: absolute;
            top: -46px;
            right: 0;
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .lightbox-close:hover {
            background: var(--gold);
        }

        .lightbox-close svg {
            width: 14px;
            height: 14px;
            stroke: white;
            fill: none;
            stroke-width: 2.5;
            stroke-linecap: round;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(231, 200, 64, 0.12);
            border: 1px solid var(--gold);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .lightbox-nav:hover {
            background: var(--gold);
        }

        .lightbox-nav svg {
            width: 16px;
            height: 16px;
            stroke: var(--gold);
            fill: none;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lightbox-nav:hover svg {
            stroke: var(--black);
        }

        .lightbox-nav.prev {
            left: -60px;
        }

        .lightbox-nav.next {
            right: -60px;
        }

        /* ── CTA SECTION ── */
        .gallery-cta {
            text-align: center;
            padding: 60px 40px 100px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .gallery-cta p {
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 16px;
        }

        .gallery-cta h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 4vw, 46px);
            font-weight: 700;
            color: var(--white);
            margin-bottom: 32px;
        }

        .cta-btn {
            display: inline-block;
            background: var(--gold);
            color: var(--black);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 15px 40px;
            border-radius: 8px;
            text-decoration: none;
            transition: opacity 0.2s, transform 0.2s;
        }

        .cta-btn:hover {
            opacity: 0.88;
            transform: translateY(-2px);
            text-decoration: none;
            color: var(--black);
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes lightboxIn {
            from {
                opacity: 0;
                transform: scale(0.88);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .gallery-hero {
                padding: 60px 20px 40px;
            }

            .gallery-filters {
                padding: 30px 20px 0;
            }

            .gallery-grid {
                columns: 2 140px;
                padding: 24px 16px 60px;
                column-gap: 10px;
            }

            .gallery-grid .gallery-item {
                margin-bottom: 10px;
            }

            .lightbox-nav.prev {
                left: -10px;
            }

            .lightbox-nav.next {
                right: -10px;
            }

            .featured-strip {
                gap: 20px;
                padding: 18px 20px;
            }

            .featured-strip p {
                font-size: 14px;
            }
        }
    </style>
    {{-- @endpush --}}


    <div class="gallery-page">

        {{-- ── HERO ── --}}
        <section class="gallery-hero">
            <div class="gallery-hero-tag">Our Gallery</div>
            <h1>Moments &amp; <span>Flavours</span></h1>
            <p>A glimpse into our world — from vibrant spice events and product showcases to the heart of every blend we
                craft.</p>
        </section>

        {{-- ── FILTER TABS ── --}}
        <div class="gallery-filters">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="events">Events</button>
            <button class="filter-btn" data-filter="products">Products</button>
            <button class="filter-btn" data-filter="behind-scenes">Behind the Scenes</button>
            <button class="filter-btn" data-filter="packaging">Packaging</button>
        </div>

        {{-- ── SECTION LABEL ── --}}
        <div class="gallery-divider">
            <span>Explore</span>
        </div>

        {{-- ── MASONRY GALLERY ── --}}
        <div class="gallery-grid" id="galleryGrid">

            <div class="gallery-item" data-category="products" data-title="Signature Spice Blends" data-tag="Products"
                onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=600&q=80" alt="Spice Blends"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Products</div>
                    <div class="overlay-title">Signature Spice Blends</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="events" data-title="Spice Festival 2024" data-tag="Events"
                onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1529543544282-ea669407fca3?w=600&q=80" alt="Spice Festival"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Events</div>
                    <div class="overlay-title">Spice Festival 2024</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="behind-scenes" data-title="Handcrafting Each Blend"
                data-tag="Behind the Scenes" onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1505253716362-afaea1d3d1af?w=600&q=80" alt="Handcrafting"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Behind the Scenes</div>
                    <div class="overlay-title">Handcrafting Each Blend</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="packaging" data-title="Premium Jar Collection" data-tag="Packaging"
                onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80" alt="Packaging"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Packaging</div>
                    <div class="overlay-title">Premium Jar Collection</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="events" data-title="Market Day Showcase" data-tag="Events"
                onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1470119693884-47d3a1d1f180?w=600&q=80" alt="Market Day"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Events</div>
                    <div class="overlay-title">Market Day Showcase</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="products" data-title="Rasam & Sambar Powders" data-tag="Products"
                onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1615485500704-8e990f9900f7?w=600&q=80" alt="Rasam Powder"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Products</div>
                    <div class="overlay-title">Rasam &amp; Sambar Powders</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="behind-scenes" data-title="Sun-Drying Spices"
                data-tag="Behind the Scenes" onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=600&q=80" alt="Sun drying"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Behind the Scenes</div>
                    <div class="overlay-title">Sun-Drying Spices</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="events" data-title="Cooking Demo — Chennai" data-tag="Events"
                onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&q=80" alt="Cooking Demo"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Events</div>
                    <div class="overlay-title">Cooking Demo — Chennai</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="packaging" data-title="Gift Box Sets" data-tag="Packaging"
                onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600&q=80" alt="Gift Boxes"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Packaging</div>
                    <div class="overlay-title">Gift Box Sets</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="products" data-title="Chettinad Masala" data-tag="Products"
                onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?w=600&q=80" alt="Chettinad Masala"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Products</div>
                    <div class="overlay-title">Chettinad Masala</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="behind-scenes" data-title="Stone Grinding Process"
                data-tag="Behind the Scenes" onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=600&q=80" alt="Stone Grinding"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Behind the Scenes</div>
                    <div class="overlay-title">Stone Grinding Process</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

            <div class="gallery-item" data-category="events" data-title="Food Expo Stall 2023" data-tag="Events"
                onclick="openLightbox(this)">
                <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80" alt="Food Expo"
                    loading="lazy">
                <div class="gallery-item-overlay">
                    <div class="overlay-tag">Events</div>
                    <div class="overlay-title">Food Expo Stall 2023</div>
                </div>
                <div class="overlay-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                    </svg>
                </div>
            </div>

        </div>

        {{-- ── FEATURED STRIP ── --}}
        <div class="featured-strip">
            <p>Authentic • Homemade • Fresh</p>
            <div class="featured-strip-dot"></div>
            <p>South Indian Tradition</p>
            <div class="featured-strip-dot"></div>
            <p>Crafted with Love</p>
            <div class="featured-strip-dot"></div>
            <p>Pure Ingredients</p>
            <div class="featured-strip-dot"></div>
            <p>Amma's Spices</p>
        </div>

        {{-- ── CTA ── --}}
        <section class="gallery-cta">
            <p>Taste the Tradition</p>
            <h2>Ready to Explore Our Spices?</h2>
            <a href="{{ route('shop.page.index') }}" class="cta-btn">Shop All Products</a>
        </section>

    </div>

    {{-- ── LIGHTBOX ── --}}
    <div class="lightbox" id="lightbox" onclick="closeLightboxOnBackdrop(event)">
        <div class="lightbox-inner">
            <button class="lightbox-close" onclick="closeLightbox()">
                <svg viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <button class="lightbox-nav prev" onclick="navigateLightbox(-1)">
                <svg viewBox="0 0 24 24">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>
            <button class="lightbox-nav next" onclick="navigateLightbox(1)">
                <svg viewBox="0 0 24 24">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </button>
            <img id="lightboxImg" src="" alt="">
            <div class="lightbox-caption">
                <span class="overlay-tag" id="lightboxTag"></span>
                <div class="overlay-title" id="lightboxTitle"></div>
            </div>
        </div>
    </div>


    <script>
        // ── FILTER ──
        const allItems = document.querySelectorAll('.gallery-item');

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;

                allItems.forEach(item => {
                    const match = filter === 'all' || item.dataset.category === filter;

                    if (match) {
                        // Step 1: make it exist in layout (but still invisible)
                        item.style.display = 'block';
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.95)';
                        item.style.transition = 'none'; // no transition yet

                        // Step 2: force browser to paint the display:block state
                        item.offsetHeight; // <-- triggers reflow

                        // Step 3: NOW apply transition + animate in
                        item.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                        item.style.pointerEvents = 'auto';
                        item.dataset.visible = 'true';
                    } else {
                        item.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.95)';
                        item.style.pointerEvents = 'none';
                        item.dataset.visible = 'false';

                        // Hide from layout AFTER fade-out finishes
                        setTimeout(() => {
                            if (item.dataset.visible === 'false') {
                                item.style.display = 'none';
                            }
                        }, 280);
                    }
                });
            });
        });

        // ── LIGHTBOX ──
        let currentIndex = 0;

        // Attach click listeners properly via JS (not inline onclick)
        allItems.forEach((item, index) => {
            item.style.cursor = 'pointer';
            item.addEventListener('click', () => {
                currentIndex = index;
                loadLightbox(item);
                document.getElementById('lightbox').classList.add('open');
                document.body.style.overflow = 'hidden';
            });
        });

        function loadLightbox(item) {
            document.getElementById('lightboxImg').src = item.querySelector('img').src;
            document.getElementById('lightboxImg').alt = item.dataset.title || '';
            document.getElementById('lightboxTag').textContent = item.dataset.tag || '';
            document.getElementById('lightboxTitle').textContent = item.dataset.title || '';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('open');
            document.body.style.overflow = '';
        }

        // Close on backdrop click
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });

        // Prev / Next  — only cycle through VISIBLE items
        function navigateLightbox(dir) {
            const visible = [...allItems].filter(i => i.style.pointerEvents !== 'none');
            const pool = visible.length ? visible : [...allItems];
            const curPos = pool.findIndex(i => i === allItems[currentIndex]);
            const nextPos = (curPos + dir + pool.length) % pool.length;
            currentIndex = [...allItems].indexOf(pool[nextPos]);
            loadLightbox(pool[nextPos]);
        }

        // Hook nav buttons
        document.querySelector('.lightbox-nav.prev').addEventListener('click', () => navigateLightbox(-1));
        document.querySelector('.lightbox-nav.next').addEventListener('click', () => navigateLightbox(1));
        document.querySelector('.lightbox-close').addEventListener('click', closeLightbox);

        // Keyboard
        document.addEventListener('keydown', e => {
            if (!document.getElementById('lightbox').classList.contains('open')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') navigateLightbox(1);
            if (e.key === 'ArrowLeft') navigateLightbox(-1);
        });
    </script>
@endsection
