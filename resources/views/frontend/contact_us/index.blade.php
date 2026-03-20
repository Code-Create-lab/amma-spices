@extends('frontend.layouts.app', ['title' => ''])

@section('content')
    <main class="main">
        <div class="page-header">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">Contact Us</h2>
                        <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                    </div>
                </div>
            </div><!-- End .container -->
        </div><!-- End .page-header -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav border-0 mb-0">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Contact us</li>
                </ol>
            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->



        <div class="page-content contact-us-page">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">

                        <div class="row">
                            <div class="col-sm-7">
                                <p>Amma’s Spices gets a lot of queries regarding our products.
                                    We are sure you would like to know about these too! Simple.
                                    Just connect with us</p>
                                <div class="contact-info">

                                    <ul class="contact-list">
                                        <li>
                                            <i class="icon-map-marker"></i>
                                            Shop No. UFF29 Signature Global, Sector 95A
                                            Gurugram, Haryana 122505
                                        </li>
                                        <li>
                                            <i class="icon-phone"></i>
                                            <a href="tel:8800952006">880 0952 006</a>
                                        </li>
                                        <li>
                                            <i class="icon-envelope"></i>
                                            <a href="mailto:info@ammasspices.com">info@ammasspices.com</a>

                                        </li>

                                    </ul><!-- End .contact-list -->
                                </div><!-- End .contact-info -->
                            </div><!-- End .col-sm-7 -->


                        </div><!-- End .row -->
                    </div><!-- End .col-lg-6 -->
                    <div class="col-lg-6">
                        <h2 class="title mb-1">Got Any Questions?</h2><!-- End .title mb-2 -->
                        <p class="mb-2">Use the form below to get in touch with the sales team</p>

                        @livewire('contact-us')

                    </div><!-- End .col-lg-6 -->
                </div><!-- End .row -->


            </div><!-- End .container -->
        </div><!-- End .page-content -->
    </main><!-- End .main -->
@endsection
