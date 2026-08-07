@extends('frontend.layouts.app', ['title' => ''])

@section('content')

    {{-- ═══════════════════════════════════════════════════════════
     AMMA'S SPICES — CLEAN HOME PAGE
     Fonts: Playfair Display (headings) + DM Sans (body)
     Tokens: --gold #e7c840 | --bg #0c0c0c | --card #161616
═══════════════════════════════════════════════════════════ --}}

    <style>
        /* .as-hero-wrap {
                            position: relative;
                            overflow: hidden;
                            background:
                                radial-gradient(circle at 18% 18%, rgba(231, 200, 64, 0.18), transparent 32%),
                                linear-gradient(135deg, #120f08 0%, #050505 100%);
                        } */

        /* .as-hero-wrap .owl-carousel,
                        .as-hero-wrap .owl-stage-outer,
                        .as-hero-wrap .owl-stage,
                        .as-hero-wrap .owl-item,
                        .as-hero-wrap .intro-slide,
                        .as-hero-wrap picture {
                            display: block;
                            width: 100%;
                            height: 100%;
                        }

                        .as-hero-wrap .inner-carousel {
                            height: clamp(260px, 40vw, 620px);
                        }*/

        .as-hero-wrap .inner-carousel {
            height: clamp(360px, 42vw, 620px);
            aspect-ratio: 5 / 2.2;
        }

        .as-hero-wrap .owl-carousel,
        .as-hero-wrap .owl-stage-outer,
        .as-hero-wrap .owl-stage,
        .as-hero-wrap .owl-item {
            height: 100%;
        }

        .as-hero-wrap .intro-slide {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #080808;
            height: 100%;
        }

        .as-hero-wrap .intro-slide picture {
            display: block;
            width: 100%;
            height: 100%;
        }

        .as-hero-wrap .baneer-img-r {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .as-hero-wrap .owl-carousel .owl-nav {
            position: absolute;
            top: 50%;
            left: 24px;
            right: 24px;
            z-index: 12;
            display: flex;
            justify-content: space-between;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .as-hero-wrap .owl-carousel .owl-nav button {
            position: static !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 44px !important;
            height: 44px !important;
            margin: 0 !important;
            border: 1px solid rgba(255, 255, 255, 0.42) !important;
            border-radius: 999px !important;
            background: rgba(8, 8, 8, 0.46) !important;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.24);
            color: #fff !important;
            cursor: pointer;
            backdrop-filter: blur(10px);
            pointer-events: auto;
            transform: none !important;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }

        .as-hero-wrap .owl-carousel .owl-nav button span {
            display: block;
            margin-top: -3px;
            font-size: 34px;
            line-height: 1;
        }

        .as-hero-wrap .owl-carousel .owl-nav button:hover,
        .as-hero-wrap .owl-carousel .owl-nav button:focus {
            border-color: #e7c840 !important;
            background: #e7c840 !important;
            box-shadow: 0 18px 38px rgba(231, 200, 64, 0.28);
            color: #1a1a1a !important;
            outline: none;
        }

        .as-hero-wrap .owl-carousel .owl-dots {
            position: absolute;
            left: 50%;
            bottom: 20px;
            z-index: 12;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 0;
            padding: 8px 10px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            background: rgba(8, 8, 8, 0.42);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(10px);
            transform: translateX(-50%);
        }

        .as-hero-wrap .owl-carousel .owl-dots .owl-dot {
            cursor: pointer;
        }

        .as-hero-wrap .owl-carousel .owl-dots .owl-dot span {
            width: 10px;
            height: 10px;
            margin: 0;
            border: 1px solid rgba(255, 255, 255, 0.76);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.45);
            transition: width 0.2s ease, background 0.2s ease, border-color 0.2s ease;
        }

        .as-hero-wrap .owl-carousel .owl-dots .owl-dot.active span,
        .as-hero-wrap .owl-carousel .owl-dots .owl-dot:hover span,
        .as-hero-wrap .owl-carousel .owl-dots .owl-dot:focus span {
            width: 26px;
            border-color: #e7c840;
            background: #e7c840;
        }

        @media (max-width: 767px) {
            .as-hero-wrap .inner-carousel {
                height: unset !important;
                /* remove the fixed height */
                aspect-ratio: 3 / 4;
                /* ← match this to your mobile banner ratio */
                width: 100%;
            }

            .as-hero-wrap .owl-carousel .owl-nav {
                left: 10px;
                right: 10px;
            }

            .as-hero-wrap .owl-carousel .owl-nav button {
                width: 36px !important;
                height: 36px !important;
            }

            .as-hero-wrap .owl-carousel .owl-nav button span {
                font-size: 28px;
            }

            .as-hero-wrap .owl-carousel .owl-dots {
                bottom: 12px;
                padding: 7px 9px;
            }
        }

        .hero-overlay-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 10;
            width: 80%;
            max-width: 800px;
            pointer-events: none;
            /* so owl carousel clicks still work */
        }

        .hero-overlay-content .hero-btn {
            pointer-events: auto;
            /* re-enable clicks on button only */
        }

        .hero-title {
            font-size: 52px;
            font-weight: 800;
            /* color: #f5c518; */
            /* golden yellow matching your screenshot */
            letter-spacing: 2px;
            margin-bottom: 12px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);


            font-size: 60px;
            margin: 0px;
            color: #e7c840;
            text-transform: uppercase;
            text-shadow: 5px 5px #000;
        }

        .hero-subtitle {
            font-size: 26px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 14px;
            text-shadow: 2px 2px #000;
        }

        .hero-desc {
            font-size: 15px;
            color: #cccccc;
            line-height: 1.8;
            margin-bottom: 28px;
        }

        .hero-btn {
            display: inline-block;
            background-color: #e7c840;
            color: #1a1a1a;
            font-size: 16px;
            font-weight: 600;
            padding: 14px 36px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .hero-btn:hover {
            background-color: #e7c840;
            transform: translateY(-2px);
            color: #1a1a1a;
            text-decoration: none;
        }

        /* ── HERO ─────────────────────────────────────────────── */

        .as-hero-wrap .inner-carousel {
            height: clamp(260px, 44vw, 620px);
            /* desktop: scales with viewport, capped at 620px */
        }

        /* All Owl internals must fill the fixed container exactly */
        .as-hero-wrap .owl-carousel,
        .as-hero-wrap .owl-stage-outer,
        .as-hero-wrap .owl-stage,
        .as-hero-wrap .owl-item,
        .as-hero-wrap .intro-slide,
        .as-hero-wrap .intro-slide picture {
            /* ← picture was missing height:100% */
            height: 100%;
            display: block;
            width: 100%;
        }

        .as-hero-wrap .intro-slide {
            background: #080808;
        }

        /* The actual <img> — object-fit:cover locks it to the box, no bleed */
        .as-hero-wrap .baneer-img-r {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        /* Nav arrows */
        .as-hero-wrap .owl-carousel .owl-nav {
            position: absolute;
            top: 50%;
            left: 24px;
            right: 24px;
            z-index: 12;
            display: flex;
            justify-content: space-between;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .as-hero-wrap .owl-carousel .owl-nav button {
            position: static !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 44px !important;
            height: 44px !important;
            margin: 0 !important;
            border: 1px solid rgba(255, 255, 255, 0.42) !important;
            border-radius: 999px !important;
            background: rgba(8, 8, 8, 0.46) !important;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.24);
            color: #fff !important;
            cursor: pointer;
            backdrop-filter: blur(10px);
            pointer-events: auto;
            transform: none !important;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }

        .as-hero-wrap .owl-carousel .owl-nav button span {
            display: block;
            margin-top: -3px;
            font-size: 34px;
            line-height: 1;
        }

        .as-hero-wrap .owl-carousel .owl-nav button:hover,
        .as-hero-wrap .owl-carousel .owl-nav button:focus {
            border-color: #e7c840 !important;
            background: #e7c840 !important;
            box-shadow: 0 18px 38px rgba(231, 200, 64, 0.28);
            color: #1a1a1a !important;
            outline: none;
        }

        /* Dots */
        .as-hero-wrap .owl-carousel .owl-dots {
            position: absolute;
            left: 50%;
            bottom: 20px;
            z-index: 12;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 0;
            padding: 8px 10px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            background: rgba(8, 8, 8, 0.42);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(10px);
            transform: translateX(-50%);
        }

        .as-hero-wrap .owl-carousel .owl-dots .owl-dot span {
            width: 10px;
            height: 10px;
            margin: 0;
            border: 1px solid rgba(255, 255, 255, 0.76);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.45);
            transition: width 0.2s ease, background 0.2s ease, border-color 0.2s ease;
        }

        .as-hero-wrap .owl-carousel .owl-dots .owl-dot.active span,
        .as-hero-wrap .owl-carousel .owl-dots .owl-dot:hover span,
        .as-hero-wrap .owl-carousel .owl-dots .owl-dot:focus span {
            width: 26px;
            border-color: #e7c840;
            background: #e7c840;
        }

        /* ── MOBILE ───────────────────────────────────────────── */
        @media (max-width: 767px) {
            .as-hero-wrap .inner-carousel {
                /* 56vw keeps a natural banner ratio on portrait phones;
                           clamp stops it going too small (220px) or too tall (420px) */
                height: clamp(522px, 56vw, 420px);
            }

            .as-hero-wrap .owl-carousel .owl-nav {
                left: 10px;
                right: 10px;
            }

            .as-hero-wrap .owl-carousel .owl-nav button {
                width: 36px !important;
                height: 36px !important;
            }

            .as-hero-wrap .owl-carousel .owl-nav button span {
                font-size: 28px;
            }

            .as-hero-wrap .owl-carousel .owl-dots {
                bottom: 12px;
                padding: 7px 9px;
            }
        }
    </style>
    @php
        $reviewCount = $googleReviewStats['count'] ?? 0;
        $reviewAverage = $googleReviewStats['average'] ?? 0;
        $reviewAverageFormatted = $reviewCount > 0 ? number_format($reviewAverage, 1) : '0.0';
    @endphp
    <main class="main">
        <div class="page-content">

            {{-- ══════════════════════════════════════════
             HERO BANNER
        ══════════════════════════════════════════ --}}
            <div class="intro-section as-hero-wrap" style="position: relative;">
                <div class="owl-carousel inner-carousel owl-simple rows cols-1" data-toggle="owl"
                    data-owl-options='{
                        "nav": true,
                        "navText": ["<span aria-label=\"Previous\">&#8249;</span>", "<span aria-label=\"Next\">&#8250;</span>"],
                        "autoplay": true,
                        "autoplayTimeout": 5000,
                        "autoplayHoverPause": true,
                        "dots": true,
                        "loop": true
                    }'>
                    @foreach ($banners as $banner)
                        <div class="intro-slide">
                            <picture>
                                @if ($banner->mobile_banner)
                                    <source media="(max-width: 767px)"
                                        srcset="{{ asset('storage/' . $banner->mobile_banner) }}">
                                @endif
                                <img src="{{ asset('storage/' . $banner->image) }}" class="baneer-img-r"
                                    alt="Amma's Spices">
                            </picture>
                        </div>
                    @endforeach
                </div>

                {{-- Centered Overlay Text --}}
                {{-- <div class="hero-overlay-content">
                    <h1 class="hero-title">AMMA'S SPICES</h1>
                    <h2 class="hero-subtitle">Authentic Home-made fresh Spice blend's</h2>
                    <p class="hero-desc">
                        Discover authentic, handcrafted south spice blends curated with love, tradition, and purity.<br>
                        Every spoonful is a journey of flavours intended to give you the taste of south india.
                    </p>
                    <a href="{{ route('shop.page.index') }}" class="hero-btn">Explore Our Products</a>
                </div> --}}
            </div>

            {{-- ══════════════════════════════════════════
             TRUST BAR
        ══════════════════════════════════════════ --}}
            <div class="as-trust">
                <div class="as-trust-inner">
                    <div class="as-trust-item">
                        <div class="as-trust-dot"></div> Lab-tested &amp; certified
                    </div>
                    <div class="as-trust-item">
                        <div class="as-trust-dot"></div> No preservatives or additives
                    </div>
                    <div class="as-trust-item">
                        <div class="as-trust-dot"></div> Home kitchen crafted
                    </div>
                    <div class="as-trust-item">
                        <div class="as-trust-dot"></div> Authentic recipes & flavours
                    </div>
                    <div class="as-trust-item">
                        <div class="as-trust-dot"></div> {{$reviewAverageFormatted}}★ Google Rating · {{$reviewCount}} reviews
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
             COLLECTION LIST
        ══════════════════════════════════════════ --}}
            <div class="as-section as-collection-wrap">
                <div class="container">
                    <div class="as-head">
                        <h2>Collection List</h2>
                        <div class="as-divider"><span class="as-divider-dot"></span></div>
                    </div>
                    <div class="row justify-content-center">
                        @foreach ($categories->take(3) as $category)
                            @php
                                $countProduct = 0;
                                $countProduct += $category
                                    ->products()
                                    ->whereHas('variations', fn($q) => $q->where('is_deleted', 0))
                                    ->where('is_deleted', 0)
                                    ->count();
                                foreach ($category->sub_categories as $subCat) {
                                    $countProduct += $subCat
                                        ->products()
                                        ->whereHas('variations', fn($q) => $q->where('is_deleted', 0))
                                        ->where('is_deleted', 0)
                                        ->count();
                                }
                            @endphp
                            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                <div class="items-box">
                                    <a href="{{ route('getCatList', $category->slug) }}">
                                        <div class="items">
                                            <img src="{{ asset($category->image) }}" alt="{{ $category->title }}">
                                        </div>
                                    </a>
                                    <h3>{{ $category->title }}</h3>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

              {{-- <div class="as-section as-collection-wrap">
                <div class="container">
                    <div class="as-head">
                        <h2>Collection List</h2>
                        <div class="as-divider"><span class="as-divider-dot"></span></div>
                    </div>
                    <div class="owl-carousel collection-list owl-simple" data-toggle="owl"
                        data-owl-options='{
                        "nav": true,
                        "loop": false,
                        "dots": false,
                        "margin": 16,
                        "responsive": {
                            "0":   { "items": 2 },
                            "480": { "items": 3 },
                            "768": { "items": 3 },
                            "992": { "items": 3 },
                            "1200":{ "items": 3 }
                        }
                    }'>
                        @foreach ($categories as $category)
                            @php
                                $countProduct = 0;
                                $countProduct += $category
                                    ->products()
                                    ->whereHas('variations', fn($q) => $q->where('is_deleted', 0))
                                    ->where('is_deleted', 0)
                                    ->count();
                                foreach ($category->sub_categories as $subCat) {
                                    $countProduct += $subCat
                                        ->products()
                                        ->whereHas('variations', fn($q) => $q->where('is_deleted', 0))
                                        ->where('is_deleted', 0)
                                        ->count();
                                }
                            @endphp
                            <div class="items-box">
                                <a href="{{ route('getCatList', $category->slug) }}">
                                    <div class="items">
                                        <img src="{{ asset($category->image) }}" alt="{{ $category->title }}">
                                    </div>
                                </a>
                                <h3>{{ $category->title }}</h3>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div> --}}


            {{-- ══════════════════════════════════════════
             FEATURED PRODUCTS
        ══════════════════════════════════════════ --}}
            <div class="featured-list-slider as-section--alt">
                <div class="container">
                    <div class="as-head">
                        <h2>Featured Products</h2>
                        <div class="as-divider"><span class="as-divider-dot"></span></div>
                    </div>
                    <div class="row">
                        <x-product-list :products="$products" />
                    </div>
                </div>
            </div>


            {{-- ══════════════════════════════════════════
             UPCOMMING PRODUCTS
        ══════════════════════════════════════════ --}}
            @if ($products->where('on_sale', 1)->count() > 0)
                <div class="featured-list-slider as-section--alt">
                    <div class="container">
                        <div class="as-head">
                            <h2>Upcoming Products</h2>
                            <div class="as-divider"><span class="as-divider-dot"></span></div>
                        </div>
                        <div class="row">
                            <x-product-list :products="$products->where('on_sale', 1)" />
                        </div>
                    </div>
                </div>
            @endif


            {{-- ══════════════════════════════════════════
             WHAT IS NOT IN OUR MASALA — PURITY PROMISE
             Lab report: drop the PDF at
             public/assets/docs/lab-report.pdf and the CTA
             links to it automatically; otherwise it falls
             back to the contact page.
        ══════════════════════════════════════════ --}}
            @php
                $labReportPath = 'assets/docs/lab-report.pdf';
                $labReportUrl = file_exists(public_path($labReportPath))
                    ? asset($labReportPath)
                    : route('contact-us.index');
                $labReportIsFile = file_exists(public_path($labReportPath));

                $purityItems = [
                    [
                        'title' => 'No Preservatives',
                        'text' => 'Nothing added to stretch shelf life. Ground fresh in small batches, every week.',
                        'icon' => '<path d="M9 3h6M10 3v5.2L4.8 17A2.4 2.4 0 0 0 6.9 20.6h10.2A2.4 2.4 0 0 0 19.2 17L14 8.2V3"/>',
                    ],
                    [
                        'title' => 'No Artificial Colours',
                        'text' => 'The colour you see comes from the spice itself — never from a dye or lake.',
                        'icon' => '<path d="M12 3.2s5.6 5.3 5.6 9.1a5.6 5.6 0 1 1-11.2 0C6.4 8.5 12 3.2 12 3.2Z"/>',
                    ],
                    [
                        'title' => 'No Fillers or Starch',
                        'text' => 'No rice flour, husk or bran bulking up the weight of your pack.',
                        'icon' => '<path d="M12 21V9M12 9c-2.6 0-4.2-1.7-4.2-4.2 2.6 0 4.2 1.7 4.2 4.2Zm0 0c2.6 0 4.2-1.7 4.2-4.2-2.6 0-4.2 1.7-4.2 4.2Zm0 6c-2.6 0-4.2-1.7-4.2-4.2 2.6 0 4.2 1.7 4.2 4.2Zm0 0c2.6 0 4.2-1.7 4.2-4.2-2.6 0-4.2 1.7-4.2 4.2Z"/>',
                    ],
                    [
                        'title' => 'No Flavour Enhancers',
                        'text' => 'No MSG, no synthetic flavouring. The aroma is the natural oil in the spice.',
                        'icon' => '<path d="M13.4 2.8 5.2 13.4h6L10.6 21.2l8.2-10.6h-6l.6-7.8Z"/>',
                    ],
                    [
                        'title' => 'No Anti-Caking Agents',
                        'text' => 'No silica or chemical free-flow agents. Store it dry and it stays loose.',
                        'icon' => '<circle cx="8" cy="8.4" r="1.7"/><circle cx="15.6" cy="10.4" r="1.7"/><circle cx="10.4" cy="15.6" r="1.7"/>',
                    ],
                    [
                        'title' => 'No Unknown Sourcing',
                        'text' => 'Sourced from farms we know by name and screened before it ever reaches the grinder.',
                        'icon' => '<path d="M12 2.9 4.6 6v5.6c0 4.3 3.1 8.3 7.4 9.5 4.3-1.2 7.4-5.2 7.4-9.5V6L12 2.9Z"/>',
                    ],
                ];
            @endphp
            <div class="as-purity">
                <div class="container">
                    <div class="as-purity-label">Our Purity Promise</div>
                    <div class="as-head">
                        <h2>What is NOT in Our Masala</h2>
                        <div class="as-divider"><span class="as-divider-dot"></span></div>
                    </div>
                    <p class="as-purity-lead">Most packet masala hides behind a long ingredient list. Ours is short on
                        purpose — here is everything we leave out, and why.</p>

                    <div class="as-purity-grid">
                        @foreach ($purityItems as $item)
                            <article class="as-purity-card">
                                <span class="as-purity-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                        {!! $item['icon'] !!}
                                        <line x1="4" y1="20" x2="20" y2="4"></line>
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="as-purity-title">{{ $item['title'] }}</h3>
                                    <p class="as-purity-text">{{ $item['text'] }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="as-purity-foot">
                        <p class="as-purity-foot-text">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M12 2.9 4.6 6v5.6c0 4.3 3.1 8.3 7.4 9.5 4.3-1.2 7.4-5.2 7.4-9.5V6L12 2.9Z" />
                                <path d="m9 12 2.1 2.1L15.4 9.8" />
                            </svg>
                            <span>Every batch is lab-tested for pesticides, heavy metals and aflatoxins before it
                                leaves our kitchen.</span>
                        </p>
                        <a href="{{ $labReportUrl }}" class="as-lab-btn"
                            @if ($labReportIsFile) target="_blank" rel="noopener" @endif>
                            Read Lab Report
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
             COMPARISON TABLE — AMMA'S vs THE REST
             Collapses into one card per row below 860px.
        ══════════════════════════════════════════ --}}
            @php
                // verdict: yes | no | part  — each cell also carries words,
                // so the meaning never depends on the colour of the mark alone.
                $compareRows = [
                    [
                        'feature' => 'Freshness',
                        'us' => ['yes', 'Ground in small batches, every week'],
                        'packet' => ['no', 'Months old before it reaches your kitchen'],
                        'mill' => ['part', 'Fresh, but ground in bulk lots'],
                    ],
                    [
                        'feature' => 'Preservatives',
                        'us' => ['yes', 'None. Ever.'],
                        'packet' => ['no', 'Commonly added for shelf life'],
                        'mill' => ['part', 'Varies — rarely declared'],
                    ],
                    [
                        'feature' => 'Artificial colours',
                        'us' => ['yes', 'Colour comes from the spice itself'],
                        'packet' => ['no', 'Often dyed for a brighter look'],
                        'mill' => ['part', 'Depends on the lot bought'],
                    ],
                    [
                        'feature' => 'Fillers &amp; starch',
                        'us' => ['yes', 'Zero bulking agents'],
                        'packet' => ['no', 'Husk, bran or flour used to cut cost'],
                        'mill' => ['part', 'Not always disclosed'],
                    ],
                    [
                        'feature' => 'Lab testing',
                        'us' => ['yes', 'Every batch, report on request'],
                        'packet' => ['no', 'Rarely shared with the buyer'],
                        'mill' => ['no', 'Not tested'],
                    ],
                    [
                        'feature' => 'Recipe',
                        'us' => ['yes', "Amma's family recipe, unchanged"],
                        'packet' => ['no', 'Mass factory blend'],
                        'mill' => ['part', 'Plain grind, no blending'],
                    ],
                    [
                        'feature' => 'Sourcing',
                        'us' => ['yes', 'Farms we know by name'],
                        'packet' => ['no', 'Origin not disclosed'],
                        'mill' => ['part', 'Open market lots'],
                    ],
                    [
                        'feature' => 'Aroma &amp; natural oils',
                        'us' => ['yes', 'Oils intact — you smell it on opening'],
                        'packet' => ['no', 'Faded after months on the shelf'],
                        'mill' => ['part', 'Good only if used quickly'],
                    ],
                ];

                $verdictIcons = [
                    'yes' => '<path d="M20 6 9 17l-5-5"/>',
                    'no' => '<path d="M18 6 6 18M6 6l12 12"/>',
                    'part' => '<path d="M5 12h14"/>',
                ];
                $verdictLabels = ['yes' => 'Yes', 'no' => 'No', 'part' => 'Partly'];
            @endphp
            <div class="as-compare">
                <div class="container">
                    <div class="as-compare-label">Honest Comparison</div>
                    <div class="as-head">
                        <h2>Amma's vs the Packet on the Shelf</h2>
                        <div class="as-divider"><span class="as-divider-dot"></span></div>
                    </div>
                    <p class="as-compare-lead">Same spice name on the label, very different jar. Here is the plain
                        difference between our masala, a supermarket packet, and the local mill.</p>

                    <div class="as-compare-scroll">
                        <table class="as-compare-table">
                            <caption class="as-sr-only">Comparison of Amma's Spices with supermarket packet masala and
                                local mill masala across freshness, additives, testing and sourcing</caption>
                            <thead>
                                <tr>
                                    <th scope="col">What matters</th>
                                    <th scope="col" class="is-us">
                                        Amma's Spices
                                        <span class="as-compare-badge">OUR PROMISE</span>
                                    </th>
                                    <th scope="col">Supermarket packet</th>
                                    <th scope="col">Local mill</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($compareRows as $row)
                                    <tr>
                                        <th scope="row">{!! $row['feature'] !!}</th>
                                        @foreach (['us' => "Amma's", 'packet' => 'Packet', 'mill' => 'Local mill'] as $key => $label)
                                            @php $verdict = $row[$key][0]; @endphp
                                            <td data-label="{{ $label }}" class="{{ $key === 'us' ? 'is-us' : '' }}">
                                                <span class="as-cmp-cell as-cmp-{{ $verdict }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2.4" stroke-linecap="round"
                                                        stroke-linejoin="round" role="img"
                                                        aria-label="{{ $verdictLabels[$verdict] }}">
                                                        {!! $verdictIcons[$verdict] !!}
                                                    </svg>
                                                    <span>{{ $row[$key][1] }}</span>
                                                </span>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="as-compare-foot">
                        <p class="as-compare-note">Comparison based on commonly sold packet masala and open-mill grinding
                            practice. Our claims are backed by the batch lab report.</p>
                        <a href="{{ route('shop.page.index') }}" class="as-compare-btn">
                            Shop Amma's Masala
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
             OUR STORY
        ══════════════════════════════════════════ --}}
            <div class="as-story">
                <div class="container">
                    <div class="as-story-grid">
                        <div class="as-story-img">
                            <img src="{{ asset('assets/img/aboutus_home.jpg') }}" alt="Our Story">
                        </div>
                        <div>
                            <div class="as-story-label">Our Story</div>
                            <h2 class="as-story-title">A Kitchen Full of Warmth &amp; Aroma</h2>
                            <p class="as-story-body">Growing up in South India, we would always watch Amma lovingly blend
                                her spices fresh before any meal preparation — filling our home with warmth and aroma.</p>
                            <p class="as-story-body">From our little home-run family kitchen, we bring you authentic South
                                Indian spice blends, crafted just like she made them — pure, traditional, and filled with
                                motherly love. Every blend is made to bring you the nostalgic taste of home-cooked meals.
                                Our mission is to make your daily cooking nourishing, easy, and full of flavour — just like
                                Amma intended.</p>
                            <a href="{{ route('frontend.about-us') }}" class="know-more-btn">Know More</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
             WHY CHOOSE US
             NOTE: Replace icon images with spice-relevant
             icons (not the soap/skin-care ones currently used)
        ══════════════════════════════════════════ --}}
            <div class="as-why">
                <div class="container">
                    <div class="as-head">
                        <h2>Why Choose Us</h2>
                        <div class="as-divider"><span class="as-divider-dot"></span></div>
                    </div>
                    <div class="as-why-grid">
                        <div class="icon-box text-center pt-0">
                            <figure class="m-0">
                                <img src="{{ asset('assets/img/preservative_free.svg') }}" alt="Authentic">
                            </figure>
                            <div class="icon-box-content">
                                <h3 class="icon-title">Preservative free</h3>
                            </div>
                        </div>
                        <div class="icon-box text-center pt-0">
                            <figure class="m-0">
                                <img src="{{ asset('assets/img/Reduced_chemical_exposure.png') }}" alt="No Chemicals">
                            </figure>
                            <div class="icon-box-content">
                                <h3 class="icon-title">No Artificial additives</h3>
                            </div>
                        </div>
                        <div class="icon-box text-center pt-0">
                            <figure class="m-0">
                                <img src="{{ asset('assets/img/Chemical-Free-Skin-Safe.png') }}" alt="Lab Tested">
                            </figure>
                            <div class="icon-box-content">
                                <h3 class="icon-title">All products are lab-tested and certified</h3>
                            </div>
                        </div>
                        <div class="icon-box text-center pt-0">
                            <figure class="m-0">
                                <img src="{{ asset('assets/img/Healthier_skin.png') }}" alt="Authentic">
                            </figure>
                            <div class="icon-box-content">
                                <h3 class="icon-title">Authentic recipes &amp; flavours</h3>
                            </div>
                        </div>
                        <div class="icon-box text-center pt-0">
                            <figure class="m-0">
                                <img src="{{ asset('assets/img/made_with_natural_ingredients.svg') }}" alt="Authentic">
                            </figure>
                            <div class="icon-box-content">
                                <h3 class="icon-title">Made with natural ingredients</h3>
                            </div>
                        </div>
                        <div class="icon-box text-center pt-0">
                            <figure class="m-0">
                                <img src="{{ asset('assets/img/high_natural_oil.svg') }}" alt="high natural oil content">
                            </figure>
                            <div class="icon-box-content">
                                <h3 class="icon-title">Natural oil content</h3>
                            </div>
                        </div>
                        <div class="icon-box text-center pt-0">
                            <figure class="m-0">
                                <img src="{{ asset('assets/img/proud_product_of_india.png') }}"
                                    alt="proud product of india" style="width: 90px;height: 60px;">
                            </figure>
                            <div class="icon-box-content">
                                <h3 class="icon-title">Proud product of India</h3>
                            </div>
                        </div>
                        <div class="icon-box text-center pt-0">
                            <figure class="m-0">
                                <img src="{{ asset('assets/img/lifestyle.png') }}" alt="Sustainable">
                            </figure>
                            <div class="icon-box-content">
                                <h3 class="icon-title">Sustainable, home kitchen crafted</h3>
                            </div>
                        </div>





                    </div>
                </div>
            </div>


            {{-- ══════════════════════════════════════════
             TESTIMONIALS
        ══════════════════════════════════════════ --}}
            <div class="as-testimonials">
                <div class="container">
                    <div class="as-head">
                        <h2>What Our Customers Say</h2>
                        <div class="as-divider"><span class="as-divider-dot"></span></div>
                    </div>

                    {{-- Google Rating Pill --}}

                    <div class="as-google-pill">
                        <div class="as-google-score">{{ $reviewAverageFormatted }}</div>
                        <div class="as-google-meta">
                            <div class="as-google-logo">
                                <span class="gb">G</span><span class="gr">o</span><span
                                    class="gy">o</span><span class="gb">g</span><span
                                    class="gg">l</span><span class="gr">e</span>
                            </div>
                            <div class="as-google-stars">★★★★★</div>
                            <div class="as-google-count">Based on {{ $reviewCount }}
                                {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var starsEl = document.querySelector('.as-google-stars');
                            if (!starsEl) return;
                            var roundedRating = {{ round($reviewAverage) }};
                            var stars = '';
                            for (var i = 1; i <= 5; i++) {
                                stars += i <= roundedRating ? '\u2605' : '\u2606';
                            }
                            starsEl.textContent = stars;
                        });
                    </script>

                    <div class="owl-carousel testimonials-carousel owl-simple" data-toggle="owl"
                        data-owl-options='{
                        "nav": false,
                        "loop": true,
                        "dots": true,
                        "autoplay": true,
                        "autoplayTimeout": 4500,
                        "autoplayHoverPause": true,
                        "margin": 16,
                        "responsive": {
                            "0":   { "items": 1 },
                            "576": { "items": 2 },
                            "992": { "items": 3 }
                        }
                    }'>

                        @if (isset($googleReviews) && $googleReviews->count())
                            @foreach ($googleReviews as $review)
                                <a target="_blank"
                                    href="https://www.google.com/search?q=Amma%27s+Spices&stick=H4sIAAAAAAAA_-NgU1I1qDC2NEgxNLc0NrdItjQzTTK1MqiwMDQ0Tkw1TjQ0szAzTTNMW8TK65ibm6herBBckJmcWgwAK_wgVzkAAAA&hl=en-GB&mat=CbssZhhO-bERElcBTVDHnl03odjGot-hCayt8d1YQ7HFGlWwIaC2dZWaJMkpMym3wctiffA35pTJp3hIJzHNu1fGuxsqu_wPnAU2OvKIxiaYGWKi5hqHvigMPCHh3nkigj4&authuser=8#mpd=~1275074283334675643/customers/reviews">
                                    <div class="g-review-card">
                                        <div class="g-review-header">
                                            <div class="g-avatar" style="background:#1a73e8;">
                                                {{ strtoupper(substr($review->reviewer_name, 0, 1)) }}</div>
                                            <div class="g-reviewer-info">
                                                <div class="g-reviewer-name">{{ $review->reviewer_name }}</div>
                                                <div class="g-reviewer-meta">
                                                    <img src="https://www.google.com/favicon.ico" class="g-favicon"
                                                        alt="Google">
                                                    <span>{{ $review->reviewer_role ?: 'Google Review' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="g-review-stars">
                                            @for ($i = 1; $i <= 5; $i++)
                                                {!! $i <= $review->rating ? '&#9733;' : '&#9734;' !!}
                                            @endfor
                                        </div>
                                        <div class="g-review-date">Customer Review</div>
                                        <p class="g-review-text">{{ $review->review_text }}</p>
                                        <div class="g-review-footer">
                                            <svg class="g-google-icon" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                                    fill="#4285F4" />
                                                <path
                                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                                    fill="#34A853" />
                                                <path
                                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                                                    fill="#FBBC05" />
                                                <path
                                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                                    fill="#EA4335" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        @else
                            {{-- Card 1 --}}
                            <div class="g-review-card">
                                <div class="g-review-header">
                                    <div class="g-avatar" style="background:#d93025;">P</div>
                                    <div class="g-reviewer-info">
                                        <div class="g-reviewer-name">Priya Sharma</div>
                                        <div class="g-reviewer-meta">
                                            <img src="https://www.google.com/favicon.ico" class="g-favicon"
                                                alt="Google">
                                            <span>Google Review</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="g-review-stars">★★★★★</div>
                                <div class="g-review-date">2 weeks ago</div>
                                <p class="g-review-text">
                                    {{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}
                                </p>
                                <div class="g-review-footer">
                                    <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                            fill="#4285F4" />
                                        <path
                                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                            fill="#34A853" />
                                        <path
                                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                                            fill="#FBBC05" />
                                        <path
                                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                            fill="#EA4335" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Card 2 --}}
                            <div class="g-review-card">
                                <div class="g-review-header">
                                    <div class="g-avatar" style="background:#1a73e8;">R</div>
                                    <div class="g-reviewer-info">
                                        <div class="g-reviewer-name">Ritu Agarwal</div>
                                        <div class="g-reviewer-meta">
                                            <img src="https://www.google.com/favicon.ico" class="g-favicon"
                                                alt="Google">
                                            <span>Google Review</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="g-review-stars">★★★★★</div>
                                <div class="g-review-date">1 month ago</div>
                                <p class="g-review-text">
                                    {{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}
                                </p>
                                <div class="g-review-footer">
                                    <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                            fill="#4285F4" />
                                        <path
                                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                            fill="#34A853" />
                                        <path
                                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                                            fill="#FBBC05" />
                                        <path
                                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                            fill="#EA4335" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Card 3 --}}
                            <div class="g-review-card">
                                <div class="g-review-header">
                                    <div class="g-avatar" style="background:#34a853;">A</div>
                                    <div class="g-reviewer-info">
                                        <div class="g-reviewer-name">Arjun Mehta</div>
                                        <div class="g-reviewer-meta">
                                            <img src="https://www.google.com/favicon.ico" class="g-favicon"
                                                alt="Google">
                                            <span>Google Review</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="g-review-stars">★★★★★</div>
                                <div class="g-review-date">3 weeks ago</div>
                                <p class="g-review-text">
                                    {{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}
                                </p>
                                <div class="g-review-footer">
                                    <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                            fill="#4285F4" />
                                        <path
                                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                            fill="#34A853" />
                                        <path
                                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                                            fill="#FBBC05" />
                                        <path
                                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                            fill="#EA4335" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Card 4 --}}
                            <div class="g-review-card">
                                <div class="g-review-header">
                                    <div class="g-avatar" style="background:#fbbc05; color:#0c0c0c;">S</div>
                                    <div class="g-reviewer-info">
                                        <div class="g-reviewer-name">Sneha Pillai</div>
                                        <div class="g-reviewer-meta">
                                            <img src="https://www.google.com/favicon.ico" class="g-favicon"
                                                alt="Google">
                                            <span>Google Review</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="g-review-stars">★★★★★</div>
                                <div class="g-review-date">1 month ago</div>
                                <p class="g-review-text">
                                    {{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}
                                </p>
                                <div class="g-review-footer">
                                    <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                            fill="#4285F4" />
                                        <path
                                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                            fill="#34A853" />
                                        <path
                                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                                            fill="#FBBC05" />
                                        <path
                                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                            fill="#EA4335" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Card 5 --}}
                            <div class="g-review-card">
                                <div class="g-review-header">
                                    <div class="g-avatar" style="background:#9c27b0;">V</div>
                                    <div class="g-reviewer-info">
                                        <div class="g-reviewer-name">Vikram Nair</div>
                                        <div class="g-reviewer-meta">
                                            <img src="https://www.google.com/favicon.ico" class="g-favicon"
                                                alt="Google">
                                            <span>Google Review</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="g-review-stars">★★★★★</div>
                                <div class="g-review-date">2 months ago</div>
                                <p class="g-review-text">
                                    {{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}
                                </p>
                                <div class="g-review-footer">
                                    <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                            fill="#4285F4" />
                                        <path
                                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                            fill="#34A853" />
                                        <path
                                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                                            fill="#FBBC05" />
                                        <path
                                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                            fill="#EA4335" />
                                    </svg>
                                </div>
                            </div>
                        @endif
                    </div>{{-- /.testimonials-carousel --}}
                </div>
            </div>


            {{-- ══════════════════════════════════════════
             BLOG SECTION
        ══════════════════════════════════════════ --}}
            <div class="blog-section">
                <div class="container">
                    <div class="as-head">
                        <h2>Blogs &amp; Articles</h2>
                        <div class="as-divider"><span class="as-divider-dot"></span></div>
                    </div>

                    @if (isset($blogs) && $blogs->count() > 0)
                        <div class="owl-carousel blog-carousel owl-simple" data-toggle="owl"
                            data-owl-options='{
                            "nav": false,
                            "loop": {{ $blogs->count() > 3 ? 'true' : 'false' }},
                            "dots": true,
                            "autoplay": true,
                            "autoplayTimeout": 4000,
                            "autoplayHoverPause": true,
                            "margin": 18,
                            "responsive": {
                                "0":   { "items": 1 },
                                "576": { "items": 2 },
                                "992": { "items": 3 }
                            }
                        }'>

                            @foreach ($blogs as $blog)
                                <a href="{{ route('customer.blog.show', $blog->slug) }}" class="blog-card">

                                    {{-- Thumbnail --}}
                                    <div class="blog-card__thumb">
                                        @if ($blog->thumbnail)
                                            @php
                                                $thumbnail = str_starts_with($blog->thumbnail, 'http')
                                                    ? $blog->thumbnail
                                                    : asset('storage/' . $blog->thumbnail);
                                            @endphp
                                            <img src="{{ $thumbnail }}" alt="{{ $blog->title }}" loading="lazy">
                                        @else
                                            <img src="{{ asset('assets/img/blog-placeholder.jpg') }}"
                                                alt="{{ $blog->title }}" loading="lazy">
                                        @endif
                                        @if ($blog->category)
                                            <span class="blog-card__cat">{{ $blog->category }}</span>
                                        @endif
                                    </div>

                                    {{-- Body --}}
                                    <div class="blog-card__body">
                                        <div class="blog-card__meta">
                                            <span>{{ $blog->published_at ? $blog->published_at->format('d M Y') : $blog->created_at->format('d M Y') }}</span>
                                            <span class="bdot"></span>
                                            <span>{{ $blog->read_time }} min read</span>
                                        </div>
                                        <h3 class="blog-card__title">{{ $blog->title }}</h3>
                                        <p class="blog-card__excerpt">{{ $blog->excerpt }}</p>
                                        <span class="blog-card__read-more">
                                            Read Article
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2.5">
                                                <path d="M5 12h14M12 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>

                                    {{-- Footer --}}
                                    <div class="blog-card__footer">
                                        <div class="blog-card__author">
                                            <div class="blog-card__avatar">
                                                {{ strtoupper(substr($blog->author ?? 'A', 0, 1)) }}</div>
                                            {{ $blog->author }}
                                        </div>
                                        @if ($blog->category)
                                            <span class="blog-card__tag">{{ $blog->category }}</span>
                                        @endif
                                    </div>

                                </a>
                            @endforeach

                        </div>{{-- /.blog-carousel --}}

                        <div class="blog-view-all-wrap">
                            <a href="{{ route('customer.blog.index') }}" class="blog-view-all-btn">
                                Explore All Articles
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2.5">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @else
                        <div
                            style="text-align:center; padding:40px 0; color:var(--text3); font-family:'DM Sans',sans-serif; font-size:13px;">
                            Blog posts coming soon.
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </main>

@endsection
