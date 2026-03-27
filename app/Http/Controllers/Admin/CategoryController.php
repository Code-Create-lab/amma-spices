<?php

namespace App\Http\Controllers\Admin;

use App\Traits\ImageStoragePicker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use DB;
use Illuminate\Support\Facades\File;
use Session;
use Auth;
use Illuminate\Support\Facades\Storage;
use App\Setting;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ImageStoragePicker;

    public function list(Request $request)
    {
        $title = "Category List";
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();

        $url_aws = "";

        $category = DB::table('categories')
            ->where('parent', 0)
            ->where('is_deleted', 0)
            ->orderBy("cat_id", 'desc')
            ->paginate(10);

        $adminTopApp = DB::table('categories')
            ->get();

        $url_aws = $this->getImageStorage();

        return view('admin.category.index', compact('title', "admin", "logo", "category", "adminTopApp", "url_aws"));
    }

    public function AddCategory(Request $request)
    {
        $title = "Add Category";
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $category = DB::table('categories')
            ->where('level', 0)
            ->get();
        $tax = DB::table('tax_types')
            ->get();

        $url_aws = $this->getImageStorage();

        return view('admin.category.add', compact("category", "admin_email", "logo", "admin", "title", "tax", "url_aws"));
    }

    public function AddNewCategory(Request $request)
    {
        if (Setting::valActDeMode()) return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $parent_id = $request->parent_id;
        $cat_id = $request->cat_id;
        $type = $request->type;
        if ($type == NULL) {
            $type = 0;
        }
        $tax = $request->tax;
        if ($tax == NULL) {
            $tax = NULL;
            $tx_name = NULL;
        } else {
            $tx = DB::table('tax_types')
                ->where('tax_id', $tax)
                ->first();
            $tx_name = $tx->name;
        }
        $tax_per = $request->tax_per;
        if ($tax_per == NULL) {
            $tax_per = 0;
        }
        $category_name = $request->cat_name;
        $status = 1;
        $slug = strtolower(str_replace(" ", '-', $category_name));
        $desc = $request->filled('desc') ? $request->desc : null;
        $filePath = '';
        $bannerFilePath = null;
        $category = DB::table('categories')
            ->where('cat_id', $parent_id)
            ->first();

        if ($status == "") {
            $status = 0;
        }

        if ($category) {
            if ($parent_id == $category->cat_id) {
                if ($category->level == 0) {
                    $level = 1;
                } elseif ($category->level == 1) {
                    $level = 2;
                }
            }
        } else {
            $level = 0;
        }
    // dd($request->all());
        $this->validate(
            $request,
            [
                'cat_name'   => 'required',
                'cat_image'  => 'required|file|mimes:jpeg,png,jpg,webp|max:2048',
                'banner_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
                'parent_id'  => 'sometimes|required',
            ],
            [
                'cat_name.required'  => 'Enter category name.',
                'cat_image.required' => 'Choose category image.',
                'parent_id.required' => 'Parent category is required.',
            ]
        );

        if ($request->hasFile('cat_image')) {
            $filePath = $this->uploadCategoryFile($request->file('cat_image'), 'category');
        } else {
            $filePath = 'images/';
        }

        if ($request->hasFile('banner_image')) {
            $bannerFilePath = $this->uploadCategoryFile($request->file('banner_image'), 'category-banner');
        }

        $insertCategory = DB::table('categories')
            ->insert([
                'parent' => $parent_id,
                'title' => $category_name,
                'slug' => $slug,
                'level' => $level,
                'image' => $filePath,
                'banner_image' => $bannerFilePath,
                'status' => $status,
                'description' => $desc,
                'tax_type' => $type,
                'tx_id' => $tax,
                'tax_per' => $tax_per,
                'tax_name' => $tx_name

            ]);

        if ($insertCategory) {
            return redirect()->back()->withSuccess(trans('keywords.Added Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function EditCategory(Request $request)
    {
        $category_id = $request->category_id;
        $title = "Edit Category";
        $admin_email = Auth::guard('admin')->user()->email;;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $category = DB::table('categories')
            ->where('level', 0)
            ->orWhere('level', 1)
            ->where('cat_id', '!=', $category_id)
            ->get();

        $cat = DB::table('categories')
            ->where('cat_id', $category_id)
            ->first();
        $tax = DB::table('tax_types')
            ->get();
        $url_aws = $url_aws = $this->getImageStorage();

        return view('admin.category.edit', compact("category", "admin_email", "admin", "logo", "cat", "title", "url_aws", "tax"));
    }

    public function UpdateCategory(Request $request)
    {
        if (Setting::valActDeMode()) return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $category_id = $request->category_id;
        $type = $request->type;
        if ($type == NULL) {
            $type = 0;
        }
        $tax = $request->tax;
        if ($tax == NULL) {
            $tax = NULL;
            $tx_name = NULL;
        } else {
            $tx = DB::table('tax_types')
                ->where('tax_id', $tax)
                ->first();
            $tx_name = $tx->name;
        }
        $tax_per = $request->tax_per;
        if ($tax_per == NULL) {
            $tax_per = 0;
        }
        $parent_id = $request->parent_id;
        $category_name = $request->cat_name;
        $status = 1;
        $slug = strtolower(str_replace(" ", '-', $category_name));
        $desc = $request->filled('desc') ? $request->desc : null;

        $category = DB::table('categories')
            ->where('cat_id', $parent_id)
            ->first();

        if ($status == "") {
            $status = 0;
        }

        if ($category) {
            if ($parent_id == $category->cat_id) {
                if ($category->level == 0) {
                    $level = 1;
                } elseif ($category->level == 1) {
                    $level = 2;
                }
            }
        } else {
            $level = 0;
        }


        $this->validate(
            $request,
            [

                'cat_name' => 'required',
            ],
            [
                'cat_name.required' => 'Enter category name.',
            ]
        );

        $getCategory = DB::table('categories')
            ->where('cat_id', $category_id)
            ->first();

        $image = $getCategory->image;
        $bannerImage = $getCategory->banner_image;
        $filePath = $image;
        $bannerFilePath = $bannerImage;

        if ($request->hasFile('cat_image')) {
            $this->validate(
                $request,
                [
                    'cat_image' => 'required|file|mimes:jpeg,png,jpg,webp|max:2048',
                ],
                [
                    'cat_image.required' => 'Choose category image.',
                ]
            );

            $this->deleteCategoryFile($getCategory->image);
            $filePath = $this->uploadCategoryFile($request->file('cat_image'), 'category');
        }

        if ($request->hasFile('banner_image')) {
            $this->validate(
                $request,
                [
                    'banner_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
                ]
            );

            $this->deleteCategoryFile($getCategory->banner_image);
            $bannerFilePath = $this->uploadCategoryFile($request->file('banner_image'), 'category-banner');
        }
        $tx = DB::table('tax_types')
            ->where('tax_id', $tax)
            ->first();
        $insertCategory = DB::table('categories')
            ->where('cat_id', $category_id)
            ->update([
                'parent' => $parent_id,
                'title' => $category_name,
                'level' => $level,
                'image' => $filePath,
                'banner_image' => $bannerFilePath,
                'status' => $status,
                'description' => $desc,
                'tax_type' => $type,
                'tx_id' => $tax,
                'tax_per' => $tax_per,
                'tax_name' => $tx_name
            ]);

        if ($insertCategory) {
            return redirect()->back()->withSuccess(trans('keywords.Updated Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function DeleteCategory(Request $request)
    {
        if (Setting::valActDeMode()) return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        $category_id = $request->category_id;

        $product =  DB::table('products')->where('cat_id', $category_id)->where('is_deleted',0)->get();
        if($product->count() > 0)
        {
            return redirect()->back()->withErrors('This category cannot be deleted because products are mapped to it.');
        }

        $delete = DB::table('categories')->where('cat_id', $request->category_id)->update(['is_deleted' => 1]);
        if ($delete) {
            // $deleteproduct = DB::table('product')
            //     ->where('cat_id', $request->category_id)->delete();

            // $deletechild = DB::table('categories')
            //     ->where('parent', $request->category_id)->delete();
            return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Nothing to Update'));
        }
    }


    public function getSubcategories($categoryId)
    {
        $subcategories = Category::where('parent', $categoryId)->get(['cat_id', 'title']);
        return response()->json($subcategories);
    }

    protected function uploadCategoryFile($image, string $folder): string
    {
        $this->getImageStorage();

        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $fileName = Str::uuid()->toString() . '.' . $extension;

        if ($this->storage_space != "same_server") {
            $filePath = '/' . trim($folder, '/') . '/' . $fileName;
            Storage::disk($this->storage_space)->put($filePath, fopen($image->getRealPath(), 'r'), 'public');

            return $filePath;
        }

        $date = date('d-m-Y');
        $relativeDirectory = 'images/' . trim($folder, '/') . '/' . $date . '/';
        File::ensureDirectoryExists(public_path($relativeDirectory));
        $image->move(public_path($relativeDirectory), $fileName);

        return '/' . str_replace('\\', '/', $relativeDirectory . $fileName);
    }

    protected function deleteCategoryFile(?string $filePath): void
    {
        if (!$filePath) {
            return;
        }

        $this->getImageStorage();

        if ($this->storage_space != "same_server") {
            Storage::disk($this->storage_space)->delete(ltrim($filePath, '/'));

            return;
        }

        $oldImagePath = public_path(ltrim($filePath, '/'));
        if (File::exists($oldImagePath)) {
            File::delete($oldImagePath);
        }
    }
}
