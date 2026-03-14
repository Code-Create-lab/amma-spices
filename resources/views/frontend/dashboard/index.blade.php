@extends('frontend.layouts.app' ,['title' => ''])

@section('content')

<main class="main">
       <div class="page-header text-center">
        <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">My Account</h2>
                        <span class="seprater-img"><img src="{{ asset('assets/img/seprater.png') }}"></span>
                    </div>
                </div>
            </div><!-- End .container -->
        </div><!-- End .page-header -->
           

            <div class="page-content">
            	<div class="dashboard">
	                <div class="container">
	                	<div class="row">
	                		  @include('frontend.layouts.sidebar')

	                		<div class="col-md-8 col-lg-9">
	                			<div class="tab-content">
								    <div class="tab-pane fade show active" id="tab-dashboard" role="tabpanel" aria-labelledby="tab-dashboard-link">
								    	<p>Hello <span class="font-weight-normal text-dark">{{auth()->user()->name}}</span> (not <span class="font-weight-normal text-dark">{{auth()->user()->name}}</span>? <a href="{{ route('customer.logout') }}">Log out</a>) 
								    	<br>
								    	From your account dashboard you can view your <a href="{{ route('customer.orders.index') }}" class="tab-trigger-link link-underline">recent orders</a>, manage your <a href="#tab-address{{ route('dashboard_my_addresses') }}" class="tab-trigger-link">shipping and billing addresses</a>, and <a href="{{ route('profile.index') }}" class="tab-trigger-link">edit your password and account details</a>.</p>
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