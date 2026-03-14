@extends('frontend.layouts.app', ['title' => ''])

@section('content')
    <main class="main">

        <div class="login-page">
            <div class="container">
               @livewire('auth-page')
            </div><!-- End .container -->
        </div><!-- End .login-page section-bg -->
    </main><!-- End .main -->
@endsection
