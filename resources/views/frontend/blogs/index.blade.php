@extends('frontend.layouts.app', [
    'title' => (optional($blogs->first())->meta_title ?: optional($blogs->first())->title ?: 'Blogs and Articles') . ' - Amma\'s Spices',
])

@section('content')

    @php
        $firstBlog = $blogs->first();
        $categoryCount = $blogs->pluck('category')->filter()->unique()->count();
        $authorCount = $blogs->pluck('author')->filter()->unique()->count();
        $avgReadTime = $blogs->count() ? (int) round((float) $blogs->avg('read_time')) : 0;
        $featuredCategories = $blogs->pluck('category')->filter()->unique()->take(4);
    @endphp

    <style>
        .bsp-page {
            --gold: #e7c840;
            --gold-soft: #f4df8b;
            --ink: #17110c;
            --ink-2: #251912;
            --paper: #fffaf3;
            --paper-2: #fff4e7;
            --muted: #786754;
            --muted-2: #9b8974;
            --line: rgba(87, 61, 32, 0.12);
            --card: #1e140f;
            --card-2: #261912;
            --card-line: rgba(255, 255, 255, 0.09);
            min-height: 100vh;
            padding: 126px 0 88px;
            background:
                radial-gradient(circle at top left, rgba(231, 200, 64, 0.18), transparent 24%),
                radial-gradient(circle at bottom right, rgba(173, 101, 44, 0.12), transparent 20%),
                linear-gradient(180deg, #fffaf3 0%, #fff 35%, #fff6ec 100%);
        }

        .bsp-shell {
            position: relative;
        }

        .bsp-shell::before {
            content: '';
            position: absolute;
            inset: 220px auto auto -30px;
            width: 120px;
            height: 120px;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(231, 200, 64, 0.16), rgba(231, 200, 64, 0));
            transform: rotate(14deg);
            pointer-events: none;
        }

        .bsp-shell::after {
            content: '';
            position: absolute;
            top: 460px;
            right: -28px;
            width: 96px;
            height: 96px;
            border: 1px solid rgba(87, 61, 32, 0.1);
            border-radius: 24px;
            transform: rotate(-10deg);
            pointer-events: none;
        }

        .bsp-hero {
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            min-height: 520px;
            background: var(--ink);
            box-shadow: 0 26px 80px rgba(56, 35, 16, 0.14);
        }

        .bsp-hero__media {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.44) saturate(0.92);
            transform: scale(1.03);
        }

        .bsp-hero__overlay {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(112deg, rgba(23, 17, 12, 0.94) 0%, rgba(23, 17, 12, 0.82) 45%, rgba(23, 17, 12, 0.5) 100%),
                radial-gradient(circle at 82% 24%, rgba(231, 200, 64, 0.2), transparent 24%);
        }

        .bsp-hero__grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 28px;
            align-items: end;
            min-height: 520px;
            padding: 54px;
        }

        .bsp-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(231, 200, 64, 0.14);
            border: 1px solid rgba(231, 200, 64, 0.24);
            color: var(--gold-soft);
            font: 700 11px/1 'DM Sans', sans-serif;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .bsp-pill--light {
            color: #8e651b;
            background: rgba(231, 200, 64, 0.12);
            border-color: rgba(231, 200, 64, 0.2);
        }

        .bsp-hero__title {
            margin: 22px 0 18px;
            color: #fff7ef;
            font: 700 clamp(40px, 6vw, 72px)/0.95 'Playfair Display', Georgia, serif;
            letter-spacing: -0.04em;
            max-width: 11ch;
        }

        .bsp-hero__title span {
            display: block;
            margin-top: 14px;
            color: rgba(255, 244, 229, 0.82);
            font: 400 clamp(17px, 2vw, 21px)/1.55 'DM Sans', sans-serif;
            letter-spacing: 0;
            max-width: 32rem;
        }

        .bsp-hero__desc {
            max-width: 35rem;
            margin: 0 0 28px;
            color: rgba(255, 239, 219, 0.72);
            font: 400 15px/1.85 'DM Sans', sans-serif;
        }

        .bsp-hero__actions {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .bsp-hero__cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 22px;
            border-radius: 18px;
            background: linear-gradient(135deg, #f3da77 0%, #d7a63b 100%);
            color: #1b120c;
            font: 700 12px/1 'DM Sans', sans-serif;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-decoration: none;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
            box-shadow: 0 16px 28px rgba(231, 200, 64, 0.18);
        }

        .bsp-hero__cta:hover {
            color: #1b120c;
            transform: translateY(-2px);
            box-shadow: 0 20px 34px rgba(231, 200, 64, 0.24);
        }

        .bsp-hero__meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            color: rgba(255, 239, 219, 0.72);
            font: 500 12px/1.2 'DM Sans', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .bsp-hero__dot {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: rgba(255, 239, 219, 0.32);
        }

        .bsp-hero__panel {
            align-self: stretch;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 18px;
            padding: 24px;
            border-radius: 28px;
            background: rgba(255, 248, 239, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(14px);
        }

        .bsp-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .bsp-stat {
            padding: 15px 14px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .bsp-stat strong {
            display: block;
            margin-bottom: 6px;
            color: #fff7ef;
            font: 700 29px/1 'Playfair Display', Georgia, serif;
        }

        .bsp-stat span {
            display: block;
            color: rgba(255, 239, 219, 0.64);
            font: 700 10px/1.4 'DM Sans', sans-serif;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .bsp-hero__topic-wrap {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .bsp-hero__topic-wrap h2 {
            margin: 0;
            color: rgba(255, 239, 219, 0.76) !important;
            font: 700 11px/1.2 'DM Sans', sans-serif;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .bsp-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .bsp-chip {
            display: inline-flex;
            align-items: center;
            padding: 9px 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #f7e8d0 !important;
            font: 500 12px/1.2 'DM Sans', sans-serif;
        }

        .bsp-hero__spotlight {
            padding: 18px;
            border-radius: 22px;
            background: rgba(0, 0, 0, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .bsp-hero__spotlight h3 {
            margin: 0 0 8px;
            color: #fff8ef !important;
            font: 700 24px/1.2 'Playfair Display', Georgia, serif;
        }

        .bsp-hero__spotlight p {
            margin: 0;
            color: rgba(255, 239, 219, 0.72) !important;
            font: 400 14px/1.75 'DM Sans', sans-serif;
        }

        .bsp-crumb {
            margin: 20px 0 30px;
            padding: 0;
            list-style: none;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            color: var(--muted) !important;
            font: 500 12px/1.2 'DM Sans', sans-serif;
        }

        .bsp-crumb a {
            color: #5c452f !important;
            text-decoration: none;
        }

        .bsp-crumb a:hover {
            color: var(--ink);
        }

        .bsp-crumb span {
            color: var(--muted-2);
        }

        .bsp-intro {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(280px, 0.95fr);
            gap: 22px;
            margin-bottom: 34px;
        }

        .bsp-intro__copy,
        .bsp-intro__note {
            border-radius: 28px;
            background: rgba(255, 250, 243, 0.88);
            border: 1px solid var(--line);
            box-shadow: 0 18px 40px rgba(84, 52, 18, 0.05);
        }

        .bsp-intro__copy {
            padding: 30px 32px;
        }

        .bsp-intro__note {
            padding: 26px 28px;
        }

        .bsp-intro__copy h2 {
            margin: 16px 0 14px;
            color: var(--ink);
            font: 700 clamp(28px, 4vw, 48px)/1.02 'Playfair Display', Georgia, serif;
            letter-spacing: -0.03em;
        }

        .bsp-divider {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bsp-divider::before,
        .bsp-divider::after {
            content: '';
            height: 1px;
            background: rgba(87, 61, 32, 0.16);
        }

        .bsp-divider::before {
            width: 68px;
        }

        .bsp-divider::after {
            width: 26px;
        }

        .bsp-divider span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--gold);
        }

        .bsp-intro__note p {
            margin: 0;
            color: #695744 !important;
            font: 400 15px/1.88 'DM Sans', sans-serif;
        }

        .bsp-related {
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            padding: 32px;
            background:
                linear-gradient(180deg, var(--card-2) 0%, var(--card) 100%);
            box-shadow: 0 28px 72px rgba(47, 27, 8, 0.16);
        }

        .bsp-related::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -60px;
            width: 220px;
            height: 220px;
            border-radius: 32px;
            background: linear-gradient(135deg, rgba(231, 200, 64, 0.14), rgba(231, 200, 64, 0));
            transform: rotate(12deg);
            pointer-events: none;
        }

        .bsp-related__hd {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
        }

        .bsp-related__hd h2 {
            margin: 14px 0 0;
            color: #fff8ef !important;
            font: 700 clamp(28px, 4vw, 42px)/1.02 'Playfair Display', Georgia, serif;
            letter-spacing: -0.03em;
        }

        .bsp-related__hd p {
            max-width: 360px;
            margin: 0;
            color: rgba(255, 239, 219, 0.7) !important;
            font: 400 14px/1.8 'DM Sans', sans-serif;
        }

        .bsp-related__grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .blog-card {
            background: rgba(255, 248, 239, 0.03);
            border: 1px solid var(--card-line);
            border-radius: 24px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
            transition: transform 0.24s ease, border-color 0.24s ease, background 0.24s ease;
            cursor: pointer;
        }

        .blog-card:hover {
            transform: translateY(-4px);
            border-color: rgba(231, 200, 64, 0.32);
            background: rgba(255, 248, 239, 0.05);
        }

        .blog-card__thumb {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: #120d09;
        }

        .blog-card__thumb::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 62px;
            background: linear-gradient(to top, rgba(30, 20, 15, 0.96), transparent);
            pointer-events: none;
        }

        .blog-card__thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            filter: brightness(0.85);
            transition: transform 0.35s ease, filter 0.35s ease;
        }

        .blog-card:hover .blog-card__thumb img {
            transform: scale(1.04);
            filter: brightness(1);
        }

        .blog-card__cat {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            padding: 7px 11px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f3da77 0%, #d7a63b 100%);
            color: #1b120c;
            font: 700 10px/1 'DM Sans', sans-serif;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .blog-card__body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 20px 20px 16px;
        }

        .blog-card__meta {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 10px;
            color: rgba(255, 239, 219, 0.5);
            font: 500 11px/1.4 'DM Sans', sans-serif;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            flex-wrap: wrap;
        }

        .blog-card__meta .bdot {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: rgba(255, 239, 219, 0.28);
        }

        .blog-card__title {
            margin: 0 0 10px;
            color: #fff8ef;
            font: 700 20px/1.32 'Playfair Display', Georgia, serif;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: color 0.22s ease;
        }

        .blog-card:hover .blog-card__title {
            color: var(--gold-soft);
        }

        .blog-card__excerpt {
            margin: 0 0 16px;
            color: rgba(255, 239, 219, 0.7);
            font: 400 13px/1.8 'DM Sans', sans-serif;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .blog-card__read-more {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: auto;
            color: var(--gold-soft);
            font: 700 11px/1 'DM Sans', sans-serif;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .blog-card__read-more svg {
            transition: transform 0.22s ease;
        }

        .blog-card:hover .blog-card__read-more svg {
            transform: translateX(3px);
        }

        .blog-card__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 20px 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            flex-wrap: wrap;
        }

        .blog-card__author {
            display: flex;
            align-items: center;
            gap: 9px;
            color: rgba(0, 0, 0, 1);
            font: 500 12px/1.2 'DM Sans', sans-serif;
        }

        .blog-card__avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f3da77 0%, #d7a63b 100%);
            color: #1b120c;
            font: 700 11px/1 'DM Sans', sans-serif;
            flex-shrink: 0;
        }

        .blog-card__tag {
            display: inline-flex;
            align-items: center;
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 239, 219, 0.62);
            font: 600 10px/1 'DM Sans', sans-serif;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .bsp-empty {
            padding: 48px 30px;
            border-radius: 32px;
            background: rgba(255, 250, 243, 0.9);
            border: 1px solid var(--line);
            text-align: center;
            box-shadow: 0 18px 40px rgba(84, 52, 18, 0.05);
        }

        .bsp-empty h2 {
            margin: 16px 0 10px;
            color: var(--ink);
            font: 700 34px/1.1 'Playfair Display', Georgia, serif;
        }

        .bsp-empty p {
            max-width: 34rem;
            margin: 0 auto;
            color: #695744;
            font: 400 15px/1.88 'DM Sans', sans-serif;
        }

        @media (max-width: 1199px) {
            .bsp-hero__grid {
                padding: 42px;
            }

            .bsp-related__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991px) {
            .bsp-page {
                padding-top: 112px;
            }

            .bsp-shell::before,
            .bsp-shell::after {
                display: none;
            }

            .bsp-hero__grid,
            .bsp-intro {
                grid-template-columns: 1fr;
            }

            .bsp-hero__grid {
                min-height: unset;
            }

            .bsp-related__hd {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 767px) {
            .bsp-page {
                padding: 104px 0 68px;
            }

            .bsp-hero,
            .bsp-related,
            .bsp-intro__copy,
            .bsp-intro__note,
            .bsp-empty {
                border-radius: 24px;
            }

            .bsp-hero__grid,
            .bsp-related {
                padding: 24px;
            }

            .bsp-hero__title {
                font-size: 38px;
            }

            .bsp-stats {
                grid-template-columns: 1fr;
            }

            .bsp-related__grid {
                grid-template-columns: 1fr;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .blog-card,
            .blog-card__thumb img,
            .blog-card__read-more svg,
            .bsp-hero__cta {
                transition: none !important;
                transform: none !important;
            }
        }
    </style>

    <div class="bsp-page">
        <div class="container bsp-shell">
            <section class="bsp-hero">
                <img src="{{ asset('assets/images/blog-banner.webp') }}" alt="Amma's Spices blog banner"
                    class="bsp-hero__media" loading="eager">
                <div class="bsp-hero__overlay"></div>

                <div class="bsp-hero__grid">
                    <div>
                        <span class="bsp-pill">Amma's Journal</span>

                        <h1 class="bsp-hero__title">
                            Blogs and Articles
                            <span>Recipes, spice wisdom, festive ideas, and warm kitchen stories from Amma's table.</span>
                        </h1>

                        <p class="bsp-hero__desc">
                            Explore thoughtful reads on flavor, technique, gifting, and everyday cooking in a cleaner,
                            warmer editorial layout built for Amma's Spices.
                        </p>

                        <div class="bsp-hero__actions">
                            <a href="#bspStories" class="bsp-hero__cta">
                                Explore Articles
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>

                            <div class="bsp-hero__meta">
                                <span>{{ $blogs->count() }} published</span>
                                <span class="bsp-hero__dot"></span>
                                <span>{{ $authorCount }} authors</span>
                                <span class="bsp-hero__dot"></span>
                                <span>{{ $avgReadTime }} min avg read</span>
                            </div>
                        </div>
                    </div>

                    <aside class="bsp-hero__panel">
                        <div class="bsp-stats">
                            <div class="bsp-stat">
                                <strong>{{ $blogs->count() }}</strong>
                                <span>Stories</span>
                            </div>
                            <div class="bsp-stat">
                                <strong>{{ $categoryCount }}</strong>
                                <span>Topics</span>
                            </div>
                            <div class="bsp-stat">
                                <strong>{{ $avgReadTime }}</strong>
                                <span>Avg Read</span>
                            </div>
                        </div>

                        @if ($featuredCategories->isNotEmpty())
                            <div class="bsp-hero__topic-wrap">
                                <h2>Featured topics</h2>
                                <div class="bsp-chips">
                                    @foreach ($featuredCategories as $category)
                                        <span class="bsp-chip">{{ $category }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="bsp-hero__spotlight">
                            <span class="bsp-pill">Start Here</span>
                            <h3>{{ $firstBlog?->title ?? 'Fresh stories coming soon.' }}</h3>
                            <p>
                                {{ $firstBlog?->excerpt
                                    ? \Illuminate\Support\Str::limit(strip_tags($firstBlog->excerpt), 130)
                                    : 'New kitchen notes, gift ideas, and flavor stories will appear here as soon as they are published.' }}
                            </p>
                        </div>
                    </aside>
                </div>
            </section>

            <ul class="bsp-crumb">
                <li><a href="{{ route('index') }}">Home</a></li>
                <li><span>&rsaquo;</span></li>
                <li>Blogs</li>
                {{-- <li><span>&rsaquo;</span></li> --}}
                {{-- <li>{{ $authorCount }} voices in archive</li> --}}
            </ul>

            <section class="bsp-intro">
                <div class="bsp-intro__copy">
                    <span class="bsp-pill bsp-pill--light">Editorial Collection</span>
                    <h2>Every post carries recipe craft, ingredient knowledge, and little pieces of kitchen memory.</h2>
                    <div class="bsp-divider">
                        <span></span>
                    </div>
                </div>

                <div class="bsp-intro__note">
                    <p>
                        Browse the full collection below for guides, festive pairings, pantry ideas, and stories that
                        make every blend feel more personal.
                    </p>
                </div>
            </section>

            @if ($blogs->count() > 0)
                <section class="bsp-related" id="bspStories">
                    <div class="bsp-related__hd">
                        <div>
                            <span class="bsp-pill">Latest Stories</span>
                            <h2>Browse the full blog archive.</h2>
                        </div>

                        <p>Bold block-based section, warmer contrast, cleaner card rhythm, fully internal CSS.</p>
                    </div>

                    <div class="bsp-related__grid">
                        @foreach ($blogs as $blog)
                            <a href="{{ route('customer.blog.show', $blog->slug) }}" class="blog-card">

                                <div class="blog-card__thumb">
                                    @if ($blog->thumbnail)
                                        <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ $blog->title }}"
                                            loading="lazy">
                                    @else
                                        <img src="{{ asset('assets/img/blog-placeholder.jpg') }}"
                                            alt="{{ $blog->title }}" loading="lazy">
                                    @endif
                                    @if ($blog->category)
                                        <span class="blog-card__cat">{{ $blog->category }}</span>
                                    @endif
                                </div>

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

                                <div class="blog-card__footer">
                                    <div class="blog-card__author">
                                        <div class="blog-card__avatar">
                                            {{ strtoupper(substr($blog->author ?? 'A', 0, 1)) }}</div>
                                        {{ $blog->author ?? "Amma's Kitchen" }}
                                    </div>
                                    @if ($blog->category)
                                        <span class="blog-card__tag">{{ $blog->category }}</span>
                                    @endif
                                </div>

                            </a>
                        @endforeach
                    </div>
                </section>
            @else
                <section class="bsp-empty">
                    <span class="bsp-pill bsp-pill--light">Archive In Progress</span>
                    <h2>Fresh stories are on the way.</h2>
                    <p>Recipes, spice notes, and kitchen guides will appear here soon. Shell ready. Articles pending.</p>
                </section>
            @endif
        </div>
    </div>

@endsection
