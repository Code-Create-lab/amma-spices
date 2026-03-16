@extends('admin.layout.app')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" />
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">{{ __('keywords.Add Product') }}</h4>
                    </div>
                    <div class="card-body">
                        <form class="forms-sample" action="{{ route('AddNewProduct') }}" method="post"
                            enctype="multipart/form-data" id="product-store-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.Category') }} *</label>
                                        <select id="category" name="parent_cat" class="form-control" required>
                                            <option disabled selected>{{ __('keywords.Select') }}
                                                {{ __('keywords.Category') }}
                                            </option>
                                            @foreach ($category as $categorys)
                                                <option value="{{ $categorys->cat_id }}">
                                                    @if ($categorys->level == 1)
                                                        -
                                                        @endif @if ($categorys->level == 2)
                                                            --
                                                        @endif {{ $categorys->title }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
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
                                <div class="col-md-4" style="display: ">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">Product Type *</label>
                                        <select name="type" class="form-control" required>
                                            <option disabled selected>{{ __('keywords.Select') }}
                                                {{ __('keywords.Type') }}
                                            </option>
                                            <option value="Simple" selected>{{ __('keywords.Simple') }}</option>
                                            <option value="Variable">{{ __('keywords.Variable') }}</option>

                                        </select>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.Product') }}
                                            {{ __('keywords.Name') }} *</label>
                                        <input type="text" name="product_name" class="form-control"
                                            value="{{ old('product_name') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6 quantity-add-product-form">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.Quantity') }} *</label>
                                        <input type="number" name="quantity" class="form-control"
                                            value="{{ old('quantity') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                {{-- <div class="col-md-6">
                              <div class="form-group">
                                  <label class="bmd-label-floating">{{ __('keywords.Unit') }} (G/KG/Ltrs/Ml)</label>
                                  <input type="text" name="unit" class="form-control" pattern="[A-Za-z]{1-10}"
                                      title="KG/G/Ltrs/Ml etc" value="{{ old('unit') }}" required>
                              </div>
                          </div> --}}
                                {{-- <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.EAN Code') }}</label>
                                        <input type="text" name="ean" class="form-control"
                                            value="{{ old('ean') }}" required>
                                    </div>
                                </div> --}}
                                {{-- <div class="col-md-3">
                                    <div class="form-group on-sale">
                                        <label class="bmd-label-floating">On Sale</label>
                                        <input type="checkbox" name="on_sale" class="form-control"
                                            value="{{ old('ean') }}" required>
                                    </div>
                                </div> --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.MRP') }} *</label>
                                        <input type="number" step="0.01" name="mrp" class="form-control"
                                            value="{{ old('mrp') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.Price') }} (Discounted Price)
                                            *</label>
                                        <input type="number" step="0.01" name="price" class="form-control"
                                            value="{{ old('price') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group ">
                                        <label class="bmd-label-floating">SKU Code *</label>
                                        <input type="text" value="{{ old('hsn_number') }}" name="hsn_number"
                                            id="sku_code" class="form-control" readonly
                                            style="background-color: #e9ecef; cursor: not-allowed;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group ">
                                        <label class="bmd-label-floating">Tax Type *</label>
                                        {{-- <input type="text" value="{{ $product->ean }}" name="ean"
                                                    class="form-control"> --}}
                                        <select id="subcategory" name="tax_id" class="form-control">
                                            <option value="">Select Tax Type</option>
                                            @foreach ($taxList as $tax)
                                                <option value="{{ $tax->id }}"
                                                    @if ($tax->id == old('tax_id')) selected @endif>
                                                    {{ $tax->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                            {{-- <div class="row">
                               
                            </div> --}}
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.Tags') }}</label>
                                        <input type="text" data-role="tagsinput" class="form-control" name="tags"
                                            value="{{ old('tags') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="bmd-label-floating"> Short {{ __('keywords.Description') }} *
                                        </label>
                                        <textarea type="text" id="editor" name="description" class="form-control" required>{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.Description') }}</label>
                                        <textarea type="text" id="editor_ad_info" name="info" class="form-control">{{ old('info') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('Shipping & Returns') }}</label>
                                        <textarea type="text" name="shipping" class="form-control">{{ old('shipping') }}</textarea>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="bmd-label-floating">{{ __('keywords.Main') }}
                                        {{ __('keywords.Product') }}
                                        {{ __('keywords.Image') }}
                                        <b>({{ __('keywords.product image size') }})
                                        </b> *</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="product_image"
                                            accept="image/*" required />
                                        <label class="custom-file-label"
                                            for="customFile">{{ __('keywords.Choose_File') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="bmd-label-floating"> {{ __('keywords.Product') }}
                                        Slider
                                        {{ __('keywords.Images') }}
                                        <b>({{ __('keywords.Each Should Be Less Then 1000 KB') }} | Dimension : 450 x
                                            600)</b>*</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="images[]" accept="image/*"
                                            multiple />
                                        <label class="custom-file-label"
                                            for="customFile">{{ __('keywords.Choose_File') }}</label>
                                    </div>
                                </div>
                            </div>


                            {{-- <div class="row">
                                <label class="bmd-label-floating"> Product Size Guide
                                    {{ __('keywords.Images') }}
                                    <b>({{ __('keywords.Each Should Be Less Then 1000 KB') }})</b></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input id="sizeGuide"
                                        name="size_guide_images" accept="image/*" multiple />
                                    <label class="custom-file-label"
                                        for="sizeGuide">{{ __('keywords.Choose_File') }}</label>
                                </div>
                            </div> --}}

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

                            <div class="row variations">
                                <h4 style="display:none" id="product-variation-heading">Product Variations</h4>
                                <div id="variations-container"></div>
                                <button id="add-variation-btn" type="button" class="btn btn-success mt-2"
                                    style="display: none;">Add
                                    Variation</button>
                            </div>

                            <div class="row">
                                <div class="submit_button_admin">
                                    <button type="submit"
                                        class="btn btn-primary pull-center">{{ __('keywords.Submit') }}</button>
                                    <a href="{{ route('productlist') }}"
                                        class="btn btn-success">{{ __('keywords.Close') }}</a>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @php
        $catId = 0;

    @endphp

    @push('scripts')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
        <script>
            ClassicEditor
                .create(document.querySelector('#editor'))
                .then(editor => {
                    console.log('Editor initialized', editor);
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#editor_ad_info'))
                .then(editor => {
                    console.log('Editor initialized', editor);
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.js"></script>
        <script>
            $(document).ready(function() {

                // Auto-generate SKU when category is selected and product name is entered
                let skuTimer = null;
                function fetchSKU() {
                    let catId = $('#category').val();
                    let productName = $('input[name="product_name"]').val();

                    if (!catId || !productName || productName.trim().length < 2) {
                        $('#sku_code').val('');
                        return;
                    }

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
                    fetchSKU();
                });

                $(".custom-file-input").on("change", function() {
                    let files = this.files;
                    let fileInput = $(this);

                    // Remove any existing previews for this specific input
                    fileInput.nextAll('.img-preview').remove();

                    console.log("files", files);

                    if (files && files.length > 0) {
                        // Loop through all selected files
                        for (let i = 0; i < files.length; i++) {
                            let file = files[i];

                            // Only process image files
                            if (file.type.startsWith('image/')) {
                                let imgPreview = $(
                                    '<img class="img-preview mt-2 mr-2 me-2" style="max-width:100px; display:inline-block;">'
                                );

                                let reader = new FileReader();
                                reader.onload = function(e) {
                                    imgPreview.attr("src", e.target.result);
                                    fileInput.after(imgPreview); // Insert right after the file input
                                };
                                reader.readAsDataURL(file);
                            }
                        }
                    }
                });




                $('#category').on('change', function() {
                    var categoryId = $(this).val();

                    $('#subcategory').html('<option value="">Loading...</option>');

                    if (categoryId) {
                        var url = "{{ route('get.subcategories', ':id') }}";
                        url = url.replace(':id', categoryId);

                        $.ajax({
                            url: url,
                            type: 'GET',
                            success: function(res) {
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






                $.validator.addMethod("maxfiles", function(value, element, param) {
                    if (element.files && element.files.length > param) {
                        return false;
                    }
                    return true;
                }, "You can upload a maximum of {0} images.");

                var validator = $("#product-store-form").validate({
                    ignore: [],
                    rules: {
                        parent_cat: {
                            required: true
                        },
                        product_name: {
                            required: true
                        },
                        quantity: {
                            required: true,
                            digits: true
                        },
                        //ean: { required: false },
                        type: {
                            required: true
                        },
                        mrp: {
                            required: true,
                            number: true
                        },
                        //  price: { required: true, number: true },
                        description: {
                            required: true
                        },
                        product_image: {
                            required: true,
                            //customAccept: true  // Use custom method instead of accept
                        },
                        'images[]': {
                            required: true,
                            // customAccept: true,  // Use custom method instead of accept
                            maxfiles: 4
                        }
                    },
                    messages: {
                        parent_cat: "Parent Category Field Is Required",
                        product_name: "Product name is required",
                        quantity: "Enter a valid quantity",
                        mrp: "Enter a valid MRP",
                        //   price: "Enter a valid price",
                        description: "Description is required",
                        product_image: {
                            required: "Please select an image",
                            customAccept: "Please select a valid image file"
                        },
                        'images[]': {
                            required: "Please upload at least one image",
                            customAccept: "Only image files are allowed",
                            maxfiles: "You can upload a maximum of 4 images"
                        }
                    },
                    errorPlacement: function(error, element) {
                        error.insertAfter(element);
                    }
                });

                let variationsContainer = $("#variations-container");
                let addVariationBtn = $("#add-variation-btn");
                let selectedAttributes = {};
                let variationCount = 0;

                $(".multi-attr").change(function() {
                    let attrId = $(this).val();
                    let attrName = $(this).data("name");
                    let attrValues = $(this).data("values");

                    if (!Array.isArray(attrValues)) {
                        attrValues = [];
                    }

                    if ($(this).is(":checked")) {
                        selectedAttributes[attrId] = {
                            name: attrName,
                            values: attrValues
                        };
                    } else {
                        delete selectedAttributes[attrId];
                    }
                    updateVariationsForm();
                });

                function updateVariationsForm() {
                    variationsContainer.html("");
                    if (Object.keys(selectedAttributes).length > 0) {
                        addVariationForm();
                        addVariationBtn.show();
                    } else {
                        addVariationBtn.hide();
                    }
                }

                function addVariationForm() {
                    $('#product-variation-heading').show();
                    variationCount++;
                    let variationDiv = $('<div class="variation p-3 border rounded mb-2"></div>');

                    $.each(selectedAttributes, function(attrId, attr) {
                        let label = $('<label></label>').text(attr.name);
                        let select = $('<select class="form-select attribute-select" required></select>')
                            .attr("name", `variations[${variationCount}][attributes][${attrId}]`);

                        select.append('<option value="">Select an option</option>');
                        $.each(attr.values, function(index, value) {
                            select.append($('<option></option>').val(value.id).text(value.name));
                        });

                        variationDiv.append(label, select);
                    });

                    let mrpLabel = $('<label>MRP *</label>');
                    let mrpInput = $('<input type="number" class="form-control mrp-input" required>')
                        .attr("name", `variations[${variationCount}][mrp]`);

                    let priceLabel = $('<label>Price</label>');
                    let priceInput = $('<input type="number" class="form-control price-input" required>')
                        .attr("name", `variations[${variationCount}][price]`);

                    let stockLabel = $('<label>Stock * </label>');
                    let stockInput = $('<input type="number" class="form-control stock-input" required>')
                        .attr("name", `variations[${variationCount}][stock]`);

                    let imageLabel = $('<label>Upload Image <b> Dimension : 450 x 600</b> </label>');
                    let imageInput = $('<input type="file" class="form-control image-input" required accept="image/*">')
                        .attr("name", `variations[${variationCount}][image]`);

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

                    let removeBtn = $('<button type="button" class="btn btn-danger mt-2">Remove</button>');
                    removeBtn.click(function() {
                        variationDiv.remove();
                    });

                    variationDiv.append(mrpLabel, mrpInput, priceLabel, priceInput, stockLabel, stockInput, imageLabel,
                        imageInput,
                        imgPreview, removeBtn);
                    variationsContainer.append(variationDiv);

                    mrpInput.rules("add", {
                        required: true,
                        number: true,
                        messages: {
                            required: "MRP is required",
                            number: "Enter a valid number"
                        }
                    });

                    // ✅ Add validation dynamically AFTER appending
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

                    imageInput.rules("add", {
                        required: true,
                        accept: "image/*",
                        messages: {
                            required: "Image is required",
                            accept: "Please select a valid image"
                        }
                    });

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

                    // ✅ Apply validation to all select fields
                    $(".attribute-select").each(function() {
                        $(this).rules("add", {
                            required: true,
                            messages: {
                                required: ""
                            }
                        });
                    });

                    // Validate new inputs
                    validator.element(mrpInput);
                    validator.element(priceInput);
                    validator.element(stockInput);
                    validator.element(imageInput);
                    $(".attribute-select").each(function() {
                        validator.element($(this));
                    });
                }

                addVariationBtn.click(function() {
                    addVariationForm();
                });

                // ✅ Validate dropdowns on change
                $(document).on("change", ".attribute-select", function() {
                    validator.element($(this));
                });

                $("#product-store-form").submit(function(event) {
                    if (!validator.form()) {
                        event.preventDefault();
                    }
                });

                // ✅ Fix: Dropdown Toggle
                $("#dropdownCheckboxButton").click(function(event) {
                    event.stopPropagation(); // Prevent closing when clicking button
                    $("#dropdownDefaultCheckbox").toggle();
                });

                // ✅ Fix: Close dropdown only if clicking outside
                $(document).click(function(event) {
                    if (!$(event.target).closest("#dropdownCheckboxButton, #dropdownDefaultCheckbox").length) {
                        $("#dropdownDefaultCheckbox").hide();
                    }
                });

                function toggleProductTypeSections(type) {
                    if (type === "Variable") {
                        $("#dropdownCheckboxButton").show();
                        if (Object.keys(selectedAttributes).length > 0) {
                            $("#add-variation-btn").show();
                        }
                    } else {
                        $("#dropdownCheckboxButton").hide();
                        $("#dropdownDefaultCheckbox").hide(); // Also hide dropdown if it was open
                        $("#variations-container").html("");
                        $("#add-variation-btn").hide();
                        selectedAttributes = {}; // Reset attribute selections
                        $(".multi-attr").prop("checked", false); // Uncheck all checkboxes
                    }
                }

                $("select[name='type']").on("change", function() {
                    toggleProductTypeSections($(this).val());
                });

                $("select[name='type']").on("change", function() {
                    var type = $(this).val();
                    if (type == 'Variable') {
                        $('.quantity-add-product-form').css({
                            'display': 'none'
                        });
                    } else {
                        $('.quantity-add-product-form').css({
                            'display': 'block'
                        });
                    }
                    toggleProductTypeSections($(this).val());
                });

                // Call it initially in case of old form value (like on validation fail)
                toggleProductTypeSections($("select[name='type']").val());
            });
        </script>
        {!! JsValidator::formRequest('App\Http\Requests\Admin\Product\ProductStoreRequest', '#product-store-form') !!}
    @endpush
@endsection
