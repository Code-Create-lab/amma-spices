@extends('frontend.layouts.app', ['title' => ($blog->meta_title ?: $blog->title) . " — Amma's Spices"])

@section('content')

    <style>

    </style>

    {{-- SEO Meta Tags --}}
    @push('head')
        <meta name="description" content="{{ $blog->meta_description ?: $blog->excerpt }}">
        <meta property="og:title" content="{{ $blog->meta_title ?: $blog->title }}">
        <meta property="og:description" content="{{ $blog->meta_description ?: $blog->excerpt }}">
        <meta property="og:image" content="{{ $blog->thumbnail ? asset('storage/' . $blog->thumbnail) : '' }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="article">
        <meta name="twitter:card" content="summary_large_image">
    @endpush

    <div class="bsp-page">

        <div class="bsp-bar" id="bspBar"></div>

        {{-- ══ HERO ══ --}}
        <div class="bsp-hero">
            <img src="{{ $blog->thumbnail ? asset('storage/' . $blog->thumbnail) : 'https://images.unsplash.com/photo-1596797038530-2c107229654b?w=1400&q=85' }}"
                alt="{{ $blog->title }}">
            <div class="bsp-hero__overlay"></div>
            <div class="bsp-hero__body">
                @if ($blog->category)
                    <span class="bsp-hero__pill">{{ $blog->category }}</span>
                @endif
                <h1 class="bsp-hero__title">{{ $blog->title }}</h1>
                <div class="bsp-hero__meta">
                    <span>
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <path d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                        {{ $blog->published_at ? $blog->published_at->format('d F Y') : $blog->created_at->format('d F Y') }}
                    </span>
                    <span class="bsp-dot">•</span>
                    <span>
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                        {{ $blog->read_time }} min read
                    </span>
                    <span class="bsp-dot">•</span>
                    <span>By {{ $blog->author }}</span>
                </div>
            </div>
        </div>

        {{-- ══ BREADCRUMB ══ --}}
        <div class="bsp-crumb">
            <ul>
                <li><a href="{{ route('index') }}">Home</a></li>
                <li><span class="bsp-crumb__sep">›</span></li>
                <li><a href="{{ route('customer.blog.index') }}">Blog</a></li>
                @if ($blog->category)
                    <li><span class="bsp-crumb__sep">›</span></li>
                    <li><a
                            href="{{ route('customer.blog.index') }}?category={{ urlencode($blog->category) }}">{{ $blog->category }}</a>
                    </li>
                @endif
                <li><span class="bsp-crumb__sep">›</span></li>
                <li>{{ Str::limit($blog->title, 40) }}</li>
            </ul>
        </div>

        {{-- ══ MAIN LAYOUT ══ --}}
        <div class="bsp-wrap">

            {{-- Author + Share strip --}}
            <div class="bsp-meta-row">
                <div class="bsp-author">
                    <div class="bsp-author__av">{{ strtoupper(substr($blog->author ?? 'A', 0, 1)) }}</div>
                    <div>
                        <div class="bsp-author__name">{{ $blog->author }}</div>
                        <div class="bsp-author__role">{{ $blog->author_role }}</div>
                    </div>
                </div>

                {{-- Social share — real URLs --}}
                @php
                    $shareUrl = urlencode(url()->current());
                    $shareTitle = urlencode($blog->title);
                @endphp

                <div class="bsp-share">
                    <span class="bsp-share__lbl">Share</span>

                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank"
                        rel="noopener noreferrer" class="bsp-share__btn" title="Share on Facebook">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                        </svg>
                    </a>

                    {{-- X / Twitter --}}
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}"
                        target="_blank" rel="noopener noreferrer" class="bsp-share__btn" title="Share on X">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>

                    {{-- WhatsApp --}}
                    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank"
                        rel="noopener noreferrer" class="bsp-share__btn" title="Share on WhatsApp">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                    </a>

                    {{-- Copy Link --}}
                    <a href="#" class="bsp-share__btn" title="Copy link" id="bspCopy">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- ══ ARTICLE BODY ══ --}}
            <article class="bsp-article">

                {{-- Content is stored as HTML — rendered safely --}}
                {!! $blog->content !!}

                {{-- Tags --}}
                @if ($blog->tags && count($blog->tags) > 0)
                    <div class="bsp-tags">
                        <span class="bsp-tags__lbl">Tags</span>
                        @foreach ($blog->tags as $tag)
                            <a href="{{ route('customer.blog.index') }}?tag={{ urlencode($tag) }}" class="bsp-tag">
                                {{ $tag }}
                            </a>
                        @endforeach
                    </div>
                @endif

            </article>



        </div>{{-- /.bsp-wrap --}}

    </div>{{-- /.bsp-page --}}
    {{-- ══ RELATED POSTS ══ --}}
    @if ($related->count() > 0)
        <div class="bsp-related container">
            <div class="bsp-related__hd">
                <h2>More from the Blog</h2>
                <span class="bsp-related__sep"></span>
            </div>

            <div class="bsp-related__grid">
                @foreach ($related as $rel)
                    <a href="{{ route('customer.blog.show', $blog->slug) }}" class="blog-card">

                        {{-- Thumbnail --}}
                        <div class="blog-card__thumb">
                            @if ($blog->thumbnail)
                                <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ $blog->title }}"
                                    loading="lazy">
                            @else
                                <img src="{{ asset('assets/img/blog-placeholder.jpg') }}" alt="{{ $blog->title }}"
                                    loading="lazy">
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

            <div class="bsp-back-wrap">
                <a href="{{ route('customer.blog.index') }}" class="bsp-back">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    Back to all articles
                </a>
            </div>
        </div>
    @endif

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
