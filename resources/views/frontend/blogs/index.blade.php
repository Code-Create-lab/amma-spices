@extends('frontend.layouts.app', ['title' => ($blogs[0]->meta_title ?: $blog->title) . " — Amma's Spices"])

@section('content')

<style>
/* ═══════════════════════════════════════════════════════
   BLOG DETAIL PAGE — Amma's Spices Theme
   All rules scoped to .bsp-* — zero global overrides
   Theme: bg #0a0a0a | card #161616 | gold #e7c840
═══════════════════════════════════════════════════════ */

.bsp-page {
    background: #ffffff;
    color: #d0d0d0;
    min-height: 100vh;
}

.bsp-bar {
    position: fixed;
    top: 0; left: 0;
    height: 3px;
    width: 0%;
    background: linear-gradient(90deg, #e7c840, #c8922a);
    z-index: 9999;
    pointer-events: none;
    transition: width 0.08s linear;
}

.bsp-hero {
    position: relative;
    height: 540px;
    overflow: hidden;
}

.bsp-hero img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    filter: brightness(0.38);
    transform-origin: center;
    animation: bspZoom 14s ease-out forwards;
}

@keyframes bspZoom {
    from { transform: scale(1.08); }
    to   { transform: scale(1); }
}

.bsp-hero__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        rgba(10,10,10,0.0) 0%,
        rgba(10,10,10,0.55) 55%,
        rgba(10,10,10,0.92) 100%
    );
}

.bsp-hero__body {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 0 20px 52px;
    text-align: center;
    animation: bspUp 0.85s ease both;
}

@keyframes bspUp {
    from { opacity:0; transform: translateY(24px); }
    to   { opacity:1; transform: translateY(0); }
}

.bsp-hero__pill {
    display: inline-block;
    background: #e7c840;
    color: #0a0a0a;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    padding: 5px 16px;
    border-radius: 20px;
    margin-bottom: 20px;
    font-family: sans-serif;
}

.bsp-hero__title {
    font-family: 'Playfair Display', 'Georgia', serif;
    font-size: clamp(22px, 4vw, 44px);
    font-weight: 900;
    color: #fff;
    line-height: 1.2;
    max-width: 760px;
    margin: 0 auto 22px;
    text-shadow: 0 3px 20px rgba(0,0,0,0.6);
}

.bsp-hero__meta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    color: rgba(255,255,255,0.65);
    font-size: 12.5px;
    font-family: sans-serif;
    flex-wrap: wrap;
}

.bsp-hero__meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.bsp-hero__meta .bsp-dot { opacity: 0.3; }

.bsp-crumb {
    background: #111;
    border-bottom: 1px solid #1e1e1e;
    padding: 11px 0;
}

.bsp-crumb ul {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    gap: 7px;
    list-style: none;
    font-family: sans-serif;
    font-size: 11.5px;
    color: #555;
    flex-wrap: wrap;
}

