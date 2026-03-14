<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Product\ProductStoreRequest;
use App\Models\Attribute;
use App\Models\ProductImage;
use App\Models\ProductVarient;
use App\Models\Variation;
use App\Models\VariationAttribute;
use App\Traits\ImageStoragePicker;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Session;
use Auth;
use Illuminate\Support\Facades\Storage;
use App\Setting;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    use ImageStoragePicker;

    public function list(Request $request)
    {
        $title = "Product List";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        // If it's a DataTables AJAX request
        if ($request->ajax()) {
            return $this->getProductsDataTable($request);
        }

        // Get categories for filter dropdown
        $categories = DB::table('categories')
            ->where('is_deleted', 0)
            ->orderBy('title', 'asc')
            ->get();

        // Get unique product types for filter dropdown
        $productTypes = DB::table('products')
            ->where('approved', 1)
            ->where('is_deleted', 0)
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort();

        $url_aws = $this->getImageStorage();

        return view('admin.product.list', compact('title', "admin", "logo", "categories", "productTypes", "url_aws"));
    }

 

    private function generateSKU(string $productName, string $categoryName): string
    {
        $categoryCode = $this->extractCode($categoryName, 3);
        $productCode  = $this->extractCode($productName, 5);

        $maxAttempts = 10;
        $attempts    = 0;

        do {
            if ($attempts >= $maxAttempts) {
                // fallback to full random to guarantee uniqueness
                $sku = strtoupper(Str::random(12));
                if (!Product::where('hsn_number', $sku)->exists()) {
                    return $sku;
                }
                continue;
            }

            $sequence = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $sku      = "{$categoryCode}-{$productCode}-{$sequence}";
            $attempts++;
        } while (Product::where('hsn_number', $sku)->exists());

        return $sku;
    }

    /**
     * Extract a clean, padded, uppercase alpha code from a string.
     */
    private function extractCode(string $input, int $length): string
    {
        if (empty(trim($input))) {
            return str_repeat('X', $length);
        }

        // Strip everything except letters, keep spaces to handle multi-word names
        $words = preg_split('/\s+/', trim($input));

        $code = '';

        // First, try initials if multi-word (e.g. "Soft Drinks" -> "SD")
        if (count($words) >= $length) {
            foreach ($words as $word) {
                $letter = preg_replace('/[^A-Za-z]/', '', $word);
                if ($letter !== '') {
                    $code .= strtoupper($letter[0]);
                }
            }
        }

        // If initials aren't enough, fill with letters from the full string
        if (strlen($code) < $length) {
            $allLetters = strtoupper(preg_replace('/[^A-Za-z]/', '', implode('', $words)));
            $code       = substr($code . $allLetters, 0, $length);
        }

        // Pad with 'X' if still too short (e.g. very short names like "Go")
        return str_pad(strtoupper(substr($code, 0, $length)), $length, 'X');
    }

    public function getAutoSKU(Request $request)
    {
        $productName = $request->input('product_name', '');
        $categoryId = $request->input('cat_id', '');

        $categoryName = '';
        if ($categoryId) {
            $category = DB::table('categories')->where('cat_id', $categoryId)->first();
            $categoryName = $category->title ?? '';
        }

        $sku = $this->generateSKU($productName, $categoryName);

        return response()->json(['sku' => $sku]);
    }

    public function AddProduct(Request $request)
    {

        //  dd($request->all());

        $title = "Add Product";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $cat = DB::table('categories')
            ->select('parent')
            ->where('is_deleted', 0)
            ->get();

        if (count($cat) > 0) {
            foreach ($cat as $cats) {
                $a = $cats->parent;
                $aa[] = array($a);
            }
        } else {
            $a = 0;
            $aa[] = array($a);
        }

        $category = DB::table('categories')
            ->where('is_deleted', 0)
            ->where('level',  0)
            // ->WhereNotIn('cat_id', $aa)
            ->get();

        $url_aws = $this->getImageStorage();
        $attributes = Attribute::with('values')->where('is_deleted', 0)->get();
        $taxList = Tax::all();
        return view('admin.product.add', [
            'category' => $category,
            'admin_email' => $admin_email,
            'logo' => $logo,
            'admin' => $admin,
            'title' => $title,
            'url_aws' => $url_aws,
            'attributes' => $attributes,
            'taxList' => $taxList
        ]);
    }

    public function AddNewProduct(ProductStoreRequest $request)
    {

        //  dd($request->all());
        // Prevent operation in demo mode
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }


        // try {
        DB::beginTransaction();
        // Retrieve files (if any)
        $images = $request->file('images');
        $size_guide_images = $request->file('size_guide_images');
        // Prepare image storage configuration (assumes $this->storage_space is set in getImageStorage)
        $this->getImageStorage();
        // Define a date string for folder naming
        $date = date('d-m-Y');
        // Prepare product data

        $tags = $request->tags;

        if ($request->on_sale == 'on') {

            $onSale = true;
        } else {

            $onSale = false;
        }

        // if ($request->price == 0 || $request->price == null || $request->price == "") {

        //     $basePrice =  $request->mrp;
        // } else {
        //     $basePrice =  $request->price;
        // }
        $getTaxId = Tax::pluck('tax_id')->first();
        $product_data = [
            'cat_id' => $request->cat_id ?? $request->parent_cat,
            'product_name' => $request->product_name,
            'type' => $request->type,
            'hide' => 0,
            'added_by' => 0,
            'approved' => 1,
            'product_image' => 'N/A',
            'store_id' => 1,
            'info' => $request->info,
            'shipping' => $request->shipping,
            'ean' => $request->ean,
            'hsn_number' => $request->hsn_number,
            'tax_id' => $request->tax_id ?? $getTaxId,
            'on_sale' => $onSale,
            'tags' => $tags,
            'description' => $request->description,
            'base_mrp' => $request->mrp,
            'base_price' => $request->price,
            'quantity' => $request->type == 'Simple' ? $request->quantity : 0
        ];


        // Process main product image if uploaded
        if ($request->hasFile('product_image')) {
            $product_image = $request->file('product_image');
            $fileName = str_replace(" ", "-", $product_image->getClientOriginalName());
            if ($this->storage_space != "same_server") {
                $filePath = '/product/' . $fileName;
                Storage::disk($this->storage_space)
                    ->put($filePath, fopen($product_image->getRealPath(), 'r+'), 'public');
                $product_data['product_image'] = $filePath;
            } else {
                // $destinationPath = 'images/product/' . $date . '/';
                $filePath = Storage::disk('public')->put('products', $request->file('product_image'));
                // $product_image->move($destinationPath, $fileName);
                // $filePath = '/' . $destinationPath . $fileName;
                $product_data['product_image'] = $filePath;
            }
        }
        // Create product record
        $product = Product::create($product_data);
        if ($request->type == 'Variable') {
            foreach ($request->variations as $variationData) {
                // Store variation
                $imagePath = null;

                // ✅ Check if an image is uploaded for this variation
                if (isset($variationData['image']) && $variationData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $imagePath = Storage::disk('public')->put('variations', $variationData['image']);
                }
                $variation = Variation::create([
                    'product_id' => $product->product_id, // Replace with actual product_id
                    'price' => $variationData['price'] ?? $variationData['mrp'],
                    'stock' => $variationData['stock'],
                    'image' => $imagePath,
                    'mrp' => $variationData['mrp']
                ]);

                // Store variation attributes
                foreach ($variationData['attributes'] as $attributeName => $attributeValueId) {
                    VariationAttribute::create([
                        'variation_id' => $variation->id,
                        'attribute_id' => $attributeValueId
                    ]);
                }
            }
        } else {
            Variation::create([
                'product_id' => $product->product_id,
                'stock' => $request->quantity,
                'price' => $product->base_price,
                'mrp' => $product->base_mrp,
                'image' => $product->image
            ]);
        }

        // Process additional images if provided
        if (!empty($images)) {
            // dd($images);
            foreach ($images as $image) {
                $fileName = str_replace(" ", "-", $image->getClientOriginalName());
                if ($this->storage_space != "same_server") {
                    $filePath1 = '/product/' . $fileName;
                    Storage::disk($this->storage_space)
                        ->put($filePath1, fopen($image->getRealPath(), 'r+'), 'public');
                } else {

                    // $destinationPath = 'images/product/' . $date . '/';
                    // $image->move($destinationPath, $fileName);
                    $filePath = Storage::disk('public')->put('variations', $image);
                }


                ProductImage::create([
                    'type' => 1,
                    'image' => $filePath,
                    'product_id' => $product->product_id
                ]);
            }
        }
        if (!empty($size_guide_images)) {
            // dd($size_guide_images);
            // foreach ($size_guide_images as $image) {
            $fileName = str_replace(" ", "-", $size_guide_images->getClientOriginalName());

            if ($this->storage_space != "same_server") {
                $filePath1 = '/product/' . $fileName;
                Storage::disk($this->storage_space)
                    ->put($filePath1, fopen($size_guide_images->getRealPath(), 'r+'), 'public');
            } else {

                // $destinationPath = 'size_guide_imagess/product/' . $date . '/';
                // $size_guide_images->move($destinationPath, $fileName);
                $filePath = Storage::disk('public')->put('product', $size_guide_images);
            }

            //  dd($fileName, $filePath,$product);
            Product::where('product_id', $product->product_id)->update([
                'size_guide_images' => $filePath
            ]);
            // $productSave

            // ProductImage::create([
            //     'type' => 1,
            //     'image' => $filePath,
            //     'product_id' => $product->product_id
            // ]);
            // }
        }
        DB::commit();
        return redirect()->back()->withSuccess(trans('keywords.Added Successfully'));
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     return redirect()->back()->withErrors($e->getMessage());
        // }
    }
    public function EditProduct(Request $request, $uuid)
    {

        // dd("adsad");
        $title = "Edit Product";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $product = Product::where('uuid', $uuid)
            ->with(['variations.variation_attributes.attribute_options'])
            ->firstOrFail();
        // dd($product->toArray());
        $images = DB::table('product_images')
            ->where('product_id', $product->product_id)
            ->get();

        $getSubcatId = Category::where('cat_id', $product->cat_id)->first();
        $category = DB::table('categories')
            ->where('level', 0)
            ->where('is_deleted', 0)
            ->get();

        $sub_category = DB::table('categories')
            ->where('level', '!=', 0)
            ->where('is_deleted', 0)
            ->get();

        $taxList = Tax::all();


        // dd($taxList);
        $attributes = Attribute::with(['values'])->where('is_deleted', 0)->get();

        $url_aws = $this->getImageStorage();

        return view('admin.product.edit', [
            'admin_email' => $admin_email,
            'admin' => $admin,
            'logo' => $logo,
            'title' => $title,
            'product' => $product,
            'images' => $images,
            'url_aws' => $url_aws,
            'category' => $category,
            'sub_category' => $sub_category,
            'sub_category_id' => $getSubcatId,
            'attributes' => $attributes,
            'taxList' => $taxList
        ]);
    }
    public function UpdateProduct(Request $request, $uuid)
    {
        if (Setting::valActDeMode())
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $currentProduct = Product::where('uuid', $uuid)->first();
        $this->validate(
            $request,
            [
                'product_name' => 'required',
                'parent_cat' => 'required',
                'hsn_number' => 'required|unique:products,hsn_number,' . $currentProduct->product_id . ',product_id',
                'base_mrp' => 'required|numeric|min:1',
                // 'base_price' => 'numeric|min:1|lte:base_mrp',
                'images' => 'sometimes|array|max:10',
                // 'product_video' => 'sometimes|max:4',
                // 'images.*' => 'required_with:images|image|max:5120',
                'quantity' => 'required_if:type,Simple',
                'variations' => 'required_if:type,Variable|array',
                'variations.*.mrp' => 'required_if:type,Variable|numeric|min:1',
                'variations.*.price' => 'required_if:type,Variable|numeric|min:1',
                'variations.*.stock' => 'required_if:type,Variable|integer|min:0',
                'variations.*.attributes' => 'sometimes|array',
                //'variations.*.attributes.*' => 'required',
                'variations.*.image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ],
            [
                'product_name.required' => 'Enter product name.',
                'parent_cat.required' => 'Select category',
                'type.required' => 'Select Type',
                'hsn_number.required' => 'SKU code is required',
                'hsn_number.unique' => 'SKU code should be unique',
                'images.max' => 'You can upload a maximum of 10 images.',
                'images.*' => 'Images should be Image'

            ]
        );

        // dd($request->all());
        $category_id = $request->child_cat_id ?? $request->cat_id ?? $request->parent_cat;
        $product_id = $request->uuid;
        $product_name = $request->product_name;
        $date = date('d-m-Y');
        $product_image = $request->product_image;
        $product_video = $request->product_video;
        // dd($request->tags);
        $tags = explode(",", $request->tags);
        $images = $request->images;
        $description = $request->description;

        $getProduct = Product::where('uuid', $uuid)
            ->with(['variations.variation_attributes'])
            ->first();

        $getProduct->update([
            'slug' => NULL
        ]);

        $image = $getProduct->product_image;
        $images_product_video = $getProduct->product_video;
        $image_size_guide_images = $getProduct->size_guide_images;


        $this->getImageStorage();

        if ($request->hasFile('product_image')) {
            $this->validate(
                $request,
                [
                    'product_image' => 'required',
                ],
                [
                    'product_image.required' => 'Choose Product image.',
                ]
            );

            $product_image = $request->product_image;

            //  dd($image,$product_image);
            $fileName = $product_image->getClientOriginalName();
            $fileName = str_replace(" ", "-", $fileName);

            if ($this->storage_space != "same_server") {
                $product_image_name = $product_image->getClientOriginalName();
                $product_image = $request->file('product_image');
                $filePath = '/product/' . $product_image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('product_image'), 'r+'), 'public');
            } else {
                $oldImagePath = public_path('storage/' . $getProduct->product_image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
                $filePath = Storage::disk('public')->put('products', request()->file('product_image'));
                // $product_image->move('images/product/' . $date . '/', $fileName);
                // $filePath = '/images/product/' . $date . '/' . $fileName;
                // dd($filePath, request()->file('product_image')  );
            }
        } else {
            $filePath = $image;
        }

        if ($request->hasFile('product_video')) {

            // dd( $request->product_video);
            $product_video = $request->product_video;

            //  dd($image,$size_guide_images);
            $fileName = $product_video->getClientOriginalName();
            $fileName = str_replace(" ", "-", $fileName);

            if ($this->storage_space != "same_server") {
                $product_video_name = $product_video->getClientOriginalName();
                $product_video = $request->file('product_video');
                $filePath_product_video = '/product/' . $product_video_name;
                Storage::disk($this->storage_space)->put($filePath_product_video, fopen($request->file('product_video'), 'r+'), 'public');
            } else {
                $oldImagePath = public_path('storage/' . $getProduct->product_video);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
                $filePath_product_video = Storage::disk('public')->put('products', request()->file('product_video'));
                // $product_video->move('images/product/' . $date . '/', $fileName);
                // $filePath = '/images/product/' . $date . '/' . $fileName;
                // dd($filePath, request()->file('product_video')  );
            }
        } else {
            $filePath_product_video = $images_product_video;
        }

        if ($request->hasFile('size_guide_images')) {
            $this->validate(
                $request,
                [
                    'size_guide_images' => 'required',
                ],
                [
                    'size_guide_images.required' => 'Choose Product image.',
                ]
            );

            $size_guide_images = $request->size_guide_images;

            //  dd($image,$size_guide_images);
            $fileName = $size_guide_images->getClientOriginalName();
            $fileName = str_replace(" ", "-", $fileName);

            if ($this->storage_space != "same_server") {
                $size_guide_images_name = $size_guide_images->getClientOriginalName();
                $size_guide_images = $request->file('size_guide_images');
                $filePath_size_guide_images = '/product/' . $size_guide_images_name;
                Storage::disk($this->storage_space)->put($filePath_size_guide_images, fopen($request->file('size_guide_images'), 'r+'), 'public');
            } else {
                $oldImagePath = public_path('storage/' . $getProduct->size_guide_images);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
                $filePath_size_guide_images = Storage::disk('public')->put('products', request()->file('size_guide_images'));
                // $size_guide_images->move('images/product/' . $date . '/', $fileName);
                // $filePath = '/images/product/' . $date . '/' . $fileName;
                // dd($filePath, request()->file('size_guide_images')  );
            }
        } else {
            $filePath_size_guide_images = $image_size_guide_images;
        }

        if ($request->on_sale == 'on') {

            $onSale = true;
        } else {

            $onSale = false;
        }
        // dd( $request->tax_id, );

        $getTaxId = Tax::pluck('tax_id')->first();
        $insertproduct = $getProduct->update([
            'cat_id' => $category_id,
            'product_name' => $product_name,
            'product_image' => $filePath,
            'description' => $request->description,
            'info' => $request->info,
            'shipping' => $request->shipping,
            'base_price' => $request->base_price,
            'base_mrp' => $request->base_mrp,
            'on_sale' => $onSale,
            'tags' => $request->tags,
            'hsn_number' => $request->hsn_number,
            'ean' => $request->ean,
            'tax_id' => $request->tax_id ?? $getTaxId,
            'size_guide_images' => $filePath_size_guide_images ?? $getProduct->size_guide_images,
            'product_video' => $filePath_product_video ?? $getProduct->product_video,
        ]);

        //  dd($insertproduct,$filePath,$request->hasFile('product_image'),$this->storage_space);


        if ($getProduct->type === 'Variable') {
            // Handle deleted variations
            if ($request->has('deleted_variations')) {
                foreach ($request->deleted_variations as $variationId) {
                    $variation = Variation::where('uuid', $variationId)->first();
                    if ($variation) {
                        $variation->variation_attributes()->update(['is_deleted' => 1]);
                        $variation->update(['is_deleted' => 1]);
                    }
                }
            }

            // Update existing variations and add new ones
            if ($request->has('variations')) {
                $count = 1;

                foreach ($request->variations as $key => $variationData) {
                    // Handle variation image
                    if (!empty($variationData) && Str::isUuid($key)) {
                        // $key = preg_replace('/\s+/', '', $key);
                        // dd($request->variations);
                        $variation = Variation::where('uuid', $key)->first();

                        if ($variation) {
                            $variation->update([
                                'price' => $request->variations[$key]['price'] ?? $request->variations[$key]['mrp'],
                                'stock' => $request->variations[$key]['stock'],
                                'mrp' => $request->variations[$key]['mrp'],
                            ]);
                            // Handle file upload if needed
                            if ($request->hasFile("variations.{$key}.image")) {
                                $oldImagePath = public_path('storage/' . $variation->image);
                                if (File::exists($oldImagePath)) {
                                    File::delete($oldImagePath);
                                }
                                $imagePath = Storage::disk('public')->put('variations', $request->file("variations.{$key}.image"));
                                $variation->update(['image' => $imagePath]);
                            }
                        }
                    } elseif ($key == 'new_' . $count && is_array($variationData)) {
                        $variation = Variation::create([
                            'product_id' => $getProduct->product_id,
                            'price' => $variationData['price'] ??  $variationData['mrp'],
                            'stock' => $variationData['stock'],
                            'mrp' => $variationData['mrp'],
                        ]);

                        $variationUpdate = $variation;
                        //   dd($request->variations,$request->all() ,$variation, $variationData['attributes']);
                        $imageInputName = "variations.$key.image";
                        if ($request->hasFile($imageInputName)) {
                            $imagePath = Storage::disk('public')->put('variations', $request->file($imageInputName));
                            $variationUpdate->update(['image' => $imagePath]);
                        }

                        if (!empty($variationData['attributes'])) {
                            foreach ($variationData['attributes'] as $attributeId => $optionId) {
                                if ($optionId) {

                                    VariationAttribute::create([
                                        'variation_id' => $variation->id,
                                        'attribute_id' => $optionId
                                    ]);
                                }
                            }
                        }
                    }
                    $count++;
                }
            }
        } else {
            // If switching to Simple product, delete all variations
            // $getProduct->variations()->update(['is_deleted' => 1]);
            $getProduct->variation()->update([
                'stock' => $request->quantity,
                'price' => $getProduct->base_price,
                'mrp' => $getProduct->base_mrp,
                'image' => $getProduct->product_image,
                'is_deleted' => 0
            ]);
        }

        // $delete_main_image = ProductImage::where('product_id', $product_id)
        //     ->where('type', 1)
        //     ->delete();

        // $main_image = ProductImage::insert([
        //     'product_id' => $product_id,
        //     'image' => $filePath,
        //     'type' => 1
        // ]);

        $enternew = NULL;
        $enternewim = NULL;
        if ($request->images != NULL) {
            $deleteold = ProductImage::where('product_id', $getProduct->product_id)
                ->where('type', 1)
                ->get();

            foreach ($deleteold as $old) {
                $oldImagePath = public_path('storage/' . $old->image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }

            ProductImage::where('product_id', $getProduct->product_id)
                ->where('type', 1)
                ->delete();

            foreach ($images as $image) {
                $fileName = $image->getClientOriginalName();
                $fileName = str_replace(" ", "-", $fileName);
                if ($this->storage_space != "same_server") {
                    $product_image_name = $image->getClientOriginalName();
                    $product_image = $request->file('product_image');
                    $filePath1 = '/product/' . $product_image_name;
                    Storage::disk($this->storage_space)->put($filePath1, fopen($image, 'r+'), 'public');
                } else {
                    $filePath1 = Storage::disk('public')->put('products', $image);
                    // $image->move('images/product/' . $date . '/', $fileName);
                    // $filePath1 = '/images/product/' . $date . '/' . $fileName;
                }

                // dd($filePath1);
                $enternewim = ProductImage::insert([
                    'product_id' => $getProduct->product_id,
                    'image' => $filePath1,
                    'type' => 1
                ]);
            }
        }

        if ($insertproduct || $enternew != NULL || $enternewim != NULL) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Already Updated'));
        }
    }
    public function DeleteProduct(Request $request, $uuid)
    {
        if (Setting::valActDeMode())
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $product_id = $request->uuid;

        $delete = DB::table('products')->where('uuid', $uuid)->update(['is_deleted' => 1]);
        if ($delete) {
            // $delete = DB::table('product_varient')->where('product_id', $request->product_id)->delete();
            // $deleteold = DB::table('tags')
            //     ->where('product_id', $product_id)
            //     ->delete();
            return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }
    private function getProductsDataTable(Request $request)
    {
        $query = DB::table('products')
            ->join('categories', 'products.cat_id', '=', 'categories.cat_id')
            ->where('products.approved', 1)
            ->where('products.is_deleted', 0)
            ->select(
                'products.*',
                'products.uuid',
                'products.product_id',
                'products.product_name',
                'products.product_image',
                'products.type',
                'categories.title as category_title'
            );

        // Apply DataTables search (global search from search box)
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('products.product_name', 'LIKE', "%{$search}%")
                    ->orWhere('products.product_id', 'LIKE', "%{$search}%")
                    ->orWhere('categories.title', 'LIKE', "%{$search}%")
                    ->orWhere('products.type', 'LIKE', "%{$search}%");
            });
        }

        // Apply category filter
        if ($request->has('category_filter') && !empty($request->category_filter)) {
            $query->where('products.cat_id', $request->category_filter);
        }

        // Apply type filter
        if ($request->has('type_filter') && !empty($request->type_filter)) {
            $query->where('products.type', $request->type_filter);
        }

        // Get total records before filtering
        $totalRecords = DB::table('products')
            ->where('approved', 1)
            ->where('is_deleted', 0)
            ->count();

        // Get total records after filtering
        $filteredRecords = $query->count();

        // Apply ordering
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];

            $columns = ['products.product_id', 'products.product_name', 'products.product_id', 'categories.title', 'products.type', '', ''];

            if (isset($columns[$orderColumn]) && !empty($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            }
        } else {
            $query->orderBy('products.product_id', 'desc');
        }

        // Apply pagination
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;

        $products = $query->skip($start)->take($length)->get();

        // Get AWS URL
        $url_aws = $this->getImageStorage();

        // Format data for DataTables
        $data = [];
        foreach ($products as $index => $product) {
            $data[] = [
                'DT_RowIndex' => $start + $index + 1,
                'product_name' => $product->product_name,
                'product_id' => $product->product_id,
                'category_title' => $product->category_title,
                'type' => $product->type ?? 'N/A',
                'image' => $this->formatProductImage($product, $url_aws),
                'action' => $this->formatProductActions($product)
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }
    /**
     * Format product image HTML
     */
    private function formatProductImage($product, $url_aws)
    {
        $imageUrl = $url_aws . '/storage/' . $product->product_image;
        return '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($product->product_name) . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">';
    }

    /**
     * Format product action buttons
     */
    private function formatProductActions($product)
    {
        // Use uuid if it exists, otherwise fall back to product_id
        $identifier = $product->uuid ?? $product->product_id;

        $editUrl = route('EditProduct', ['uuid' => $identifier]);
        $deleteUrl = route('DeleteProduct', ['uuid' => $identifier]);

        $html = '<div class="btn-group" role="group">';
        $html .= '<a href="' . $editUrl . '" class="btn btn-sm btn-primary" title="Edit"><i class="fa fa-edit"></i></a>';
        $html .= '<a href="' . $deleteUrl . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this product?\')" title="Delete"><i class="fa fa-trash"></i></a>';
        $html .= '</div>';

        return $html;
    }
    public function exportProducts(Request $request)
    {
        $categoryFilter = $request->category_filter;
        $typeFilter = $request->type_filter;
        $search = $request->search;

        $fileName = 'products_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new ProductsExport($categoryFilter, $typeFilter, $search), $fileName);
    }
}
