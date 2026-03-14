@extends('frontend.layouts.app', ['title' => ''])

@section('content')
    <main class="main">
        <div class="page-content">
            <div class="intro-section">
                <div class="container">
                    <div class="row">
                        <div class="container-full-sb col-md-12">
                            <div class="owl-carousel inner-carousel owl-simple rows cols-1" data-toggle="owl"
                                data-owl-options='{"nav": false, "autoplay":true, "autoplayTimeout":3000, "dots": false, "loop": true}'>
                                @foreach ($banners as $banner)
                                    <div class="intro-slide">
                                        <img src="{{ asset('storage/' . $banner->image) }}" class="baneer-img-r">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collection-list-slider">
                <div class="container">
                    <div class="row" style="display: block;">
                        <div class="heading">
                            <h2 class="title  text-center">Collection List</h2>
                            <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="container-full-s">
                                <div class="owl-carousel collection-list owl-simple">
                                    @foreach ($categories as $category)
                                        @php
                                            $countProduct = 0;
                                            $countProduct += $category
                                                ->products()
                                                ->whereHas('variations', function ($query) {
                                                    $query->where('is_deleted', 0);
                                                })
                                                ->where('is_deleted', 0)
                                                ->count();
                                            foreach ($category->sub_categories as $subCat) {
                                                $countProduct += $subCat
                                                    ->products()
                                                    ->whereHas('variations', function ($query) {
                                                        $query->where('is_deleted', 0);
                                                    })
                                                    ->where('is_deleted', 0)
                                                    ->count();
                                            }
                                        @endphp
                                        <div class="items-box">
                                            <a href="{{ route('getCatList', $category->slug) }}">
                                                <div class="items">
                                                    <img src="{{ asset($category->image) }}">
                                                </div>
                                            </a>
                                            <h3>{{ $category->title }}</h3>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="featured-list-slider">
                <img src="{{ asset('assets/img/top_down.png') }}" class="top_down-sec">
                <img src="{{ asset('assets/img/bg-feture.png') }}" class="bg-feture-sec">
                <div class="container">
                    <div class="row" style="display: block;">
                        <div class="heading">
                            <h2 class="title  text-center">Featured Products</h2>
                            <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                        </div>
                    </div>
                    <div class="row">
                        <x-product-list :products="$products" />
                    </div>
                </div>
                <img src="assets/img/bg-feature2.png" class="bg-feture2-sec">
            </div>

            {{-- =============================================
                 OUR CERTIFICATIONS - KEPT COMMENTED AS REQUESTED
                 =============================================
            <div class="CERTIFICATIONS">
                <img src="assets/img/out-certi-bg.png" class="bg-feture3-sec">
                <div class="container">
                    <div class="row" style="display: block;">
                        <div class="heading">
                            <h2 class="title  text-center">OUR CERTIFICATIONS</h2>
                            <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                        </div>
                    </div>
                    <div class="service-section">
                        <div class="owl-carousel carousel-simple owl-simple" data-toggle="owl"
                            data-owl-options='{
                               "nav": true,
                               "loop": false,
                               "dots": false,
                               "margin": 10,
                               "responsive": {
                                   "576": {
                                       "items":2
                                   },
                                   "972": {
                                       "items":5
                                   }
                               }}'>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Soap-Intensive-Diploma-Certificate.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Advanced-CP-Soap-Certificate-1.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Boidy-Butters-&-Balms-Certificate.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Healthy-Haircare-Certificate.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Fizziology-Certificate.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Creams-&-Lotions-Certificate.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Conditioner-Bar-Course-Certificate.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Soap-Boot-Camp.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Shampoo-Bar-Course-Certificate.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-M&P-Soap-Certificate.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Hybrid-(CP-and-M&P)-Certificate.jpg"></div>
                            <div class="icon-boxf"><img src="assets/img/Niharica-Paul-Hot-Process-Certificate.jpg"></div>
                        </div>
                    </div>
                </div>
            </div>
            --}}

            {{-- =============================================
                 TESTIMONIALS SECTION
                 ============================================= --}}
            {{-- =============================================
     TESTIMONIALS SECTION - GOOGLE REVIEWS STYLE
     ============================================= --}}
            <div class="testimonials-section">
                <div class="container">
                    <div class="row" style="display: block;">
                        <div class="heading">
                            <h2 class="title text-center">What Our Customers Say</h2>
                            <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                        </div>
                    </div>

                    {{-- Google Rating Summary Bar --}}
                    <div class="google-rating-summary">
                        <div class="google-rating-left">
                            <div class="google-logo">
                                <svg width="74" height="24" viewBox="0 0 74 24" xmlns="http://www.w3.org/2000/svg">
                                    <text x="0" y="20" font-family="'Product Sans', Arial, sans-serif" font-size="22"
                                        font-weight="700">
                                        <tspan fill="#4285F4">G</tspan>
                                        <tspan fill="#EA4335">o</tspan>
                                        <tspan fill="#FBBC05">o</tspan>
                                        <tspan fill="#4285F4">g</tspan>
                                        <tspan fill="#34A853">l</tspan>
                                        <tspan fill="#EA4335">e</tspan>
                                    </text>
                                </svg>
                            </div>
                            <div class="google-overall-score">4.9</div>
                            <div class="google-overall-stars">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                            <div class="google-review-count">Based on 120+ reviews</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="owl-carousel testimonials-carousel owl-simple" data-toggle="owl"
                                data-owl-options='{
                        "nav": false,
                        "loop": true,
                        "dots": true,
                        "autoplay": true,
                        "autoplayTimeout": 4500,
                        "autoplayHoverPause": true,
                        "margin": 20,
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
                                                <img src="https://www.google.com/favicon.ico" class="g-favicon"
                                                    alt="Google">
                                                <span>Google Review</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="g-review-stars">
                                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                    </div>
                                    <div class="g-review-date">2 weeks ago</div>
                                    <p class="g-review-text">I've struggled with dry, sensitive skin for years. After
                                        switching to Amma's Spices, my skin feels softer and calmer. No more redness
                                        or
                                        itching. Truly a game-changer!</p>
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
                                    <div class="g-review-stars">
                                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                    </div>
                                    <div class="g-review-date">1 month ago</div>
                                    <p class="g-review-text">I bought the lavender soap for my toddler and it's been
                                        wonderful — gentle, fragrant, and lathers beautifully. I love knowing exactly what's
                                        going into my child's bathtime routine.</p>
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
                                    <div class="g-review-stars">
                                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                    </div>
                                    <div class="g-review-date">3 weeks ago</div>
                                    <p class="g-review-text">The charcoal detox bar is incredible. My face feels clean
                                        without that tight, stripped feeling. I've recommended it to all my friends. The
                                        packaging is beautiful too — makes a great gift.</p>
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

                                {{-- Card 4 --}}
                                <div class="g-review-card">
                                    <div class="g-review-header">
                                        <div class="g-avatar" style="background:#fbbc05;">S</div>
                                        <div class="g-reviewer-info">
                                            <div class="g-reviewer-name">Sneha Pillai</div>
                                            <div class="g-reviewer-meta">
                                                <img src="https://www.google.com/favicon.ico" class="g-favicon"
                                                    alt="Google">
                                                <span>Google Review</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="g-review-stars">
                                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                    </div>
                                    <div class="g-review-date">1 month ago</div>
                                    <p class="g-review-text">Finally, a natural soap that actually smells amazing AND lasts
                                        long. The honey oat bar has become a permanent part of my morning ritual. Worth
                                        every penny — quality over quantity.</p>
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
                                    <div class="g-review-stars">
                                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                    </div>
                                    <div class="g-review-date">2 months ago</div>
                                    <p class="g-review-text">As someone with eczema-prone skin, finding safe products is a
                                        challenge. Amma's Spices has been a revelation — no flare-ups, no irritation. I will
                                        never go back to commercial soap again.</p>
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

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="who-we-are">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6 col-lg-6 cols">
                            <img src="assets/img/who.jpg" style="width: 100%;">
                        </div>
                        <div class="col-sm-6 col-lg-6 who-we-are-inner">
                            <h2>A Conscious Choice for Skin & Planet</h2>
                            <p>Amma's Spices Soap Co. products may not look fancy but they work. With rising pollution and
                                increasing skin sensitivities, we believe it is time to return to basics and care for our
                                skin naturally. We do not claim miracles — only honesty.
                                My family and I have used these home-made products for years and the difference is felt, not
                                advertised.
                            </p>
                            <a href="{{ route('frontend.about-us') }}" class="know-more-btn">Know more</a>
                        </div>
                    </div>
                </div>
                <img src="assets/img/out-certi-bg-2.png" class="bg-feture4-sec">
            </div>

            <div class="whychose">
                <div class="container">
                    <div class="row" style="display: block;">
                        <div class="heading">
                            <h2 class="title  text-center">Why choose us</h2>
                            <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                        </div>
                    </div>
                    <div class="service-section">
                        <div class="owl-carousel carousel-simple" data-toggle="owl"
                            data-owl-options='{
                               "nav": false,
                               "loop": false,
                               "dots": false,
                               "margin": 20,
                               "responsive": {
                                   "576": {
                                       "items":2
                                   },
                                   "972": {
                                       "items":4
                                   }
                               }}'>
                            <div class="icon-box  text-center  pt-0">
                                <figure class="m-0">
                                    <img src="assets/img/Chemical-Free-Skin-Safe.png">
                                </figure>
                                <div class="icon-box-content">
                                    <h3 class="icon-title">All our products are lab-tested and certified</h3>
                                </div>
                            </div>
                            <div class="icon-box  text-center  pt-0">
                                <figure class="m-0">
                                    <img src="assets/img/Reduced_chemical_exposure.png">
                                </figure>
                                <div class="icon-box-content">
                                    <h3 class="icon-title">Reduced chemical<br> exposure</h3>
                                </div>
                            </div>
                            <div class="icon-box  text-center   pt-0">
                                <figure class="m-0">
                                    <img src="assets/img/lifestyle.png">
                                </figure>
                                <div class="icon-box-content">
                                    <h3 class="icon-title">A more sustainable<br> lifestyle</h3>
                                </div>
                            </div>
                            <div class="icon-box d text-center   pt-0">
                                <figure class="m-0">
                                    <img src="assets/img/Healthier_skin.png">
                                </figure>
                                <div class="icon-box-content">
                                    <h3 class="icon-title">Healthier skin means fewer salon and dermatology visits.</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
@endsection