.bsp-crumb a { color: #e7c840; text-decoration: none; }
.bsp-crumb a:hover { opacity: 0.75; }
.bsp-crumb__sep { color: #333; font-size: 10px; }

.bsp-wrap {
    max-width: 840px;
    margin: 0 auto;
    padding: 0 24px;
}

.bsp-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 26px 0 22px;
    border-bottom: 1px solid #1e1e1e;
    flex-wrap: wrap;
    gap: 14px;
    animation: bspUp 0.6s 0.15s ease both;
}

.bsp-author { display: flex; align-items: center; gap: 12px; }

.bsp-author__av {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e7c840, #c8922a);
    color: #0a0a0a;
    font-family: Georgia, serif;
    font-size: 18px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.bsp-author__name { font-size: 13.5px; font-weight: 700; color: #eee; font-family: sans-serif; }
.bsp-author__role { font-size: 11.5px; color: #555; font-family: sans-serif; margin-top: 2px; }

.bsp-share { display: flex; align-items: center; gap: 8px; }

.bsp-share__lbl {
    font-size: 10.5px; font-weight: 700; letter-spacing: 0.1em;
    text-transform: uppercase; color: #555; font-family: sans-serif;
}

.bsp-share__btn {
    width: 33px; height: 33px;
    border-radius: 50%;
    border: 1px solid #2a2a2a;
    background: #161616;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
    color: #666;
    transition: border-color 0.2s, color 0.2s, background 0.2s;
    cursor: pointer;
}

.bsp-share__btn:hover {
    border-color: #e7c840;
    color: #e7c840;
    background: rgba(231,200,64,0.07);
}

.bsp-article {
    padding: 38px 0 0;
    animation: bspUp 0.7s 0.28s ease both;
}

.bsp-lead {
    font-family: 'Lora', Georgia, serif;
    font-size: 18.5px;
    font-style: italic;
    color: #ddd;
    line-height: 1.8;
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #1e1e1e;
}

.bsp-lead::first-letter {
    float: left;
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 72px;
    font-weight: 900;
    color: #e7c840;
    line-height: 0.78;
    margin: 8px 12px 0 0;
}

.bsp-article p {
    font-family: 'Lora', Georgia, serif;
    font-size: 16px;
    line-height: 1.88;
    color: #aaa;
    margin-bottom: 22px;
}

.bsp-article p strong { color: #e0e0e0; }

.bsp-article h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 22px;
    font-weight: 700;
    color: #f0f0f0;
    margin: 44px 0 14px;
    padding-left: 16px;
    border-left: 3px solid #e7c840;
    line-height: 1.3;
}

.bsp-article h3 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 18px;
    font-weight: 700;
    color: #e0e0e0;
    margin: 32px 0 12px;
}

.bsp-figure {
    margin: 34px 0;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #1e1e1e;
}

.bsp-figure img {
    width: 100%;
    display: block;
    max-height: 420px;
    object-fit: cover;
    filter: brightness(0.9);
    transition: filter 0.3s;
}

.bsp-figure:hover img { filter: brightness(1); }

.bsp-figure figcaption {
    background: #111;
    border-top: 1px solid #1e1e1e;
    font-family: sans-serif;
    font-size: 11.5px;
    color: #555;
    padding: 9px 16px;
    text-align: center;
    font-style: italic;
}

.bsp-quote {
    margin: 38px 0;
    padding: 26px 30px;
    background: #111;
    border-left: 3px solid #e7c840;
    border-radius: 0 10px 10px 0;
    position: relative;
    overflow: hidden;
}

.bsp-quote::before {
    content: '\201C';
    position: absolute;
    top: -16px; left: 18px;
    font-family: Georgia, serif;
    font-size: 90px;
    color: #e7c840;
    opacity: 0.12;
    line-height: 1;
    pointer-events: none;
}

.bsp-quote p {
    font-family: 'Playfair Display', Georgia, serif !important;
    font-size: 18px !important;
    font-style: italic;
    color: #e0e0e0 !important;
    line-height: 1.65 !important;
    margin: 0 !important;
}

.bsp-quote cite {
    display: block;
    margin-top: 14px;
    font-family: sans-serif;
    font-size: 11.5px;
    font-style: normal;
    color: #e7c840;
    font-weight: 700;
    letter-spacing: 0.06em;
}

.bsp-article ul, .bsp-article ol {
    list-style: none;
    padding: 0; margin: 0 0 24px;
    background: #111;
    border: 1px solid #1e1e1e;
    border-radius: 10px;
    overflow: hidden;
}

.bsp-article ul li, .bsp-article ol li {
    position: relative;
    padding: 12px 16px 12px 42px;
    font-family: sans-serif;
    font-size: 14.5px;
    color: #aaa;
    border-bottom: 1px solid #1a1a1a;
    line-height: 1.6;
}

.bsp-article ul li:last-child, .bsp-article ol li:last-child { border-bottom: none; }
.bsp-article ul li strong, .bsp-article ol li strong { color: #ddd; }

.bsp-article ul li::before {
    content: '';
    position: absolute;
    left: 17px; top: 21px;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #e7c840;
}

.bsp-article ol { counter-reset: bsp-n; }

.bsp-article ol li::before {
    counter-increment: bsp-n;
    content: counter(bsp-n);
    position: absolute;
    left: 12px; top: 11px;
    width: 22px; height: 22px;
    background: #e7c840;
    color: #0a0a0a;
    font-size: 10.5px;
    font-weight: 800;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: sans-serif;
}

.bsp-tip {
    margin: 30px 0;
    padding: 18px 20px;
    background: rgba(231,200,64,0.04);
    border: 1px dashed rgba(231,200,64,0.28);
    border-radius: 10px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
}

.bsp-tip__icon { font-size: 22px; flex-shrink: 0; line-height: 1; margin-top: 2px; }

.bsp-tip__text { font-family: sans-serif; font-size: 13.5px; color: #999; line-height: 1.68; }

.bsp-tip__text strong {
    display: block; margin-bottom: 4px;
    font-weight: 700; color: #e7c840;
    font-size: 12px; letter-spacing: 0.06em; text-transform: uppercase;
}

.bsp-tags {
    margin-top: 42px;
    padding-top: 24px;
    border-top: 1px solid #1e1e1e;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 9px;
}

.bsp-tags__lbl {
    font-family: sans-serif; font-size: 10.5px; font-weight: 800;
    letter-spacing: 0.1em; text-transform: uppercase; color: #444; margin-right: 4px;
}

.bsp-tag {
    background: #141414;
    border: 1px solid #2a2a2a;
    color: #777;
    font-family: sans-serif; font-size: 12px; font-weight: 500;
    padding: 5px 14px; border-radius: 20px;
    text-decoration: none;
    transition: border-color 0.2s, color 0.2s;
}

.bsp-tag:hover { border-color: #e7c840; color: #e7c840; background: rgba(231,200,64,0.06); }

.bsp-related { margin-top: 56px; padding: 42px 0 60px; border-top: 1px solid #1e1e1e; }

.bsp-related__hd { text-align: center; margin-bottom: 32px; }

.bsp-related__hd h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 22px; font-weight: 700; color: #fff;
    letter-spacing: 0.06em; text-transform: uppercase; margin: 0 0 12px;
}

.bsp-related__sep {
    display: block; width: 60px; height: 2px;
    background: linear-gradient(90deg, transparent, #e7c840, transparent);
    margin: 0 auto;
}

.bsp-related__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

@media (max-width: 700px) { .bsp-related__grid { grid-template-columns: 1fr; } }
@media (min-width: 701px) and (max-width: 860px) { .bsp-related__grid { grid-template-columns: repeat(2, 1fr); } }

.bsp-rc {
    background: #161616;
    border: 1px solid #222;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none !important;
    color: inherit;
    display: block;
    transition: transform 0.28s ease, border-color 0.28s ease, box-shadow 0.28s ease;
}

.bsp-rc:hover { transform: translateY(-5px); border-color: #e7c840; box-shadow: 0 10px 30px rgba(231,200,64,0.1); }

.bsp-rc__thumb { position: relative; height: 148px; overflow: hidden; }

.bsp-rc__thumb img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    filter: brightness(0.82);
    transition: transform 0.4s ease, filter 0.3s;
}

.bsp-rc:hover .bsp-rc__thumb img { transform: scale(1.06); filter: brightness(1); }

.bsp-rc__thumb::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 40px;
    background: linear-gradient(to top, #161616, transparent);
    pointer-events: none;
}

.bsp-rc__cat {
    position: absolute; top: 11px; left: 11px;
    background: #e7c840; color: #0a0a0a;
    font-size: 9.5px; font-weight: 800; letter-spacing: 0.1em;
    text-transform: uppercase; padding: 3px 10px; border-radius: 20px;
    font-family: sans-serif; z-index: 1;
}

.bsp-rc__body { padding: 14px 16px 16px; }

.bsp-rc__title {
    font-family: Georgia, serif; font-size: 14px; font-weight: 700;
    color: #eee; line-height: 1.42;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    transition: color 0.2s; margin-bottom: 8px;
}

.bsp-rc:hover .bsp-rc__title { color: #e7c840; }

.bsp-rc__meta {
    font-family: sans-serif; font-size: 11px; color: #555;
    display: flex; align-items: center; gap: 8px;
}

.bsp-rc__meta .bsp-mdot { width: 3px; height: 3px; border-radius: 50%; background: #444; display: inline-block; }

.bsp-back-wrap { margin-top: 36px; text-align: center; }

.bsp-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 11px 30px;
    border: 1.5px solid #2a2a2a; border-radius: 30px;
    color: #888; font-family: sans-serif;
    font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
    text-decoration: none;
    transition: border-color 0.2s, color 0.2s;
    background: #111;
}

.bsp-back:hover { border-color: #e7c840; color: #e7c840; background: rgba(231,200,64,0.05); }
.bsp-back svg { transition: transform 0.2s; }
.bsp-back:hover svg { transform: translateX(-3px); }

@media (max-width: 600px) {
    .bsp-hero { height: 360px; }
    .bsp-meta-row { flex-direction: column; align-items: flex-start; }
    .bsp-lead { font-size: 16.5px; }
    .bsp-article p { font-size: 15px; }
    .bsp-quote { padding: 20px 18px; }
    .bsp-hero__title { font-size: 22px; }
}
</style>

{{-- SEO Meta Tags --}}
@push('head')
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

                <h1 class="bsp-hero__title">Blogs & Articles</h1>

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
