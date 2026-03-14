<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title>{{ config('app.name') }} | Admin Login</title>
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/images/icons/apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/icons/apple-touch-icon.png')}}">

     <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/icons/apple-touch-icon.png') }}">
      <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="SOAP OPERA is your destination for elegant and trendy ladies' fashion. Discover stylish outfits, accessories, and timeless looks curated for modern women.">
    <meta name="keywords"
        content="SOAP OPERA, ladies fashion, women's clothing, trendy outfits, fashion accessories, modern style, chic wear, women's apparel">
    <meta property="og:title" content="SOAP OPERA – Trendy & Elegant Ladies' Fashion">
    <meta property="og:description"
        content="Explore the latest in ladies' fashion with SOAP OPERA. From casual chic to elegant styles, we've got looks you'll love.">
    <meta property="og:image" content="{{ asset('assets/images/Og_Soap.jpg') }}">
    <meta property="og:url" content="https://soapopera.in/">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SOAP OPERA – Trendy & Elegant Ladies' Fashion">
    <meta name="twitter:description" content="Stylish fashion for modern women. Shop the latest trends at SOAP OPERA.">
    <meta name="twitter:image" content="{{ asset('assets/images/Og_Soap.jpg') }}">
    <meta property="og:site_name" content="SOAP OPERA">
    <meta name="robots" content="noindex, nofollow">
    <!-- ================== BEGIN core-css ================== -->
    <link href="{{ url('assets_old/theme_assets/css/app.min.css') }}" rel="stylesheet" />
    {{-- <link rel="icon" type="image/png" href="{{ asset('/images/favicon.ico') }}"> --}}
    <!-- ================== END core-css ================== -->

</head>


<body>
    <!-- BEGIN #app -->
    <div id="app" class="app app-full-height app-without-header">
        <!-- BEGIN login -->
        <div class="login">

            <!-- BEGIN login-content -->
            <div class="login-content">
                <div align="center">
                    <img align="center" img style="height: 40px;"
                        src="{{ asset('assets/images/demos/demo-9/SOAP.png') }}" alt="IMG">
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
                <form action="{{ route('forgot_passwordadmin', $user->id) }}" method="POST" name="login_form">
                    @csrf
                    <h1 class="text-center">{{ __('keywords.Change Password') }}</h1>

                    <div class="form-group">
                        <label>{{ __('keywords.Password') }}</label>
                        <input type="hidden" name="token" value="{{ $id }}">
                        <input type="text" name="password" placeholder="New Password" class="form-control"><br><br>

                    </div>
                    <div class="form-group">
                        <label>{{ __('keywords.Confirm Password') }}</label>
                        <input type="text" name="password2" placeholder="Confirm Password"
                            class="form-control"><br><br>

                    </div>
                    <button type="submit"
                        class="btn btn-primary btn-lg btn-block fw-500 mb-3">{{ __('keywords.Change Password') }}</button>
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
    <script src="{{ url('assets/theme_assets/js/app.min.js') }}"></script>
    <!-- ================== END core-js ================== -->

</body>

</html>
