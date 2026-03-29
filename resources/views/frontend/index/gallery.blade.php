@extends('frontend.layouts.app')

@section('content')
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --gold: #e7c840;
            --black: #000000;
            --off-black: #0e0e0e;
            --white: #ffffff;
            --grey: #888888;
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

        .gallery-empty-wrap {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 40px 80px;
        }

        .gallery-empty {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 48px 24px;
            text-align: center;
            background: rgba(255, 255, 255, 0.02);
        }

        .gallery-empty h3 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            margin-bottom: 12px;
        }

        .gallery-empty p {
            color: var(--grey);
            max-width: 540px;
            margin: 0 auto;
            line-height: 1.7;
        }

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

            .gallery-empty-wrap {
                padding: 24px 16px 60px;
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

    <div class="gallery-page">
        <section class="gallery-hero">
            <div class="gallery-hero-tag">Our Gallery</div>
            <h1>Moments &amp; <span>Flavours</span></h1>
            <p>A glimpse into our world - from vibrant spice events and product showcases to the heart of every blend we craft.</p>
        </section>

        <div class="gallery-filters">
            <button class="filter-btn active" data-filter="all" type="button">All</button>
            @foreach ($galleryCategories as $galleryCategory)
                <button class="filter-btn" data-filter="{{ $galleryCategory['slug'] }}" type="button">
                    {{ $galleryCategory['name'] }}
                </button>
            @endforeach
        </div>

        <div class="gallery-divider">
            <span>Explore</span>
        </div>

        @if ($galleryImages->isNotEmpty())
            <div class="gallery-grid" id="galleryGrid">
                @foreach ($galleryImages as $galleryImage)
                    <div
                        class="gallery-item"
                        data-category="{{ $galleryImage->category_slug }}"
                        data-title="{{ $galleryImage->title }}"
                        data-tag="{{ $galleryImage->category_name }}">
                        <img src="{{ $galleryImage->image_url }}" alt="{{ $galleryImage->title }}" loading="lazy">
                        <div class="gallery-item-overlay">
                            <div class="overlay-tag">{{ $galleryImage->category_name }}</div>
                            <div class="overlay-title">{{ $galleryImage->title }}</div>
                        </div>
                        <div class="overlay-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" />
                            </svg>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="gallery-empty-wrap">
                <div class="gallery-empty">
                    <h3>Gallery updates are coming soon.</h3>
                    <p>Please check back again for new moments from our kitchen, products, and events.</p>
                </div>
            </div>
        @endif

        <div class="featured-strip">
            <p>Authentic &bull; Homemade &bull; Fresh</p>
            <div class="featured-strip-dot"></div>
            <p>South Indian Tradition</p>
            <div class="featured-strip-dot"></div>
            <p>Crafted with Love</p>
            <div class="featured-strip-dot"></div>
            <p>Pure Ingredients</p>
            <div class="featured-strip-dot"></div>
            <p>Amma's Spices</p>
        </div>

        <section class="gallery-cta">
            <p>Taste the Tradition</p>
            <h2>Ready to Explore Our Spices?</h2>
            <a href="{{ route('shop.page.index') }}" class="cta-btn">Shop All Products</a>
        </section>
    </div>

    <div class="lightbox" id="lightbox">
        <div class="lightbox-inner">
            <button class="lightbox-close" id="lightboxClose" type="button">
                <svg viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <button class="lightbox-nav prev" id="lightboxPrev" type="button">
                <svg viewBox="0 0 24 24">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>
            <button class="lightbox-nav next" id="lightboxNext" type="button">
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
        const allItems = Array.from(document.querySelectorAll('.gallery-item'));
        const filterButtons = Array.from(document.querySelectorAll('.filter-btn'));
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightboxImg');
        const lightboxTag = document.getElementById('lightboxTag');
        const lightboxTitle = document.getElementById('lightboxTitle');
        const lightboxClose = document.getElementById('lightboxClose');
        const lightboxPrev = document.getElementById('lightboxPrev');
        const lightboxNext = document.getElementById('lightboxNext');

        let currentIndex = 0;

        function loadLightbox(item) {
            const itemImage = item.querySelector('img');

            lightboxImg.src = itemImage.src;
            lightboxImg.alt = item.dataset.title || '';
            lightboxTag.textContent = item.dataset.tag || '';
            lightboxTitle.textContent = item.dataset.title || '';
        }

        function closeLightbox() {
            lightbox.classList.remove('open');
            document.body.style.overflow = '';
        }

        function openLightbox(item) {
            loadLightbox(item);
            lightbox.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function getVisibleItems() {
            return allItems.filter((item) => item.style.display !== 'none');
        }

        function navigateLightbox(direction) {
            const visibleItems = getVisibleItems();

            if (!visibleItems.length) {
                return;
            }

            const currentItem = allItems[currentIndex] || visibleItems[0];
            const currentVisibleIndex = visibleItems.indexOf(currentItem);
            const startIndex = currentVisibleIndex === -1 ? 0 : currentVisibleIndex;
            const nextVisibleIndex = (startIndex + direction + visibleItems.length) % visibleItems.length;
            const nextItem = visibleItems[nextVisibleIndex];

            currentIndex = allItems.indexOf(nextItem);
            loadLightbox(nextItem);
        }

        allItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                currentIndex = index;
                openLightbox(item);
            });
        });

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                filterButtons.forEach((item) => item.classList.remove('active'));
                button.classList.add('active');

                const filter = button.dataset.filter;

                allItems.forEach((item) => {
                    const matches = filter === 'all' || item.dataset.category === filter;

                    if (matches) {
                        item.style.display = 'block';
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.95)';
                        item.style.transition = 'none';
                        item.offsetHeight;
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

                        setTimeout(() => {
                            if (item.dataset.visible === 'false') {
                                item.style.display = 'none';
                            }
                        }, 280);
                    }
                });
            });
        });

        lightboxClose.addEventListener('click', closeLightbox);
        lightboxPrev.addEventListener('click', () => navigateLightbox(-1));
        lightboxNext.addEventListener('click', () => navigateLightbox(1));

        lightbox.addEventListener('click', (event) => {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (!lightbox.classList.contains('open')) {
                return;
            }

            if (event.key === 'Escape') {
                closeLightbox();
            }

            if (event.key === 'ArrowRight') {
                navigateLightbox(1);
            }

            if (event.key === 'ArrowLeft') {
                navigateLightbox(-1);
            }
        });
    </script>
@endsection
