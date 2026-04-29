@extends('frontend.layouts.app', ['title' => 'Cancellation And Refund Policy'])

@section('content')
    <main class="main">
        <div class="page-header">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">{{ $cancel_and_refund[0]->title }}</h2>
                        <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                    </div>
                </div>
            </div><!-- End .container -->
        </div><!-- End .page-header -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $cancel_and_refund[0]->title }}</li>
                </ol>
            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->

        <div class="container common-class-page">
            <div class="row">
                {{-- <h1>{{ $cancel_and_refund[0]->title }}</h1> --}}
                {!! $cancel_and_refund[0]->description !!}
            </div>
        </div>
    </main>
@endsection
