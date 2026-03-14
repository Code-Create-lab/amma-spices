@extends('frontend.layouts.app', ['title' => ''])

@section('content')
    {{-- Address Create Modal --}}
    <div class="modal fade" id="address_model" tabindex="-1" role="dialog" aria-labelledby="addAddressModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Address</h5>
                    <button type="button" class="close close-modal-edit-address" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="address-store-form" method="POST" action="{{ route('customer.address.store') }}">
                        @csrf

                        <div class="form-group">
                            <label>Address Type</label>
                            <div class="d-flex">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="ad1" name="type"
                                        value="Home" checked>
                                    <label class="form-check-label" for="ad1">Home</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="ad2" name="type"
                                        value="Office">
                                    <label class="form-check-label" for="ad2">Office</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="ad3" name="type"
                                        value="Other">
                                    <label class="form-check-label" for="ad3">Other</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label>Name*</label>
                            <input id="receiver_name" name="receiver_name" type="text" class="form-control" required>
                        </div>

                        <div class="form-group mt-2">
                            <label>Phone Number*</label>
                            <input id="receiver_phone" name="receiver_phone" type="text" class="form-control" required>
                        </div>

                        <div class="form-group mt-2">
                            <label>Email*</label>
                            <input id="receiver_email" name="receiver_email" type="email" class="form-control" required>
                        </div>

                        <div class="form-group mt-2">
                            <label>Flat / House / Office No.*</label>
                            <input id="house_no" name="house_no" type="text" class="form-control" required>
                        </div>

                        <div class="form-group mt-2">
                            <label>Street / Society / Office Name*</label>
                            <input id="street" name="society" type="text" class="form-control" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>City*</label>
                                <input id="city" name="city" type="text" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>State*</label>
                                <input id="state" name="state" type="text" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Pincode*</label>
                            <input id="pincode" name="pincode" type="text" class="form-control" required>
                        </div>

                        <div class="form-group mt-2">
                            <button type="submit" class="btn btn-primary btn-block">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- End Address Create Modal --}}
    <main class="main">
        <div class="page-header">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">My Address</h2>
                        <span class="seprater-img"><img src="{{ asset('assets/img/seprater.png') }}"></span>
                    </div>
                </div>
            </div><!-- End .container -->
        </div><!-- End .page-header -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Address</li>
                </ol>
            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->

        <div class="page-content">
            <div class="dashboard">
                <div class="container">
                    <div class="row">
                        @include('frontend.layouts.sidebar')

                        <div class="col-md-8 col-lg-9">
                            <div class="" id="tab-address" role="tabpanel" aria-labelledby="tab-address-link">
                                <p>The following addresses will be used on the checkout page by default.</p>
                                <a href="#" data-toggle="modal" data-target="#address_model">
                                    <button class="custom-btn-Address btn btn-outline-primary btn-rounded">Add New
                                        Address</button>
                                </a>
                                <div class="row">

                                    @if ($addresses->isNotEmpty())
                                        @foreach ($addresses as $address)
                                            <div class="col-lg-6 mt-2">
                                                <div class="card card-dashboard">
                                                    <div class="card-body">
                                                        <h3 class="card-title">{{ $address->type }}</h3>
                                                        <!-- End .card-title -->

                                                        <p>{{ $address->house_no }},{{ $address->society }},
                                                            {{ $address->state }},
                                                            {{ $address->city }},{{ $address->pincode }} </p>
                                                        {{-- <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="433a2c36312e222a2f032e222a2f6d202c2e">[email&#160;protected]</a><br> --}}
                                                        {{-- <a href="#" class="edit-address"  data-address-id="{{ $address->uuid }}"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#edit_address_model">Edit <i class="icon-edit"></i></a></p> --}}

                                                        <a href="#" class="edit-address"
                                                            data-address-id="{{ $address->uuid }}" data-toggle="modal"
                                                            data-target="#edit_address_model">
                                                            Edit <i class="icon-edit"></i>
                                                        </a>
                                                        <a href="{{ route('customer.address.delete', $address->uuid) }}"
                                                            class="delete-address">
                                                            Delete <i class="icon-edit"></i>
                                                        </a>
                                                    </div><!-- End .card-body -->
                                                </div><!-- End .card-dashboard -->
                                            </div><!-- End .col-lg-6 -->
                                        @endforeach
                                    @else
                                        <h2 class="mt-2">No Addresses Found</h2>
                                    @endif
                                </div><!-- End .row -->
                            </div><!-- .End .tab-pane -->
                        </div><!-- End .col-lg-9 -->
                    </div><!-- End .row -->
                </div><!-- End .container -->
            </div><!-- End .dashboard -->
        </div><!-- End .page-content -->
    </main><!-- End .main -->

    {{-- Address Edit Modal --}}
    {{-- <div class="header-cate-model main-gambo-model modal fade" id="edit_address_model" tabindex="-1" role="dialog">
        <div class="modal-dialog category-area" role="document">
            <div class="category-area-inner">
                <div class="modal-header cate-header">
                    <h4 class="">Update Address</h4>
                    <button type="button" class="btn-close close-modal-edit-address" data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="uil uil-multiply"></i>
                    </button>
                </div>
                <div class="category-model-content modal-content">
                   
                    <div class="add-address-form">
                        <div class="checout-address-step">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form id="address-edit-form" method="POST"
                                        action="{{ route('customer.address.update') }}" class="address_model_form">
                                        @csrf
                                        <!-- Multiple Radios (inline) -->
                                        <div class="form-group">
                                            <input type="hidden" name="address_id" />
                                            <div class="product-radio">
                                                <ul class="product-now">
                                                    <li>
                                                        <input type="radio" id="addd1" name="type"
                                                            value="Home">
                                                        <label for="addd1" class="hover-btn">Home</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" id="addd2" name="type"
                                                            value="Office">
                                                        <label for="addd2" class="hover-btn">Office</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" id="addd3" name="type"
                                                            value="Other">
                                                        <label for="addd3" class="hover-btn">Other</label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="address-fieldset">
                                            <div class="row">
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="form-group mt-4">
                                                        <label class="control-label">Name*</label>
                                                        <input id="receiver_name" name="receiver_name" type="text"
                                                            placeholder="Name" class="form-control input-md" required />
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="form-group mt-4">
                                                        <label class="control-label">Phone Number*</label>
                                                        <input id="receiver_phone" name="receiver_phone" type="text"
                                                            placeholder="Contact" class="form-control input-md"
                                                            required />
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="form-group mt-4">
                                                        <label class="control-label">Email*</label>
                                                        <input id="receiver_email" name="receiver_email" type="text"
                                                            placeholder="Email" class="form-control input-md" required />
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="form-group mt-4">
                                                        <label class="control-label">Flat / House / Office No.*</label>
                                                        <input id="house_no" name="house_no" type="text"
                                                            placeholder="Address" class="form-control input-md"
                                                            required />
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="form-group mt-4">
                                                        <label class="control-label">Street / Society / Office
                                                            Name*</label>
                                                        <input id="street" name="society" type="text"
                                                            placeholder="Street Address" class="form-control input-md">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="form-group mt-4">
                                                        <label class="control-label">City*</label>
                                                        <input id="city" name="city" type="text"
                                                            placeholder="City" class="form-control input-md" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="form-group mt-4">
                                                        <label class="control-label">State*</label>
                                                        <input id="state" name="state" type="text"
                                                            placeholder="State" class="form-control input-md" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-12">
                                                    <div class="form-group mt-4">
                                                        <label class="control-label">Pincode*</label>
                                                        <input id="pincode" name="pincode" type="text"
                                                            placeholder="Pincode" class="form-control input-md"
                                                            required="">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="form-group mt-30">
                                                        <div class="address-btns">
                                                            <button class="w-100 hover-btn">Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="modal fade" id="edit_address_model" tabindex="-1" role="dialog"
        aria-labelledby="editAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Address</h5>
                    <button type="button" class="close close-modal-edit-address" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="address-edit-form" method="POST" action="{{ route('customer.address.update') }}">
                        @csrf
                        <input type="hidden" name="address_id" />

                        <div class="form-group">
                            <label>Address Type</label>
                            <div class="d-flex">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="addd1" name="type"
                                        value="Home">
                                    <label class="form-check-label" for="addd1">Home</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="addd2" name="type"
                                        value="Office">
                                    <label class="form-check-label" for="addd2">Office</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="addd3" name="type"
                                        value="Other">
                                    <label class="form-check-label" for="addd3">Other</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label>Name*</label>
                            <input id="receiver_name" name="receiver_name" type="text" class="form-control" required>
                        </div>

                        <div class="form-group mt-3">
                            <label>Phone Number*</label>
                            <input id="receiver_phone" name="receiver_phone" type="text" class="form-control"
                                required>
                        </div>

                        <div class="form-group mt-3">
                            <label>Email*</label>
                            <input id="receiver_email" name="receiver_email" type="email" class="form-control"
                                required>
                        </div>

                        <div class="form-group mt-3">
                            <label>Flat / House / Office No.*</label>
                            <input id="house_no" name="house_no" type="text" class="form-control" required>
                        </div>

                        <div class="form-group mt-3">
                            <label>Street / Society / Office Name*</label>
                            <input id="street" name="society" type="text" class="form-control" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mt-3">
                                <label>City*</label>
                                <input id="city" name="city" type="text" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6 mt-3">
                                <label>State*</label>
                                <input id="state" name="state" type="text" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label>Pincode*</label>
                            <input id="pincode" name="pincode" type="text" class="form-control" required>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block">Save</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- End Address Edit Modal --}}

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
        {!! JsValidator::formRequest('App\Http\Requests\Frontend\Profile\ProfileUpdateRequest', '#profile-update-form') !!}

        <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
        {!! JsValidator::formRequest('App\Http\Requests\Frontend\Address\AddressStoreRequest', '#address-store-form') !!}
        {!! JsValidator::formRequest('App\Http\Requests\Frontend\Address\AddressUpdateRequest', '#address-edit-form') !!}
        <script>
            $(document).on('click', '.edit-address', function() {
                var address_id = $(this).data('address-id');
                $.ajax({
                    url: '{{ route('customer.address.edit') }}',
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    data: {
                        address_id: address_id
                    },
                    success: function(response) {
                        var address = response.address;
                        console.log(address);
                        $('#address-edit-form input[name="receiver_name"]').val(address.receiver_name);
                        $('#address-edit-form input[name="receiver_phone"]').val(address.receiver_phone);
                        $('#address-edit-form input[name="receiver_email"]').val(address.receiver_email);
                        $('#address-edit-form input[name="email"]').val(address.receiver_email);
                        $('#address-edit-form input[name="house_no"]').val(address.house_no);
                        $('#address-edit-form input[name="society"]').val(address.society);
                        $('#address-edit-form input[name="city"]').val(address.city);
                        $('#address-edit-form input[name="state"]').val(address.state);
                        $('#address-edit-form input[name="pincode"]').val(address.pincode);
                        $('#address-edit-form input[name="address_id"]').val(address.uuid);
                        $('#address-edit-form input[name="type"][value="' + address.type + '"]').prop(
                            'checked', true);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });
        </script>
    @endpush


@endsection
