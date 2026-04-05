<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title>{{ config('app.name') }} | Admin Login</title>
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/images/logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/logo.png') }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="ZAKH offers stylish women’s and men’s clothing, trendy outfits, chic fashion accessories, and modern apparel designed for a contemporary lifestyle.">
    <meta name="keywords"
        content="ZAKH, women’s fashion, men’s fashion, trendy clothing, stylish outfits, fashion accessories, modern chic style, contemporary wear, women’s apparel">
    <meta property="og:title" content="ZAKH – Modern Clothing & Accessories for Women and Men">
    <meta property="og:description"
        content="ZAKH offers stylish women’s and men’s clothing, trendy outfits, chic fashion accessories, and modern apparel designed for a contemporary lifestyle.">
    <meta property="og:image" content="{{ asset('assets/images/logo.png') }}">
    <meta property="og:url" content="{{ url('/admin/login') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ZAKH – Modern Clothing & Accessories for Women and Men">
    <meta name="twitter:description"
        content="Discover trendy fashion for women and men at ZAKH. Modern styles, chic outfits, and must-have accessories.">
    <meta name="twitter:image" content="{{ asset('assets/images/logo.png') }}">
    <meta property="og:site_name" content="ZAKH">
    <meta name="robots" content="noindex, nofollow">
    <!-- ================== BEGIN core-css ================== -->
    <link href="{{ url('assets_old/theme_assets/css/app.min.css') }}" rel="stylesheet" />
    {{-- <link rel="icon" type="image/png" href="{{ asset('/images/favicon.png') }}"> --}}
    <!-- ================== END core-css ================== -->
    <style type="text/css">
        div#app {
            /* //  background: url(/assets/images/login-bg.jpg); */
            background-position: center right;
            background-size: cover;
            height: 100vh;
        }
    </style>

</head>

<body>
    <!-- BEGIN #app -->
    <div id="app" class="login-app app-full-height app-without-header">
        <!-- BEGIN login -->
        <div class="login">

            <!-- BEGIN login-content -->
            <div class="login-content">
                <div align="center">
                    <img align="center" img style="height: 110px;" src="{{ asset('assets/img/logo-bodhi.png') }}"
                        alt="Amma's Spices Logo" />
                </div>
                <hr>
                @if (session()->has('success'))
                    <div class="alert alert-success">
                        @if (is_array(session()->get('success')))
                            <ul>
                                @foreach (session()->get('success') as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        @else
                            {{ session()->get('success') }}
                        @endif
                    </div>
                @endif
                @if (count($errors) > 0)
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first() }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                @endif
                <form action="{{ route('adminLoginCheck') }}" method="POST" name="login_form">
                    @csrf
                    <h1 class="text-center">{{ __('keywords.Sign In') }}</h1>
                    {{-- <div class="text-muted text-center mb-4">
                        {{ __('keywords.Admin/Sub-Admin Login.') }}
                    </div> --}}
                    <div class="form-group">
                        <label>{{ __('keywords.Email Address') }}</label>
                        <input data-validate = "Valid email is required: ex@abc.xyz" type="email"
                            class="form-control form-control-lg fs-15px" name="email"
                            placeholder="username@address.com" />
                    </div>
                    <div class="form-group">
                        <div class="d-flex">
                            <label>{{ __('keywords.Password') }}</label>
                            <a href="{{ route('reset_pass') }}"
                                class="ml-auto text-dark">{{ __('keywords.Forgot password?') }}</a>
                        </div>
                        <input type="password" class="form-control form-control-lg fs-15px" name="password"
                            placeholder="Enter your password" />
                        @include('admin.partials._googletagmanager')
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" value="" id="customCheck1" />
                            <label class="custom-control-label fw-500"
                                for="customCheck1">{{ __('keywords.Remember me') }}</label>
                        </div>
                    </div>
                    <button type="submit"
                        class="btn btn-primary btn-lg btn-block fw-500 mb-3">{{ __('keywords.Sign In') }}</button>
                </form>
            </div>
            <!-- END login-content -->
        </div>
        <!-- END login -->

        <!-- BEGIN btn-scroll-top -->
        <a href="#" data-click="scroll-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
        <!-- END btn-scroll-top -->
    </div>
    <!-- END #app -->

    <!-- ================== BEGIN core-js ================== -->
    <script src="{{ url('assets_old/theme_assets/js/app.min.js') }}"></script>
    <!-- ================== END core-js ================== -->

</body>

</html>
