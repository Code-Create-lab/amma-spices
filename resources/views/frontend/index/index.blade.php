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
                <img src="{{ asset('assets/img/top_down.png') }}" class="top_down-sec" style="display: none">
                <img src="{{ asset('assets/img/bg-feture.png') }}" class="bg-feture-sec" style="display: none">
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
                <img src="assets/img/bg-feature2.png" class="bg-feture2-sec" style="display: none">
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
            <div class="testimonials-section" style="position: relative">
                <img src="{{ asset('assets/img/bg-feture.png') }}" class="bg-feture-sec" style="display: none">
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
                                <svg width="200" height="24" viewBox="0 0 74 24" xmlns="http://www.w3.org/2000/svg">
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
                                    <p class="g-review-text">Lorem Ipsum is simply dummy text of the printing and
                                        typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever
                                        since the 1500s, when an unknown printer took a galley of type and scrambled it to
                                        make a type specimen book. It has survived not only five centuries, but also the
                                        leap into electronic typesetting, remaining essentially unchanged. It was
                                        popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum
                                        passages, and more recently with desktop publishing software like Aldus PageMaker
                                        including versions of Lorem Ipsum</p>
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
                                    <p class="g-review-text">Lorem Ipsum is simply dummy text of the printing and
                                        typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever
                                        since the 1500s, when an unknown printer took a galley of type and scrambled it to
                                        make a type specimen book. It has survived not only five centuries, but also the
                                        leap into electronic typesetting, remaining essentially unchanged. It was
                                        popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum
                                        passages, and more recently with desktop publishing software like Aldus PageMaker
                                        including versions of Lorem Ipsum</p>
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
                                    <p class="g-review-text">Lorem Ipsum is simply dummy text of the printing and
                                        typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever
                                        since the 1500s, when an unknown printer took a galley of type and scrambled it to
                                        make a type specimen book. It has survived not only five centuries, but also the
                                        leap into electronic typesetting, remaining essentially unchanged. It was
                                        popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum
                                        passages, and more recently with desktop publishing software like Aldus PageMaker
                                        including versions of Lorem Ipsum</p>
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
                                    <p class="g-review-text">Lorem Ipsum is simply dummy text of the printing and
                                        typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever
                                        since the 1500s, when an unknown printer took a galley of type and scrambled it to
                                        make a type specimen book. It has survived not only five centuries, but also the
                                        leap into electronic typesetting, remaining essentially unchanged. It was
                                        popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum
                                        passages, and more recently with desktop publishing software like Aldus PageMaker
                                        including versions of Lorem Ipsum</p>
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
                                    <p class="g-review-text">Lorem Ipsum is simply dummy text of the printing and
                                        typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever
                                        since the 1500s, when an unknown printer took a galley of type and scrambled it to
                                        make a type specimen book. It has survived not only five centuries, but also the
                                        leap into electronic typesetting, remaining essentially unchanged. It was
                                        popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum
                                        passages, and more recently with desktop publishing software like Aldus PageMaker
                                        including versions of Lorem Ipsum</p>
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
                            <h2>Our Story</h2>
                            <p>Amma's Spices growing up in South of India, we would always watch Amma lovingly blend her
                                spices fresh before any meal preparation, thus filling our home with warmth and aroma.
                            </p>
                            <p>Now, from our little home-run family kitchen, we bring you these authentic South Indian spice
                                blends for you – crafted just like she made them, pure, traditional, and filled with
                                motherly love. Every blend is crafted with love to bring you the nostalgic taste of
                                home-cooked meals.
                                Our mission is to make your daily cooking nourishing, easy, and full of flavour – just like
                                Amma intended</p>
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

            {{-- =============================================
     BLOG SECTION — Paste just before closing </div><!-- .page-content -->
     Theme: Dark background #0d0d0d | Gold accent #e7c840
     ============================================= --}}

            <style>
                /* ─────────────────────────────────────────────
               BLOG SECTION WRAPPER
            ───────────────────────────────────────────── */
                .blog-section {
                    padding: 70px 0 80px;
                    /* background: #0d0d0d; */
                    position: relative;
                    overflow: hidden;
                }

                /* Subtle texture overlay matching site's dark sections */
                .blog-section::before {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background-image:
                        radial-gradient(ellipse 80% 50% at 50% 0%, rgba(227, 199, 64, 0.06) 0%, transparent 70%);
                    pointer-events: none;
                    z-index: 0;
                }

                /* ─────────────────────────────────────────────
               BLOG CARD
            ───────────────────────────────────────────── */
                .blog-card {
                    background: #181818;
                    border: 1px solid #2a2a2a;
                    border-radius: 12px;
                    overflow: hidden;
                    display: flex !important;
                    flex-direction: column;
                    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
                    text-decoration: none !important;
                    color: inherit !important;
                    margin: 6px 4px 16px;
                }

                .blog-card:hover {
                    transform: translateY(-6px);
                    border-color: #e7c840;
                    box-shadow: 0 12px 40px rgba(231, 200, 64, 0.12);
                }

                /* ── Thumbnail ── */
                .blog-card__thumb {
                    position: relative;
                    height: 195px;
                    overflow: hidden;
                    flex-shrink: 0;
                }

                .blog-card__thumb img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    display: block;
                    transition: transform 0.5s ease;
                    filter: brightness(0.88);
                }

                .blog-card:hover .blog-card__thumb img {
                    transform: scale(1.06);
                    filter: brightness(1);
                }

                /* Gold gradient overlay on image bottom */
                .blog-card__thumb::after {
                    content: '';
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    height: 60px;
                    background: linear-gradient(to top, #181818, transparent);
                    pointer-events: none;
                }

                /* Category pill */
                .blog-card__cat {
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

                /* ── Card Body ── */
                .blog-card__body {
                    padding: 18px 18px 14px;
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                }

                .blog-card__meta {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    font-size: 11px;
                    color: #777;
                    margin-bottom: 10px;
                    font-family: sans-serif;
                    letter-spacing: 0.03em;
                }

                .blog-card__meta .bdot {
                    width: 3px;
                    height: 3px;
                    background: #444;
                    border-radius: 50%;
                    display: inline-block;
                }

                .blog-card__title {
                    font-size: 15.5px;
                    font-weight: 700;
                    color: #f0f0f0;
                    line-height: 1.45;
                    margin: 0 0 10px;
                    font-family: 'Georgia', serif;
                    transition: color 0.2s ease;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }

                .blog-card:hover .blog-card__title {
                    color: #e7c840;
                }

                .blog-card__excerpt {
                    font-size: 12.5px;
                    color: #888;
                    line-height: 1.68;
                    margin: 0 0 14px;
                    flex: 1;
                    display: -webkit-box;
                    -webkit-line-clamp: 3;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                    font-family: sans-serif;
                }

                /* Read more link */
                .blog-card__read-more {
                    display: inline-flex;
                    align-items: center;
                    gap: 5px;
                    font-size: 11px;
                    font-weight: 800;
                    color: #e7c840;
                    letter-spacing: 0.1em;
                    text-transform: uppercase;
                    margin-top: auto;
                    font-family: sans-serif;
                    transition: gap 0.2s;
                }

                .blog-card__read-more svg {
                    transition: transform 0.22s ease;
                }

                .blog-card:hover .blog-card__read-more svg {
                    transform: translateX(4px);
                }

                /* ── Card Footer ── */
                .blog-card__footer {
                    border-top: 1px solid #252525;
                    padding: 11px 18px 13px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    flex-shrink: 0;
                }

                .blog-card__author {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    font-size: 11.5px;
                    color: #bbb;
                    font-weight: 500;
                    font-family: sans-serif;
                }

                .blog-card__avatar {
                    width: 26px;
                    height: 26px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #e7c840, #c8922a);
                    color: #0d0d0d;
                    font-size: 11px;
                    font-weight: 900;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    font-family: sans-serif;
                }

                .blog-card__tag {
                    font-size: 10px;
                    color: #555;
                    font-weight: 700;
                    letter-spacing: 0.08em;
                    text-transform: uppercase;
                    font-family: sans-serif;
                }

                /* ─────────────────────────────────────────────
               OWL CAROUSEL DOTS — Gold theme
            ───────────────────────────────────────────── */
                .blog-carousel .owl-dots {
                    margin-top: 28px;
                    text-align: center;
                }

                .blog-carousel .owl-dot span {
                    background: #333 !important;
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    transition: background 0.25s ease, width 0.25s ease;
                    margin: 0 4px;
                }

                .blog-carousel .owl-dot.active span {
                    background: #e7c840 !important;
                    width: 24px;
                    border-radius: 4px;
                }

                /* ─────────────────────────────────────────────
               VIEW ALL BUTTON
            ───────────────────────────────────────────── */
                .blog-view-all-wrap {
                    text-align: center;
                    margin-top: 40px;
                }

                .blog-view-all-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 9px;
                    padding: 13px 36px;
                    border: 2px solid #e7c840;
                    color: #e7c840;
                    font-size: 12px;
                    font-weight: 800;
                    letter-spacing: 0.12em;
                    text-transform: uppercase;
                    border-radius: 40px;
                    text-decoration: none !important;
                    background: transparent;
                    font-family: sans-serif;
                    transition: background 0.25s ease, color 0.25s ease, transform 0.2s ease,
                        box-shadow 0.25s ease;
                }

                .blog-view-all-btn:hover {
                    background: #e7c840;
                    color: #0d0d0d !important;
                    transform: translateY(-2px);
                    box-shadow: 0 8px 28px rgba(231, 200, 64, 0.3);
                }

                .blog-view-all-btn svg {
                    transition: transform 0.2s ease;
                }

                .blog-view-all-btn:hover svg {
                    transform: translateX(4px);
                }
            </style>

            {{-- =============================================
     HOME PAGE — Blog Section (Dynamic)
     Requires: $blogs passed from HomeController
     ============================================= --}}

            <div class="blog-section mt-2">
                <div class="container" style="position:relative; z-index:1;">

                    <div class="row" style="display:block;">
                        <div class="heading">
                            <h2 class="title text-center">From Our Kitchen &amp; Blog</h2>
                            <span class="seprater-img"><img src="assets/img/seprater.png" alt=""></span>
                        </div>
                    </div>
{{-- @dd($blogs) --}}
                    @if (isset($blogs) && $blogs->count() > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="owl-carousel blog-carousel owl-simple" data-toggle="owl"
                                    data-owl-options='{
                            "nav": false,
                            "loop": {{ $blogs->count() > 3 ? 'true' : 'false' }},
                            "dots": true,
                            "autoplay": true,
                            "autoplayTimeout": 4000,
                            "autoplayHoverPause": true,
                            "margin": 22,
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
                                                    <img src="{{ asset('storage/' . $blog->thumbnail) }}"
                                                        alt="{{ $blog->title }}" loading="lazy">
                                                @else
                                                    <img src="assets/img/blog-placeholder.jpg" alt="{{ $blog->title }}"
                                                        loading="lazy">
                                                @endif
                                                @if ($blog->category)
                                                    <span class="blog-card__cat">{{ $blog->category }}</span>
                                                @endif
                                            </div>

                                            {{-- Body --}}
                                            <div class="blog-card__body">
                                                <div class="blog-card__meta">
                                                    <span>
                                                        {{ $blog->published_at ? $blog->published_at->format('d M Y') : $blog->created_at->format('d M Y') }}
                                                    </span>
                                                    <span class="bdot"></span>
                                                    <span>{{ $blog->read_time }} min read</span>
                                                </div>
                                                <h3 class="blog-card__title">{{ $blog->title }}</h3>
                                                <p class="blog-card__excerpt">{{ $blog->excerpt }}</p>
                                                <span class="blog-card__read-more">
                                                    Read Article
                                                    <svg width="14" height="14" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                                    </svg>
                                                </span>
                                            </div>

                                            {{-- Footer --}}
                                            <div class="blog-card__footer">
                                                <div class="blog-card__author">
                                                    <div class="blog-card__avatar">
                                                        {{ strtoupper(substr($blog->author ?? 'A', 0, 1)) }}
                                                    </div>
                                                    {{ $blog->author }}
                                                </div>
                                                @if ($blog->category)
                                                    <span class="blog-card__tag">{{ $blog->category }}</span>
                                                @endif
                                            </div>

                                        </a>
                                    @endforeach

                                </div>{{-- /.owl-carousel --}}
                            </div>
                        </div>

                        {{-- View All CTA --}}
                        <div class="blog-view-all-wrap">
                            <a href="{{ route('customer.blog.index') }}" class="blog-view-all-btn">
                                Explore All Articles
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2.5">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @else
                        {{-- No blogs published yet — hidden section --}}
                        <div
                            style="text-align:center; padding: 40px 0; color:#555; font-family:sans-serif; font-size:13px;">
                            Blog posts coming soon.
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </main>
@endsection
