@extends('admin.layout.app', ['title' => 'Banners'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

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
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">{{ __('keywords.Add') }} {{ __('keywords.Banner') }}</h4>
                    </div>

                    <div class="card-body">
                        <form class="forms-sample" action="{{ route('banners.store') }}" method="POST"
                            enctype="multipart/form-data" id="banner-store-form">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <label
                                        class="bmd-label-floating">{{ __('keywords.Image') }}<b> ({{ __('keywords.banner image size') }})</b></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="customFile" name="image"
                                            accept="image/*" required />
                                        <label class="custom-file-label"
                                            for="customFile">{{ __('keywords.Choose_File') }}</label>
                                    </div>
                                </div>
                            </div>

                            <br>
                            <button type="submit" class="btn btn-primary pull-center">{{ __('keywords.Submit') }}</button>
                            <a href="{{ route('banners') }}" class="btn">{{ __('keywords.Close') }}</a>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                $(".custom-file-input").on("change", function() {
                    var fileName = $(this).val().split("\\").pop();
                    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
                });
            });
        </script>
    @endpush
@endsection
