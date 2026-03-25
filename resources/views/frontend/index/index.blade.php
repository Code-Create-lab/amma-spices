@extends('frontend.layouts.app', ['title' => ''])

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     AMMA'S SPICES — CLEAN HOME PAGE
     Fonts: Playfair Display (headings) + DM Sans (body)
     Tokens: --gold #e7c840 | --bg #0c0c0c | --card #161616
═══════════════════════════════════════════════════════════ --}}

<style>
</style>

<main class="main">
    <div class="page-content">

        {{-- ══════════════════════════════════════════
             HERO BANNER
        ══════════════════════════════════════════ --}}
        <div class="intro-section as-hero-wrap">
            <div class="owl-carousel inner-carousel owl-simple rows cols-1" data-toggle="owl"
                data-owl-options='{"nav": false, "autoplay":true, "autoplayTimeout":3000, "dots": false, "loop": true}'>
                @foreach ($banners as $banner)
                    <div class="intro-slide">
                        <img src="{{ asset('storage/' . $banner->image) }}" class="baneer-img-r" alt="Amma's Spices">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             TRUST BAR
        ══════════════════════════════════════════ --}}
        <div class="as-trust">
            <div class="as-trust-inner">
                <div class="as-trust-item"><div class="as-trust-dot"></div> Lab-tested &amp; certified</div>
                <div class="as-trust-item"><div class="as-trust-dot"></div> No preservatives or additives</div>
                <div class="as-trust-item"><div class="as-trust-dot"></div> Home kitchen crafted</div>
                <div class="as-trust-item"><div class="as-trust-dot"></div> Authentic South Indian recipes</div>
                <div class="as-trust-item"><div class="as-trust-dot"></div> 4.9★ Google Rating · 120+ reviews</div>
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
                <div class="owl-carousel collection-list owl-simple" data-toggle="owl"
                    data-owl-options='{
                        "nav": true,
                        "loop": false,
                        "dots": false,
                        "margin": 16,
                        "responsive": {
                            "0":   { "items": 2 },
                            "480": { "items": 3 },
                            "768": { "items": 4 },
                            "992": { "items": 5 },
                            "1200":{ "items": 6 }
                        }
                    }'>
                    @foreach ($categories as $category)
                        @php
                            $countProduct = 0;
                            $countProduct += $category->products()
                                ->whereHas('variations', fn($q) => $q->where('is_deleted', 0))
                                ->where('is_deleted', 0)->count();
                            foreach ($category->sub_categories as $subCat) {
                                $countProduct += $subCat->products()
                                    ->whereHas('variations', fn($q) => $q->where('is_deleted', 0))
                                    ->where('is_deleted', 0)->count();
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
        </div>

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
                    <div class="as-google-score">4.9</div>
                    <div class="as-google-meta">
                        <div class="as-google-logo">
                            <span class="gb">G</span><span class="gr">o</span><span class="gy">o</span><span class="gb">g</span><span class="gg">l</span><span class="gr">e</span>
                        </div>
                        <div class="as-google-stars">★★★★★</div>
                        <div class="as-google-count">Based on 120+ reviews</div>
                    </div>
                </div>

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

                    {{-- Card 1 --}}
                    <div class="g-review-card">
                        <div class="g-review-header">
                            <div class="g-avatar" style="background:#d93025;">P</div>
                            <div class="g-reviewer-info">
                                <div class="g-reviewer-name">Priya Sharma</div>
                                <div class="g-reviewer-meta">
                                    <img src="https://www.google.com/favicon.ico" class="g-favicon" alt="Google">
                                    <span>Google Review</span>
                                </div>
                            </div>
                        </div>
                        <div class="g-review-stars">★★★★★</div>
                        <div class="g-review-date">2 weeks ago</div>
                        <p class="g-review-text">{{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}</p>
                        <div class="g-review-footer">
                            <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        </div>
                    </div>

                    {{-- Card 2 --}}
                    <div class="g-review-card">
                        <div class="g-review-header">
                            <div class="g-avatar" style="background:#1a73e8;">R</div>
                            <div class="g-reviewer-info">
                                <div class="g-reviewer-name">Ritu Agarwal</div>
                                <div class="g-reviewer-meta">
                                    <img src="https://www.google.com/favicon.ico" class="g-favicon" alt="Google">
                                    <span>Google Review</span>
                                </div>
                            </div>
                        </div>
                        <div class="g-review-stars">★★★★★</div>
                        <div class="g-review-date">1 month ago</div>
                        <p class="g-review-text">{{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}</p>
                        <div class="g-review-footer">
                            <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        </div>
                    </div>

                    {{-- Card 3 --}}
                    <div class="g-review-card">
                        <div class="g-review-header">
                            <div class="g-avatar" style="background:#34a853;">A</div>
                            <div class="g-reviewer-info">
                                <div class="g-reviewer-name">Arjun Mehta</div>
                                <div class="g-reviewer-meta">
                                    <img src="https://www.google.com/favicon.ico" class="g-favicon" alt="Google">
                                    <span>Google Review</span>
                                </div>
                            </div>
                        </div>
                        <div class="g-review-stars">★★★★★</div>
                        <div class="g-review-date">3 weeks ago</div>
                        <p class="g-review-text">{{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}</p>
                        <div class="g-review-footer">
                            <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        </div>
                    </div>

                    {{-- Card 4 --}}
                    <div class="g-review-card">
                        <div class="g-review-header">
                            <div class="g-avatar" style="background:#fbbc05; color:#0c0c0c;">S</div>
                            <div class="g-reviewer-info">
                                <div class="g-reviewer-name">Sneha Pillai</div>
                                <div class="g-reviewer-meta">
                                    <img src="https://www.google.com/favicon.ico" class="g-favicon" alt="Google">
                                    <span>Google Review</span>
                                </div>
                            </div>
                        </div>
                        <div class="g-review-stars">★★★★★</div>
                        <div class="g-review-date">1 month ago</div>
                        <p class="g-review-text">{{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}</p>
                        <div class="g-review-footer">
                            <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        </div>
                    </div>

                    {{-- Card 5 --}}
                    <div class="g-review-card">
                        <div class="g-review-header">
                            <div class="g-avatar" style="background:#9c27b0;">V</div>
                            <div class="g-reviewer-info">
                                <div class="g-reviewer-name">Vikram Nair</div>
                                <div class="g-reviewer-meta">
                                    <img src="https://www.google.com/favicon.ico" class="g-favicon" alt="Google">
                                    <span>Google Review</span>
                                </div>
                            </div>
                        </div>
                        <div class="g-review-stars">★★★★★</div>
                        <div class="g-review-date">2 months ago</div>
                        <p class="g-review-text">{{ __('Replace with real customer review text here — the card will auto-clamp to 4 lines.') }}</p>
                        <div class="g-review-footer">
                            <svg class="g-google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        </div>
                    </div>

                </div>{{-- /.testimonials-carousel --}}
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             OUR STORY
        ══════════════════════════════════════════ --}}
        <div class="as-story">
            <div class="container">
                <div class="as-story-grid">
                    <div class="as-story-img">
                        <img src="{{ asset('assets/img/who.jpg') }}" alt="Our Story">
                    </div>
                    <div>
                        <div class="as-story-label">Our Story</div>
                        <h2 class="as-story-title">A Kitchen Full of Warmth &amp; Aroma</h2>
                        <p class="as-story-body">Growing up in South India, we would always watch Amma lovingly blend her spices fresh before any meal preparation — filling our home with warmth and aroma.</p>
                        <p class="as-story-body">From our little home-run family kitchen, we bring you authentic South Indian spice blends, crafted just like she made them — pure, traditional, and filled with motherly love. Every blend is made to bring you the nostalgic taste of home-cooked meals. Our mission is to make your daily cooking nourishing, easy, and full of flavour — just like Amma intended.</p>
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
                            <img src="{{ asset('assets/img/Chemical-Free-Skin-Safe.png') }}" alt="Lab Tested">
                        </figure>
                        <div class="icon-box-content">
                            <h3 class="icon-title">All products are lab-tested and certified</h3>
                        </div>
                    </div>
                    <div class="icon-box text-center pt-0">
                        <figure class="m-0">
                            <img src="{{ asset('assets/img/Reduced_chemical_exposure.png') }}" alt="No Chemicals">
                        </figure>
                        <div class="icon-box-content">
                            <h3 class="icon-title">No preservatives or artificial additives</h3>
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
                    <div class="icon-box text-center pt-0">
                        <figure class="m-0">
                            <img src="{{ asset('assets/img/Healthier_skin.png') }}" alt="Authentic">
                        </figure>
                        <div class="icon-box-content">
                            <h3 class="icon-title">Authentic South Indian recipes &amp; flavours</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             BLOG SECTION
        ══════════════════════════════════════════ --}}
        <div class="blog-section">
            <div class="container">
                <div class="as-head">
                    <h2>From Our Kitchen &amp; Blog</h2>
                    <div class="as-divider"><span class="as-divider-dot"></span></div>
                </div>

                @if (isset($blogs) && $blogs->count() > 0)
                    <div class="owl-carousel blog-carousel owl-simple" data-toggle="owl"
                        data-owl-options='{
                            "nav": false,
                            "loop": {{ $blogs->count() > 3 ? "true" : "false" }},
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
                                        <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ $blog->title }}" loading="lazy">
                                    @else
                                        <img src="{{ asset('assets/img/blog-placeholder.jpg') }}" alt="{{ $blog->title }}" loading="lazy">
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
                                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </span>
                                </div>

                                {{-- Footer --}}
                                <div class="blog-card__footer">
                                    <div class="blog-card__author">
                                        <div class="blog-card__avatar">{{ strtoupper(substr($blog->author ?? 'A', 0, 1)) }}</div>
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
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                @else
                    <div style="text-align:center; padding:40px 0; color:var(--text3); font-family:'DM Sans',sans-serif; font-size:13px;">
                        Blog posts coming soon.
                    </div>
                @endif

            </div>
        </div>

    </div>
</main>

@endsection