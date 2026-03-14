@extends('admin.layout.app')

@section('preload-content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" />
@endsection
<style>
    .row.variations select.form-select {
        border: 1px solid #ccc;
        padding: 5px 10px;
    }
</style>

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
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
            <form class="forms-sample" action="{{ route('UpdateProduct', $product->uuid) }}" method="post"
                enctype="multipart/form-data" id="product-edit-form">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title">{{ __('keywords.Edit') }} {{ __('keywords.Product') }}</h4>
                        </div>
                        <div class="card-body">

                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.Category') }}*</label>
                                        <select id="category" name="parent_cat" class="form-control" required>
                                            <option disabled selected>{{ __('keywords.Select') }}
                                                {{ __('keywords.Category') }}
                                            </option>
                                            @foreach ($category as $categorys)
                                                <option value="{{ $categorys->cat_id }}"
                                                    @if ($categorys->cat_id == $product->cat_id) selected @endif>
                                                    @if ($categorys->level == 1)
                                                        -
                                                    @endif{{ $categorys->title }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4" id="sub_cat_div">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Sub Category</label>

                                        <!-- subcategory dropdown -->
                                        <select id="subcategory" name="cat_id" class="form-control">
                                            <option value="">-- Select Subcategory --</option>
                                        </select>
                                        {{-- <select name="cat_id" class="form-control">
                                            <option disabled selected>{{ __('keywords.Select') }}
                                                {{ __('keywords.Category') }}
                                            </option>
                                            @foreach ($sub_category as $categorys)
                                                <option value="{{ $categorys->cat_id }}"
                                                    @if ($categorys->cat_id == $product->cat_id) selected @endif>
                                                    @if ($categorys->level == 1)
                                                        -
                                                    @endif{{ $categorys->title }}
                                                </option>
                                            @endforeach

                                        </select> --}}
                                    </div>
                                </div>
                                <div class="col-md-4" id="child_cat_div">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Child Category</label>

                                        <!-- subcategory dropdown -->
                                        <select id="child_cat_id" name="child_cat_id" class="form-control">
                                            <option value="">-- Select Child category --</option>
                                        </select>
                                        {{-- <select name="cat_id" class="form-control">
                                            <option disabled selected>{{ __('keywords.Select') }}
                                                {{ __('keywords.Category') }}
                                            </option>
                                            @foreach ($sub_category as $categorys)
                                                <option value="{{ $categorys->cat_id }}"
                                                    @if ($categorys->cat_id == $product->cat_id) selected @endif>
                                                    @if ($categorys->level == 1)
                                                        -
                                                    @endif{{ $categorys->title }}
                                                </option>
                                            @endforeach

                                        </select> --}}
                                    </div>
                                </div>
                                <div class="col-md-3" style="display: none">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Product Type*</label>
                                        <select name="type" class="form-control" disabled required>
                                            <option disabled selected>{{ __('keywords.Select') }}
                                                {{ __('keywords.Type') }}
                                            </option>
                                            <option selected value="Simple"
                                                @if ($product->type == 'Simple') selected @endif>
                                                {{ __('keywords.Simple') }}</option>
                                            <option value="Variable" @if ($product->type == 'Variable') selected @endif>
                                                {{ __('keywords.Variable') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label class="bmd-label-floating">{{ __('keywords.Product') }}
                                                {{ __('keywords.Name') }}*</label>
                                            <input type="text" value="{{ $product->product_name }}" name="product_name"
                                                class="form-control" required>
                                        </div>

                                        @if ($product->type == 'Simple')
                                            <div class="form-group quantity-edit-product-form col-md-4">
                                                <label class="bmd-label-floating">{{ __('keywords.Quantity') }}*</label>
                                                <input type="number" name="quantity" class="form-control"
                                                    value="{{ $product->variation->stock }}" required />
                                            </div>
                                        @endif

                                        <div class="form-group col-md-4">
                                            <label class="bmd-label-floating">{{ __('keywords.Tags') }}</label>
                                            <input type="text" data-role="tagsinput" value="{{ $product->tags }}"
                                                class="form-control" name="tags">
                                        </div>
                                    </div>


                                    <div class="row">


                                        <div class="form-group col-md-3">
                                            <label class="bmd-label-floating">{{ __('keywords.Price') }} (Discounted
                                                Price)*</label>
                                            <input type="text"
                                                value="{{ $product->base_price == 0 ? ' ' : $product->base_price }}"
                                                name="base_price" class="form-control" required>
                                        </div>
                                        {{-- @if ($product->base_mrp != $product->base_price) --}}

                                        <div class="form-group col-md-3">
                                            <label class="bmd-label-floating">{{ __('keywords.MRP') }}
                                                *</label>
                                            <input type="text" value="{{ $product->base_mrp }}" name="base_mrp"
                                                class="form-control">
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group ">
                                                <label class="bmd-label-floating">SKU Code *</label>
                                                <input type="text" value="{{ $product->hsn_number }}" name="hsn_number"
                                                    id="sku_code" class="form-control" readonly
                                                    style="background-color: #e9ecef; cursor: not-allowed;">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group ">
                                                <label class="bmd-label-floating">Tax Type</label>
                                                {{-- <input type="text" value="{{ $product->ean }}" name="ean"
                                                    class="form-control"> --}}
                                                <select id="tax_id" name="tax_id" class="form-control ">
                                                    <option value="">Select Tax Type</option>
                                                    @foreach ($taxList as $tax)
                                                        <option {{ $product->tax_id }} {{ $tax->tax_id }}
                                                            value="{{ $tax->tax_id }}"
                                                            @if ($tax->tax_id == $product->tax_id) selected @endif>
                                                            {{ $tax->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>


                                    </div>
                                    {{-- @endif --}}
                                    <div class="row">




                                    </div>
                                    <div class="row">
                                        {{-- <div class="form-group col-md-3">
                                            <label class="bmd-label-floating">On Sale</label>
                                            <input type="checkbox" name="on_sale" class="form-control"
                                                {{ $product->on_sale ? 'checked' : '' }} required>
                                        </div> --}}

                                        {{-- <div class="col-md-3">
                                            <div class="form-group ">
                                                <label class="bmd-label-floating">EAN</label>
                                                <input type="text" value="{{ $product->ean }}" name="ean"
                                                    class="form-control">
                                            </div>
                                        </div> --}}

                                    </div>
                                </div>
                                {{-- <div class="row"> --}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Short {{ __('keywords.Description') }}</label>
                                        <textarea type="text" id="editor" name="description" class="form-control" required>{{ $product->description }}</textarea>
                                    </div>
                                </div>
                                {{-- </div> --}}
                                {{-- <div class="row"> --}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('Description') }}</label>
                                        <textarea type="text" id="editor_ad_info" height="200px" name="info" class="form-control" required>{{ $product->info }}</textarea>
                                    </div>
                                </div>
                                {{-- </div>
                                <div class="row"> --}}
                                {{-- <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('Shipping & Returns') }}</label>
                                        <textarea type="text" name="shipping" class="form-control" required>{{ $product->shipping }}</textarea>
                                    </div>
                                </div> --}}
                                {{-- </div> --}}
                            </div>
                        </div>
                        <img src="{{ asset('storage/' . $product->product_image) }}" alt="image" name="old_image"
                            style="width:100px;height:100px; border-radius:50%">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form">
                                    <label class="bmd-label-floating">{{ __('keywords.Main') }}
                                        {{ __('keywords.Product') }}
                                        {{ __('keywords.Image') }}
                                        <b>({{ __('keywords.product image size') }})
                                        </b> *</label>
                                    <div class="custom-file" style="margin-top:10px">
                                        <input type="file" class="custom-file-input" id="customFile"
                                            name="product_image" accept="image/*" required />
                                        <label class="custom-file-label"
                                            for="customFile">{{ __('keywords.Choose_File') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>

                        {{-- <video src="{{ asset('storage/' . $product->product_video) }}"
                            class="media-preview mt-2 mr-2 me-2" style="max-width:100px; display:inline-block;"
                            controls></video> --}}
                        {{-- <img src="{{ asset('storage/' . $product->product_image) }}" alt="image" name="old_image"
                                style="width:100px;height:100px; border-radius:50%"> --}}
                        {{-- <div class="row">
                            <div class="col-md-12">
                                <div class="form">
                                    <label class="bmd-label-floating">{{ __('keywords.Product') }}
                                        {{ __('keywords.Video') }}
                                        <b>({{ __('It Should Be Less Then 10 MB') }})</b></label>
                                    <div class="custom-file" style="margin-top:10px">
                                        <input type="file" class="custom-file-input" id="customFile"
                                            name="product_video" accept="image/*" required />
                                        <label class="custom-file-label"
                                            for="customFile">{{ __('keywords.Choose_File') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br> --}}

                        {{--
                        @if ($product->size_guide_images)
                            <img src="{{ asset('storage/' . $product->size_guide_images) }}" alt="image"
                                name="old_image_size_guide_images" style="width:100px;height:100px; border-radius:50%">
                        @else
                            <img src="{{ asset('storage/sizeguide-clothes-en.jpg') }}" alt="image"
                                name="old_image_size_guide_images" style="width:100px;height:100px; border-radius:50%">
                        @endif
                         <div class="row">
                            <div class="col-md-12">
                                <div class="form">
                                    <label class="bmd-label-floating"> Product Size Guide
                                        {{ __('keywords.Image') }}
                                        <b>({{ __('keywords.It Should Be Less Then 1000 KB') }})</b></label>
                                    <div class="custom-file" style="margin-top:10px">
                                        <input type="file" class="custom-file-input" id="customFile"
                                            name="size_guide_images" accept="image/*" required />
                                        <label class="custom-file-label"
                                            for="customFile">{{ __('keywords.Choose_File') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div><br> --}}
                        <div class="row">
                            <div class="col-md-12">
                                @foreach ($images as $im)
                                    <img src="{{ asset('storage/' . $im->image) }}" alt="image"
                                        style="width:50px;height:50px; border-radius:50%;border:2px solid black;float: left;">
                                @endforeach
                            </div><br>
                            <div class="col-md-12"><br>
                                <label class="bmd-label-floating"> Slider Images
                                    <b>({{ __('keywords.Each Should Be Less Then 1000 KB') }} | Dimension : 450 x
                                        600)</b> *</label>
                                <div class="custom-file" style="margin-top:10px">
                                    <input type="file" class="custom-file-input" id="customFile" name="images[]"
                                        accept="image/*" multiple />
                                    <label class="custom-file-label"
                                        for="customFile">{{ __('keywords.Choose_File') }}</label>
                                </div>
                            </div>
                        </div>

                        @if ($product->type == 'Variable')
                            <!-- Replace the variations container with this -->
                            <div class="row variations">
                                <h4 style="display:none" id="product-variation-heading">Product Variations</h4>
                                <div id="variations-container" class="mt-4">
                                    @foreach ($product->variations as $index => $variation)
                                        <div class="variation p-3 border rounded mb-2 existing-variation"
                                            data-index="{{ $variation->uuid }}">
                                            <input type="hidden" name="variations_ids[]"
                                                value="{{ $variation->uuid }}" />
                                            <!-- Loop through attributes -->
                                            @foreach ($attributes as $attribute)
                                                <label>{{ $attribute->name }}</label>
                                                <select
                                                    name="variations[{{ $variation->id }}][attributes][{{ $attribute->id }}]"
                                                    class="form-select attribute-select" required disabled>
                                                    <option value="">Select {{ $attribute->name }}</option>
                                                    @foreach ($attribute->values as $option)
                                                        @php
                                                            $selectedOption = $variation->variation_attributes
                                                                ->where('attribute_id', $option->id)
                                                                ->first()?->attribute_id;
                                                        @endphp
                                                        <option value="{{ $option->id }}"
                                                            {{ $selectedOption == $option->id ? 'selected' : '' }}>
                                                            {{ $option->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endforeach

                                            <!-- MRP -->
                                            <label>MRP</label>
                                            <input type="number" name="variations[{{ $variation->uuid }}][mrp]"
                                                step="0.01" class="form-control" value="{{ $variation->mrp }}"
                                                required>

                                            <!-- Price -->
                                            <label>Price</label>
                                            <input type="number" name="variations[{{ $variation->uuid }}][price]"
                                                step="0.01" class="form-control" value="{{ $variation->price }}"
                                                required>

                                            <!-- Stock -->
                                            <label>Stock</label>
                                            <input type="number" name="variations[{{ $variation->uuid }}][stock]"
                                                class="form-control" value="{{ $variation->stock }}" required>

                                            <!-- Image -->
                                            <label>Current Image</label>
                                            @if ($variation->image)
                                                <img src="{{ asset('storage/' . $variation->image) }}"
                                                    style="width:100px; display:block;" class="mt-2" />
                                            @endif

                                            <label class="mt-2">Update Image (optional)</label>
                                            <input type="file" name="variations[{{ $variation->uuid }}][image]"
                                                class="form-control">

                                            <!-- Remove Variation -->
                                            <button type="button"
                                                class="btn btn-danger mt-2 remove-variation">Remove</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Add this after the variations container -->
                            <button type="button" class="btn btn-info mt-2" id="add-variation-btn"
                                style="display:none;">+
                                Add Variation</button>

                            <div class="row">
                                <div class="size-wrapper">
                                    <!-- Button to Toggle Dropdown -->
                                    <button id="dropdownCheckboxButton" class="btn btn-primary" type="button">Select
                                        Attributes</button>

                                    <!-- Dropdown Container -->
                                    <div id="dropdownDefaultCheckbox"
                                        class="dropdown-menu p-3 shadow-lg bg-white size_arrtributes"
                                        style="display: none;">
                                        <ul class="list-unstyled m-0">
                                            @foreach ($attributes as $attribute)
                                                <li>
                                                    <div class="form-check">
                                                        <input class="form-check-input multi-attr" type="checkbox"
                                                            value="{{ $attribute->id }}"
                                                            data-name="{{ $attribute->name }}"
                                                            data-values='@json($attribute->values)'
                                                            id="checkbox-item-{{ $attribute->id }}">
                                                        <label class="form-check-label"
                                                            for="checkbox-item-{{ $attribute->id }}">
                                                            {{ $attribute->name }}
                                                        </label>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <button type="submit"
                            class="btn btn-primary pull-center mt-4">{{ __('keywords.Submit') }}</button>
                        <a href="{{ route('productlist') }}" class="btn">{{ __('keywords.Close') }}</a>
                        <div class="clearfix"></div>

                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>

    @php
        $catId = 0;

        $childCatId = $product->category->cat_id;
        $subCatId = $product->category?->parent ?? 0;
        $parentCatId = $product->category?->parentObj?->parent ?? 0;

        if ($parentCatId == 0) {
            // $childCatId = $sub_category_id->cat_id;
            $parentCatId = $subCatId;
        }
        if ($parentCatId == 0 && $subCatId == 0) {
            // dd($parentCatId,$subCatId,  $sub_category_id, $product->category?->parentObj, $childCatId,$parentCatId == 0 && $subCatId == 0);

            $parentCatId = $childCatId;
        }

        if ($subCatId == 0) {
            $subCatId = $childCatId;
        }

        // if ($sub_category_id->parent != 0) {
        //     $catId = $sub_category_id->parent;
        //     $parentCatId = $sub_category_id->parent;
        // } else {
        //     $catId = $sub_category_id->cat_id;
        //     $parentCatId = $sub_category_id->cat_id;
        // }

        // dd($childCatId, $subCatId, $parentCatId,$product->category->parentObj);

    @endphp

    @push('scripts')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
        <script>
            ClassicEditor
                .create(document.querySelector('#editor'), {
                    list: {
                        properties: {
                            styles: true,
                            startIndex: true,
                            reversed: true
                        }
                    },
                    toolbar: [
                        'undo', 'redo', '|',
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'indent', 'outdent', '|',
                        'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed'
                    ]
                })
                .then(editor => {
                    console.log('Editor initialized', editor);

                    // Add CSS to ensure bullets are visible
                    const editableElement = editor.ui.view.editable.element;
                    if (editableElement) {
                        editableElement.style.paddingLeft = '2em';
                    }
                })
                .catch(error => {
                    console.error(error);
                });

            ClassicEditor
                .create(document.querySelector('#editor_ad_info'), {
                    list: {
                        properties: {
                            styles: true,
                            startIndex: true,
                            reversed: true
                        }
                    },
                    toolbar: [
                        'undo', 'redo', '|',
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'indent', 'outdent', '|',
                        'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed'
                    ]
                })
                .then(editor => {
                    console.log('Editor initialized', editor);

                    // Add CSS to ensure bullets are visible
                    const editableElement = editor.ui.view.editable.element;
                    if (editableElement) {
                        editableElement.style.paddingLeft = '2em';
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
        {{-- You must include files that have no direct efect on the load of the page and can be loaded meanwhile other tasks can be performed by user --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.js"></script>
        {{-- <script src="{{ url('assets/theme_assets/plugins/bs-custom-file-input/dist/bs-custom-file-input.min.js') }}"></script> --}}
        <script>
            $(document).ready(function() {
                // Form submit validation
                $('form').on('submit', function(e) {
                    let isValid = true;

                    // Check subcategory
                    if (isSubcategoryVisible()) {
                        if ($('#subcategory').val() === '' || $('#subcategory').val() === null) {
                            showError('#subcategory', 'Please select a subcategory');
                            isValid = false;
                        } else {
                            removeError('#subcategory');
                        }
                    }

                    // Check child category
                    if (isChildCategoryVisible()) {
                        if ($('#child_cat_id').val() === '' || $('#child_cat_id').val() === null) {
                            showError('#child_cat_id', 'Please select a child category');
                            isValid = false;
                        } else {
                            removeError('#child_cat_id');
                        }
                    }

                    // COmment for testing and adding data
                    // if (!isValid) {
                    //     e.preventDefault(); // Stop form submission
                    // }
                });

                // Function to check if subcategory is visible
                function isSubcategoryVisible() {
                    return $('#subcategory').is(':visible');
                }

                // Function to check if child category is visible
                function isChildCategoryVisible() {
                    return $('#child_cat_id').is(':visible');
                }

                // Function to show error message
                function showError(elementId, message) {
                    removeError(elementId); // Remove existing error first
                    $(elementId).after(
                        '<span class="error-message" style="color: red; display: block; font-size: 12px; margin-top: 5px;">' +
                        message + '</span>');
                }

                // Function to remove error message
                function removeError(elementId) {
                    $(elementId).next('.error-message').remove();
                }

                // Remove error on change
                $('#subcategory, #child_cat_id').on('change', function() {
                    removeError('#' + $(this).attr('id'));
                });
            });
            $(document).ready(function() {

                $(".custom-file-input").on("change", function() {
                    let files = this.files;
                    let fileInput = $(this);

                    // Remove any existing previews
                    fileInput.nextAll('.media-preview').remove();

                    if (files && files.length > 0) {
                        for (let i = 0; i < files.length; i++) {
                            let file = files[i];
                            let reader = new FileReader();

                            if (file.type.startsWith('image/')) {
                                let imgPreview = $(
                                    '<img class="media-preview mt-2 mr-2 me-2" style="max-width:200px; display:inline-block;">'
                                );
                                reader.onload = function(e) {
                                    imgPreview.attr("src", e.target.result);
                                    fileInput.after(imgPreview);
                                };
                                reader.readAsDataURL(file);
                            } else if (file.type.startsWith('video/')) {
                                let videoPreview = $(
                                    '<video class="media-preview mt-2 mr-2 me-2" style="max-width:100px; display:inline-block;" controls></video>'
                                );
                                reader.onload = function(e) {
                                    videoPreview.attr("src", e.target.result);
                                    fileInput.after(videoPreview);
                                };
                                reader.readAsDataURL(file);
                            }
                        }
                    }
                });


                // Auto-generate SKU if empty when category or product name changes
                let skuTimer = null;
                function fetchSKU() {
                    // Only auto-generate if SKU field is empty
                    if ($('#sku_code').val().length >= 5) return;

                    let catId = $('#category').val();
                    let productName = $('input[name="product_name"]').val();

                    if (!catId || !productName || productName.trim().length < 2) {
                        return;
                    }

                    console.log('Fetching SKU for category:', catId, 'and product name:', productName);

                    clearTimeout(skuTimer);
                    skuTimer = setTimeout(function() {
                        $.ajax({
                            url: "{{ route('generate.sku') }}",
                            type: 'GET',
                            data: {
                                cat_id: catId,
                                product_name: productName
                            },
                            success: function(res) {
                                if (res.sku) {
                                    $('#sku_code').val(res.sku);
                                }
                            },
                            error: function() {
                                console.error('Failed to generate SKU');
                            }
                        });
                    }, 500);
                }

                $('#category').on('change', function() {
                    fetchSKU();
                });

                $('input[name="product_name"]').on('input', function() {
                    console.log('Product name changed:', $(this).val());
                    fetchSKU();
                });

                // Auto-generate SKU on page load if empty
                if ($('#sku_code').val() === '') {
                    fetchSKU();
                }

                var categoryId = @json($parentCatId);
                var subCategoryId = @json($subCatId);
                var childCategoryId = @json($childCatId);

                $('#category').val(categoryId);

                console.log('categoryId', @json($parentCatId), categoryId, subCategoryId, childCategoryId)

                $('#subcategory').html('<option value="">Loading...</option>');

                if (categoryId) {
                    var url = "{{ route('get.subcategories', ':id') }}";
                    url = url.replace(':id', categoryId);

                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(res) {
                            console.log('Select Subcategory', res)
                            if (res.length == 0) {
                                $('#sub_cat_div').hide();
                                return;
                            } else {
                                $('#sub_cat_div').show();
                            }
                            let options = '<option value="">-- Select Subcategory --</option>';
                            $.each(res, function(key, subcategory) {
                                options +=
                                    `<option value="${subcategory.cat_id}">${subcategory.title}</option>`;
                            });
                            $('#subcategory').html(options);
                            console.log('subcategory', res)
                            if (categoryId == subCategoryId) {

                                $('#subcategory').val(childCategoryId);
                            } else {

                                $('#subcategory').val(subCategoryId);

                            }

                        },
                        error: function() {
                            $('#subcategory').html('<option value="">Error loading data</option>');
                        }
                    });
                } else {
                    $('#subcategory').html('<option value="">-- Select Subcategory --</option>');
                }

                if (subCategoryId) {
                    if (categoryId == subCategoryId) {
                        var url = "{{ route('get.subcategories', ':id') }}";
                        url = url.replace(':id', childCategoryId);

                    } else {
                        var url = "{{ route('get.subcategories', ':id') }}";
                        url = url.replace(':id', subCategoryId);
                    }

                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(res) {
                            console.log('Select child_cat_id', res)
                            if (res.length == 0) {
                                $('#child_cat_div').hide();
                                return;
                            } else {
                                $('#child_cat_div').show();
                            }
                            let options = '<option value="">-- Select child_cat_id --</option>';
                            $.each(res, function(key, subcategory) {
                                options +=
                                    `<option value="${subcategory.cat_id}">${subcategory.title}</option>`;
                            });


                            $('#child_cat_id').html(options);
                            console.log('child_cat_id', res, childCategoryId)


                            $('#child_cat_id').val(childCategoryId);


                        },
                        error: function() {
                            $('#child_cat_id').html('<option value="">Error loading data</option>');
                        }
                    });
                } else {
                    $('#child_cat_id').html('<option value="">-- Select Child Category --</option>');
                }





                $('#category').on('change', function() {
                    var categoryId = $(this).val();

                    $('#subcategory').html('<option value="">Loading...</option>');
                    $('#child_cat_div').hide();

                    if (categoryId) {
                        var url = "{{ route('get.subcategories', ':id') }}";
                        url = url.replace(':id', categoryId);

                        $.ajax({
                            url: url,
                            type: 'GET',
                            success: function(res) {
                                 if (res.length == 0) {
                                    $('#sub_cat_div').hide();
                                    return;
                                } else {
                                    $('#sub_cat_div').show();
                                }
                                let options = '<option value="">-- Select Subcategory --</option>';
                                $.each(res, function(key, subcategory) {
                                    options +=
                                        `<option value="${subcategory.cat_id}">${subcategory.title}</option>`;
                                });
                                $('#subcategory').html(options);

                            },
                            error: function() {
                                $('#subcategory').html(
                                    '<option value="">Error loading data</option>');
                            }
                        });
                    } else {
                        $('#subcategory').html('<option value="">-- Select Subcategory --</option>');
                    }
                });

                $('#subcategory').on('change', function() {
                    var categoryId = $(this).val();

                    $('#child_cat_id').html('<option value="">Loading...</option>');

                    if (categoryId) {
                        var url = "{{ route('get.subcategories', ':id') }}";
                        url = url.replace(':id', categoryId);

                        $.ajax({
                            url: url,
                            type: 'GET',
                            success: function(res) {
                                if (res.length == 0) {
                                    $('#child_cat_div').hide();
                                    return;
                                } else {
                                    $('#child_cat_div').show();
                                }
                                let options = '<option value="">-- Select Subcategory --</option>';
                                $.each(res, function(key, subcategory) {
                                    options +=
                                        `<option value="${subcategory.cat_id}">${subcategory.title}</option>`;
                                });
                                $('#child_cat_id').html(options);
                            },
                            error: function() {
                                $('#child_cat_id').html(
                                    '<option value="">Error loading data</option>');
                            }
                        });
                    } else {
                        $('#child_cat_id').html('<option value="">-- Select Child Category --</option>');
                    }
                });



                var validator = $("#product-edit-form").validate({
                    ignore: [],
                    rules: {
                        parent_cat: {
                            required: true
                        },
                        product_name: {
                            required: true
                        },
                        type: {
                            required: true
                        },
                        quantity: {
                            required: true,
                            digits: true
                        },
                        ean: {
                            required: false
                        },
                        type: {
                            required: true,
                        },
                        mrp: {
                            required: true,
                            number: true
                        },
                        price: {
                            required: true,
                            number: true
                        },
                        description: {
                            required: true
                        },
                        // product_image: {
                        //     required: true,
                        //     accept: "image/*"
                        // }
                        // Other validation rules remain the same
                    },
                    messages: {
                        parent_cat: "Category Field Is Required",
                        product_name: "Product name is required",
                        quantity: "Enter a valid quantity",
                        ean: "SKU code is required",
                        mrp: "Enter a valid MRP",
                        price: "Enter a valid price",
                        description: "Description is required",
                        product_image: "Please select a valid image"
                    },
                    errorPlacement: function(error, element) {
                        error.insertAfter(element);
                    }
                });

                // Initialize variables
                let variationsContainer = $("#variations-container");
                let addVariationBtn = $("#add-variation-btn");
                let selectedAttributes = {};
                let variationCount = variationsContainer.find('.variation').length;

                // Handle removing variations
                $(document).on('click', '.remove-variation', function() {
                    const variationDiv = $(this).closest('.variation');
                    const variationIndex = variationDiv.data('index');
                    const updateForm = $("#product-edit-form");
                    // If it's an existing variation, add a hidden input to track deletion
                    if (variationDiv.hasClass('existing-variation')) {
                        variationsContainer.append(
                            `<input type="hidden" name="deleted_variations[]" value="${variationIndex}">`
                        );
                        // updateForm.append(
                        //     `<input type="hidden" name="deleted_variations[]" value="${variationIndex}">`
                        // );
                    }

                    variationDiv.remove();
                });

                // Attribute checkbox change handler
                $(".multi-attr").change(function() {
                    let attrId = $(this).val();
                    let attrName = $(this).data("name");
                    let attrValues = $(this).data("values");

                    if ($(this).is(":checked")) {
                        selectedAttributes[attrId] = {
                            name: attrName,
                            values: attrValues
                        };
                    } else {
                        // Check if any existing variations use this attribute
                        let attributeInUse = false;
                        variationsContainer.find('.variation').each(function() {
                            if ($(this).find(`select[name*="attributes"][name*="${attrId}"]`).length >
                                0) {
                                attributeInUse = true;
                                return false; // Break the loop
                            }
                        });

                        if (attributeInUse) {
                            alert("This attribute is used in existing variations and cannot be removed.");
                            $(this).prop('checked', true);
                            return;
                        }

                        delete selectedAttributes[attrId];
                    }

                    // Only show Add Variation button if we have attributes selected
                    if (Object.keys(selectedAttributes).length > 0) {
                        addVariationBtn.show();
                    } else {
                        addVariationBtn.hide();
                    }
                });

                // Add a new variation
                function addVariationForm() {
                    variationCount++;
                    let variationDiv = $('<div class="variation p-3 border rounded mb-2 new-variation"></div>');
                    variationDiv.attr('data-index', `new_${variationCount}`);

                    // Add attribute selects
                    $.each(selectedAttributes, function(attrId, attr) {
                        let label = $('<label></label>').text(attr.name);
                        let select = $('<select class="form-select attribute-select" required></select>')
                            .attr("name", `variations[new_${variationCount}][attributes][${attrId}]`);

                        select.append('<option value="">Select an option</option>');
                        $.each(attr.values, function(index, value) {
                            select.append($('<option></option>').val(value.id).text(value.name));
                        });

                        variationDiv.append(label, select);
                    });

                    // Add MRP, Price, Stock inputs
                    let mrpLabel = $('<br><label>MRP</label>');
                    let mrpInput = $('<input type="number" step="0.01" class="form-control mrp-input" required>')
                        .attr("name", `variations[new_${variationCount}][mrp]`);

                    let priceLabel = $('<label>Price</label>');
                    let priceInput = $('<input type="number" step="0.01" class="form-control price-input" required>')
                        .attr("name", `variations[new_${variationCount}][price]`);

                    let stockLabel = $('<label>Stock</label>');
                    let stockInput = $('<input type="number" class="form-control stock-input" required>')
                        .attr("name", `variations[new_${variationCount}][stock]`);

                    // Image section
                    let imageLabel = $('<label>Upload Image</label>');
                    let imageInput = $('<input type="file" class="form-control image-input" accept="image/*" required>')
                        .attr("name", `variations[new_${variationCount}][image]`);

                    let imgPreview = $('<img class="img-preview mt-2" style="max-width:100px; display:none;">');

                    imageInput.change(function() {
                        let file = this.files[0];
                        if (file) {
                            let reader = new FileReader();
                            reader.onload = function(e) {
                                imgPreview.attr("src", e.target.result).show();
                            };
                            reader.readAsDataURL(file);
                        } else {
                            imgPreview.hide();
                        }
                    });

                    // Remove button
                    let removeBtn = $(
                        '<button type="button" class="btn btn-danger mt-2 remove-variation">Remove</button>');

                    variationDiv.append(
                        mrpLabel, mrpInput,
                        priceLabel, priceInput,
                        stockLabel, stockInput,
                        imageLabel, imageInput,
                        imgPreview, removeBtn
                    );

                    variationsContainer.append(variationDiv);

                    // Add validation rules
                    addValidationToInputs(mrpInput, priceInput, stockInput, imageInput);

                    // Add validation to select fields
                    variationDiv.find(".attribute-select").each(function() {
                        $(this).rules("add", {
                            required: true,
                            messages: {
                                required: "Please select an option"
                            }
                        });
                    });
                }

                function addValidationToInputs(mrpInput, priceInput, stockInput, imageInput = null) {
                    mrpInput.rules("add", {
                        required: true,
                        number: true,
                        messages: {
                            required: "MRP is required",
                            number: "Enter a valid number"
                        }
                    });

                    priceInput.rules("add", {
                        required: true,
                        number: true,
                        messages: {
                            required: "Price is required",
                            number: "Enter a valid number"
                        }
                    });

                    stockInput.rules("add", {
                        required: true,
                        digits: true,
                        messages: {
                            required: "Stock is required",
                            digits: "Enter a valid quantity"
                        }
                    });

                    if (imageInput) {
                        imageInput.rules("add", {
                            required: true,
                            accept: "image/*",
                            messages: {
                                required: "Image is required",
                                accept: "Please select a valid image"
                            }
                        });
                    }

                    // Price cannot be higher than MRP validation
                    priceInput.on('input', function() {
                        const mrpVal = parseFloat(mrpInput.val());
                        const priceVal = parseFloat(priceInput.val());
                        if (!isNaN(mrpVal) && !isNaN(priceVal) && priceVal > mrpVal) {
                            priceInput[0].setCustomValidity("Price must be less than or equal to MRP");
                        } else {
                            priceInput[0].setCustomValidity("");
                        }
                    });

                    mrpInput.on('input', function() {
                        const mrpVal = parseFloat(mrpInput.val());
                        const priceVal = parseFloat(priceInput.val());
                        if (!isNaN(mrpVal) && !isNaN(priceVal) && priceVal > mrpVal) {
                            priceInput[0].setCustomValidity("Price must be less than or equal to MRP");
                        } else {
                            priceInput[0].setCustomValidity("");
                        }
                    });
                }

                // Add variation button click handler
                $("#add-variation-btn").click(function() {
                    addVariationForm();
                });

                // Validate dropdowns on change
                $(document).on("change", ".attribute-select", function() {
                    validator.element($(this));
                });

                // Form submission validation
                $("#product-edit-form").submit(function(event) {
                    if (!validator.form()) {
                        event.preventDefault();
                    }
                });

                // Dropdown Toggle for attributes selection
                $("#dropdownCheckboxButton").click(function(event) {
                    event.stopPropagation();
                    $("#dropdownDefaultCheckbox").toggle();
                });

                // Close dropdown when clicking outside
                $(document).click(function(event) {
                    if (!$(event.target).closest("#dropdownCheckboxButton, #dropdownDefaultCheckbox").length) {
                        $("#dropdownDefaultCheckbox").hide();
                    }
                });

                // Toggle product type sections
                function toggleProductTypeSections(type) {
                    if (type === "Variable") {
                        $("#dropdownCheckboxButton").show();

                        // Check if we have attributes selected or existing variations
                        if (Object.keys(selectedAttributes).length > 0 || variationsContainer.find('.variation')
                            .length > 0) {
                            addVariationBtn.show();
                        }
                    } else {
                        $("#dropdownCheckboxButton").hide();
                        $("#dropdownDefaultCheckbox").hide();

                        // If changing from Variable to Simple, ask for confirmation if variations exist
                        if (variationsContainer.find('.variation').length > 0) {
                            if (confirm("Changing to Simple product will remove all variations. Are you sure?")) {
                                variationsContainer.html("");
                                $(".multi-attr").prop("checked", false);
                                selectedAttributes = {};
                            } else {
                                $("select[name='type']").val("Variable");
                                return;
                            }
                        }

                        addVariationBtn.hide();
                    }
                }

                // Product type change handler
                $("select[name='type']").on("change", function() {
                    var type = $(this).val();
                    if (type == 'Variable') {
                        $('.quantity-edit-product-form').css({
                            'display': 'none'
                        });
                    } else {
                        $('.quantity-edit-product-form').css({
                            'display': 'block'
                        });
                    }
                    toggleProductTypeSections($(this).val());
                });

                // Initialize product type sections
                toggleProductTypeSections($("select[name='type']").val());

                // Pre-select attributes for existing variations
                variationsContainer.find('.variation').each(function() {
                    $(this).find('select[name*="attributes"]').each(function() {
                        // Extract attribute ID from the name
                        const nameAttr = $(this).attr('name');
                        const matches = nameAttr.match(/\[attributes\]\[(\d+)\]/);

                        if (matches && matches[1]) {
                            const attrId = matches[1];
                            const attrCheckbox = $(`#checkbox-item-${attrId}`);

                            if (attrCheckbox.length && !attrCheckbox.prop('checked')) {
                                attrCheckbox.prop('checked', true);

                                // Add to selectedAttributes
                                selectedAttributes[attrId] = {
                                    name: attrCheckbox.data('name'),
                                    values: attrCheckbox.data('values')
                                };
                            }
                        }
                    });
                });

                // Initialize custom file input
                //bsCustomFileInput.init();
            });
        </script>
    @endpush
@endsection
