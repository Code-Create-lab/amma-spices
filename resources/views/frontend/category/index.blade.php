@extends('frontend.layouts.app', ['title' => $category->title])

@section('content')

{{-- BREADCRUMB --}}
<nav aria-label="breadcrumb" class="cp-breadcrumb">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
            @if ($category->parent && $category->parentObj->parentObj)
                <li class="breadcrumb-item">
                    <a href="{{ route('getCatList', $category->parentObj->parentObj->slug) }}">{{ $category->parentObj->parentObj->title }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('getCatList', $category->parentObj->slug) }}">{{ $category->parentObj->title }}</a>
                </li>
            @elseif($category->parentObj)
                <li class="breadcrumb-item">
                    <a href="{{ route('getCatList', $category->parentObj->slug) }}">{{ $category->parentObj->title }}</a>
                </li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $category->title }}</li>
        </ol>
    </div>
</nav>

{{-- FULL WIDTH BANNER --}}

<div class="cp-banner">
    <img class="cp-banner-img"
         src="{{ $category->banner_image ? $url_aws . $category->banner_image : ($category->image ? $url_aws . $category->image : asset('assets/img/seprater.png')) }}"
         alt="{{ $category->title }}">
    <div class="cp-banner-gradient"></div>
    <div class="cp-banner-body">
        <p class="cp-banner-super">Amma Spices</p>
        <h1 class="cp-banner-title">{{ $category->title }}</h1>
        @if($category->description)
            <p class="cp-banner-sub">{{ $category->description }}</p>
        @else
            <p class="cp-banner-sub">{{ $category->title }}</p>
        @endif
    </div>
    <div class="cp-banner-corner">Home Kitchen Crafted</div>
    <div class="cp-banner-line"></div>
</div>

{{-- MAIN CONTENT --}}
<div class="cp-page-wrap">
    <div class="container">

        {{-- SUB CATEGORIES --}}
        @if ($categories->isNotEmpty())
            <div class="cp-section-head">
                <h3>Browse Categories</h3>
                <div class="cp-section-sep"><span></span><img src="{{ asset('assets/img/seprater.png') }}" style="height:14px;"><span></span></div>
            </div>
            <div class="cp-subcat-grid">
                @foreach ($categories as $cat)
                    <div class="cp-subcat-card">
                        <a href="{{ route('getCatList', $cat->fullRouteParams()) }}">
                            <div class="cp-subcat-img-wrap">
                                <img src="{{ $url_aws . $cat->image }}" alt="{{ $cat->title }}">
                            </div>
                            <h4>{{ $cat->title }}</h4>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- PRODUCTS --}}
        @if (!$categories->where('slug', $category->slug)->first())
            @if($category->products->isNotEmpty())
                <div class="cp-section-head" style="margin-top:48px;">
                    <h3>Products</h3>
                    <div class="cp-section-sep"><span></span><img src="{{ asset('assets/img/seprater.png') }}" style="height:14px;"><span></span></div>
                </div>
                {{-- <div class="cp-prod-grid"> --}}
                      <x-product-list :products="$category->products" />
                    
                    
                {{-- </div> --}}
            @endif
        @endif

    </div>
</div>
@endsection
