@extends('frontend.layouts.app', ['title' => ''])

@section('content')
    <main class="main">
        <div class="page-header">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">My Account</h2>
                        <span class="seprater-img"><img src="{{ asset('assets/img/seprater.png') }}"></span>
                    </div>
                </div>
            </div><!-- End .container -->
        </div><!-- End .page-header -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Account</li>
                </ol>
            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->


        <div class="page-content">
            <div class="dashboard">
                <div class="container">
                    <div class="row">
                        @include('frontend.layouts.sidebar')

                        <div class="col-md-8 col-lg-9">
                            <div class="tab-content">

                                <div class="">
                                    <form method="POST" action="{{ route('profile.update') }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <label>First Name *</label>
                                                <input type="text" name="name" class="form-control" required
                                                    value="{{ auth()->user()->name }}">
                                            </div><!-- End .col-sm-6 -->

                                            <div class="col-sm-6">
                                                <label>Phone No*</label>
                                                <input type="text" name="user_phone" class="form-control" required
                                                    value="{{ auth()->user()->user_phone }}">
                                            </div><!-- End .col-sm-6 -->
                                        </div><!-- End .row -->

                                        {{-- <label>Display Name *</label>
		            						<input type="text" class="form-control" required>
		            						<small class="form-text">This will be how your name will be displayed in the account section and in reviews</small> --}}

                                        <label>Email address *</label>
                                        <input type="email" name="email" class="form-control" required
                                            value="{{ auth()->user()->email }}">

                                        {{-- <label>Current password (leave blank to leave unchanged)</label>
		            						<input type="password" class="form-control">

		            						<label>New password (leave blank to leave unchanged)</label>
		            						<input type="password" class="form-control">

		            						<label>Confirm new password</label>
		            						<input type="password" class="form-control mb-2"> --}}

                                        <button type="submit" class="btn btn-outline-primary-2">
                                            <span>Save Change</span>
                                            <!-- <i class="icon-long-arrow-right"></i> -->
                                        </button>
                                    </form>
                                </div><!-- .End .tab-pane -->
                            </div>
                        </div><!-- End .col-lg-9 -->
                    </div><!-- End .row -->
                </div><!-- End .container -->
            </div><!-- End .dashboard -->
        </div><!-- End .page-content -->
    </main><!-- End .main -->

    @push('scripts')
        <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
        {!! JsValidator::formRequest('App\Http\Requests\Frontend\Profile\ProfileUpdateRequest', '#profile-update-form') !!}
    @endpush
@endsection
