@extends('frontend.layouts.app', ['title' => '#'])

@section('content')
    <!-- start section -->
    <nav aria-label="breadcrumb" class="breadcrumb-nav">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Seach Result</li>
            </ol>
        </div><!-- End .container -->
    </nav><!-- End .breadcrumb-nav -->
    <!-- end section -->

    @if ($products->isNotEmpty())
        <!-- start section -->
        <div class="featured-list-slider">
            <img src="{{ asset('assets/img/bg-feture.png') }}" class="bg-feture-sec">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">Seach Result</h2>
                        <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                    </div>
                </div>
                <div class="row">
                    <x-product-list :products="$products" />

                </div>
            </div>
            <img src="assets/img/bg-feature2.png" class="bg-feture2-sec">
        </div>
    @endif
    <!-- end section -->
@endsection
