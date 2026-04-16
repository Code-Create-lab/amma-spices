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
                                <div class="col-md-6 mb-4">
                                    <label class="bmd-label-floating">{{ __('keywords.Image') }} (Desktop) <b>({{ __('keywords.banner image size') }})</b> <span class="text-danger">*</span></label>
                                    <div id="desktop-dropzone" class="dropzone-custom">
                                        <div class="dz-message">
                                            <i class="fa fa-cloud-upload fa-3x mb-3"></i>
                                            <h5>{{ __('keywords.Choose_File') }} or Drag & Drop</h5>
                                            <p class="text-muted small">Max file size: 2MB (JPG, PNG, WEBP)</p>
                                        </div>
                                    </div>
                                    <input type="file" id="desktop-input" style="display:none;" />
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="bmd-label-floating">Mobile Banner <b>(Optional)</b></label>
                                    <div id="mobile-dropzone" class="dropzone-custom">
                                        <div class="dz-message">
                                            <i class="fa fa-mobile fa-3x mb-3"></i>
                                            <h5>Choose Mobile Banner</h5>
                                            <p class="text-muted small">Optimized for small screens</p>
                                        </div>
                                    </div>
                                    <input type="file" id="mobile-input" style="display:none;" />
                                </div>
                            </div>

                            <br>
                            <button type="button" id="submit-all" class="btn btn-primary pull-center">{{ __('keywords.Submit') }}</button>
                            <a href="{{ route('banners') }}" class="btn">{{ __('keywords.Close') }}</a>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dropzone-custom {
            border: 2px dashed #e7c840 !important;
            background: #fdfdfd !important;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dropzone-custom:hover {
            background: #fff9e6 !important;
            border-color: #d1b435 !important;
        }
        .dropzone-custom .dz-message i {
            color: #e7c840;
            display: block;
        }
        .dropzone-custom .dz-preview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            z-index: 10;
        }
        .dropzone-custom .dz-preview .dz-image {
            width: 100%;
            height: 100%;
            border-radius: 8px;
        }
        .dropzone-custom .dz-preview .dz-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .dropzone-custom .dz-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 30;
            background: rgba(231, 200, 64, 0.9);
            color: #1a1a1a !important;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-decoration: none !important;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.2s;
            border: 1px solid #d1b435;
        }
        .dropzone-custom .dz-remove:hover {
            background: #ff4d4d;
            color: white !important;
            border-color: #e60000;
            transform: scale(1.1);
        }
        .dropzone-custom .dz-success-mark, .dropzone-custom .dz-error-mark {
            pointer-events: none;
            z-index: 25;
            top: 50% !important;
            left: 50% !important;
            margin-left: -27px !important;
            margin-top: -27px !important;
        }
        .dropzone-custom .dz-success-mark svg fill {
            fill: #e7c840 !important;
        }
    </style>

    @push('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
        <script>
            Dropzone.autoDiscover = false;

            $(document).ready(function() {
                var desktopDz = new Dropzone("#desktop-dropzone", {
                    url: "#",
                    autoProcessQueue: false,
                    maxFiles: 1,
                    addRemoveLinks: true,
                    dictRemoveFile: '<i class="fa fa-times"></i>',
                    acceptedFiles: "image/*",
                    thumbnailWidth: 300,
                    thumbnailHeight: 200,
                    init: function() {
                        this.on("addedfile", function() {
                            if (this.files[1] != null) this.removeFile(this.files[0]);
                        });
                    }
                });

                var mobileDz = new Dropzone("#mobile-dropzone", {
                    url: "#",
                    autoProcessQueue: false,
                    maxFiles: 1,
                    addRemoveLinks: true,
                    dictRemoveFile: '<i class="fa fa-times"></i>',
                    acceptedFiles: "image/*",
                    thumbnailWidth: 200,
                    thumbnailHeight: 300,
                    init: function() {
                        this.on("addedfile", function() {
                            if (this.files[1] != null) this.removeFile(this.files[0]);
                        });
                    }
                });

                $("#submit-all").on("click", function(e) {
                    e.preventDefault();
                    
                    var formData = new FormData($("#banner-store-form")[0]);
                    
                    if (desktopDz.files.length > 0 && desktopDz.files[0].status) {
                        formData.append("image", desktopDz.files[0]);
                    }
                    
                    if (mobileDz.files.length > 0 && mobileDz.files[0].status) {
                        formData.append("mobile_banner", mobileDz.files[0]);
                    }

                    $.ajax({
                        url: $("#banner-store-form").attr("action"),
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            window.location.href = "{{ route('banners') }}";
                        },
                        error: function(xhr) {
                            var errors = xhr.responseJSON.errors;
                            var errorMsg = "";
                            $.each(errors, function(key, value) {
                                errorMsg += value[0] + "\n";
                            });
                            alert(errorMsg || "Something went wrong!");
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
