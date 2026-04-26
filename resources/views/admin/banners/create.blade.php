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

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="bmd-label-floating d-block mb-2">Banner Visibility</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_visible" name="is_visible" value="1" checked>
                                        <label class="custom-control-label" for="is_visible">Visible on website</label>
                                    </div>
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
        /* ── Dropzone Container ── */
        .dropzone-custom {
            border: 2px dashed #e7c840 !important;
            background: #fdfdfd !important;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;          /* keeps everything inside the box */
        }
        .dropzone-custom:hover {
            background: #fff9e6 !important;
            border-color: #d1b435 !important;
            box-shadow: 0 0 0 3px rgba(231, 200, 64, 0.15);
        }

        /* ── Upload prompt icon ── */
        .dropzone-custom .dz-message i {
            color: #e7c840;
            display: block;
        }
        .dropzone-custom .dz-message h5 {
            margin-top: 8px;
            font-weight: 600;
        }

        /* ── Preview tile – fill the whole dropzone ── */
        .dropzone-custom .dz-preview {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            margin: 0 !important;
            padding: 0 !important;
            min-height: 0 !important;
            z-index: 10;
        }
        .dropzone-custom .dz-preview .dz-image {
            width: 100% !important;
            height: 100% !important;
            border-radius: 10px;
            overflow: hidden;
        }
        .dropzone-custom .dz-preview .dz-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* ── Hide default Dropzone extras that break the layout ── */
        .dropzone-custom .dz-preview .dz-details,
        .dropzone-custom .dz-preview .dz-filename,
        .dropzone-custom .dz-preview .dz-size,
        .dropzone-custom .dz-preview .dz-error-message,
        .dropzone-custom .dz-preview .dz-progress {
            display: none !important;
        }

        /* ── Remove ✕ button – always inside the box ── */
        .dropzone-custom .dz-remove {
            position: absolute;
            top: 8px; right: 8px;
            z-index: 30;
            background: rgba(26, 26, 26, 0.70);
            color: #fff !important;
            width: 28px; height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-decoration: none !important;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.2s;
            border: 2px solid rgba(255,255,255,0.3);
            line-height: 1;
            padding: 0;
        }
        .dropzone-custom .dz-remove:hover {
            background: #ff4d4d;
            color: #fff !important;
            border-color: #ff4d4d;
            transform: scale(1.15);
        }

        /* ── Success / Error marks – centered ── */
        .dropzone-custom .dz-success-mark,
        .dropzone-custom .dz-error-mark {
            display: none !important;
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
                    thumbnailWidth: null,
                    thumbnailHeight: null,
                    init: function() {
                        this.on("addedfile", function() {
                            $("#desktop-dropzone .dz-message").hide();
                            if (this.files[1] != null) this.removeFile(this.files[0]);
                        });
                        this.on("removedfile", function() {
                            if (this.files.length === 0) $("#desktop-dropzone .dz-message").show();
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
                    thumbnailWidth: null,
                    thumbnailHeight: null,
                    init: function() {
                        this.on("addedfile", function() {
                            $("#mobile-dropzone .dz-message").hide();
                            if (this.files[1] != null) this.removeFile(this.files[0]);
                        });
                        this.on("removedfile", function() {
                            if (this.files.length === 0) $("#mobile-dropzone .dz-message").show();
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
