@extends('frontend.layouts.app', ['title' => ($blogs[0]->meta_title ?: $blog->title) . " — Amma's Spices"])

@section('content')

    <style>
        .main-nav .menu>li>a {
            color: #ffffff !important;
            text-shadow: none !important;
        }

        .header-28 .header-right .icon i,
        .header-28 .wishlist-link,
        .header-28 .cart-dropdown .dropdown-toggle,
        .header-28 .cart-txt,
        .header-28 .mobile-menu-toggler,
        .header-28 .mobile-menu-toggler i {
            color: #ffffff !important;
            text-shadow: none !important;
        }

        .header-28 .header-search .search-toggle {
            color: #ffffff !important;
        }

        .bsp-related {
            margin-top: 56px;
            /* padding: 42px 0 60px; */
            border-top: 0px solid #1e1e1e !important;
            padding-top: 42px;
            padding-bottom: 42px;
        }
    </style>

    {{-- SEO Meta Tags --}}
    {{-- @push('head')
<meta name="description" content="{{ $blog->meta_description ?: $blog->excerpt }}">
<meta property="og:title"       content="{{ $blog->meta_title ?: $blog->title }}">
<meta property="og:description" content="{{ $blog->meta_description ?: $blog->excerpt }}">
<meta property="og:image"       content="{{ $blog->thumbnail ? asset('storage/'.$blog->thumbnail) : '' }}">
<meta property="og:url"         content="{{ url()->current() }}">
<meta property="og:type"        content="article">
<meta name="twitter:card"       content="summary_large_image">
@endpush --}}

    <div class="bsp-page">

        <div class="bsp-bar" id="bspBar"></div>

        {{-- ══ HERO ══ --}}
        <div class="bsp-hero">
            <img src="{{ asset('assets/images/blog-banner.webp') }}" alt="Blogs & & Articles">
            <div class="bsp-hero__overlay"></div>
            <div class="bsp-hero__body">

                <h1 class="bsp-hero__title">Blogs & & Articles</h1>

            </div>
        </div>

        {{-- ══ BREADCRUMB ══ --}}
        <div class="bsp-crumb">
            <ul>
                <li><a href="{{ route('index') }}">Home</a></li>
                <li><span class="bsp-crumb__sep">›</span></li>
                <li>Blogs</li>
               
                {{-- <li><span class="bsp-crumb__sep">›</span></li> --}}
            </ul>
        </div>

        {{-- ══ MAIN LAYOUT ══ --}}
        <div class="container">

            {{-- Author + Share strip --}}


            {{-- ══ RELATED POSTS ══ --}}
            @if ($blogs->count() > 0)
                <div class="bsp-related container">
                    {{-- <div class="bsp-related__hd">
                        <h2>More from the Blog</h2>
                        <span class="bsp-related__sep"></span>
                    </div> --}}

                    <div class="bsp-related__grid">
                        @foreach ($blogs as $blog)
                            <a href="{{ route('customer.blog.show', $blog->slug) }}" class="blog-card">

                                {{-- Thumbnail --}}
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
                    </div>

                    {{-- <div class="bsp-back-wrap">
                        <a href="{{ route('customer.blog.index') }}" class="bsp-back">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                            Back to all articles
                        </a>
                    </div> --}}
                </div>
            @endif

        </div>{{-- /.bsp-wrap --}}

    </div>{{-- /.bsp-page --}}

    <script>
        (function() {
            // Reading progress bar
            var bar = document.getElementById('bspBar');
            if (bar) {
                window.addEventListener('scroll', function() {
                    var doc = document.documentElement;
                    var total = doc.scrollHeight - doc.clientHeight;
                    bar.style.width = (total > 0 ? (doc.scrollTop / total) * 100 : 0) + '%';
                }, {
                    passive: true
                });
            }

            // Copy link button
            var cp = document.getElementById('bspCopy');
            if (cp) {
                cp.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(window.location.href).then(function() {
                            cp.style.borderColor = '#e7c840';
                            cp.style.color = '#e7c840';
                            setTimeout(function() {
                                cp.style.borderColor = '';
                                cp.style.color = '';
                            }, 2000);
                        });
                    } else {
                        // Fallback for older browsers
                        var tmp = document.createElement('input');
                        tmp.value = window.location.href;
                        document.body.appendChild(tmp);
                        tmp.select();
                        document.execCommand('copy');
                        document.body.removeChild(tmp);
                    }
                });
            }
        })();
    </script>

@endsection
