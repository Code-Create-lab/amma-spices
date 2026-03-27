<?php

namespace App\Http\Controllers\Frontend\ShopGrid;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Wishlist;
use App\Traits\ImageStoragePicker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopGridController extends Controller
{
    use ImageStoragePicker;

    public function index($slug)
    {

        // dd("dasdasd");

        $category = Category::where('slug', $slug)
            ->where('cat_id', '!=', 0)
            // ->where('level', 0)
            ->where('is_deleted', 0)
            ->firstOrFail();

        $sub_categories = Category::where('parent', $category->cat_id)
            ->paginate(10);

        return view('frontend.product.shop_page', [
            'sub_categories' => $sub_categories,
            'category' => $category,
            'slug' => $slug,
        ]);
    }
    public function allCategories()
    {
        $categories = Category::where('is_deleted', 0)->where('parent', 0)->paginate(10);
        return view('frontend.product.all_categories', [
            'categories' => $categories
        ]);
    }

    public function getCatList($slug, $sub_category_slug = null, $child_category_slug = null)
    {
        // ---------------------------
        // 1. MAIN CATEGORY (Level 1)
        // ---------------------------
        $category = Category::where('slug', $slug)
            ->where('is_deleted', 0)
            ->firstOrFail();

        // If sub-category exists...
        if ($sub_category_slug) {

            // ---------------------------
            // 2. SUB CATEGORY (Level 2)
            // ---------------------------
            $sub_category = Category::where('slug', $sub_category_slug)
                ->where('parent', $category->cat_id)
                ->where('is_deleted', 0)
                ->firstOrFail();

            // If child category exists...
            if ($child_category_slug) {

                // ---------------------------
                // 3. CHILD CATEGORY (Level 3)
                // ---------------------------
                $child_category = Category::where('slug', $child_category_slug)
                    ->where('parent', $sub_category->cat_id)
                    ->where('is_deleted', 0)
                    ->firstOrFail();

                // Fetch products/categories under child
                $categories = Category::where('parent', $child_category->cat_id)
                    ->paginate(10);

                return view('frontend.category.index', [
                    'categories' => $categories,
                    'category' => $child_category,
                    'url_aws' => $this->getImageStorage(),
                    'level' => 3
                ]);
            }

            // Level 2 sub-category listing
            $categories = Category::where('parent', $sub_category->cat_id)
                ->paginate(10);

            return view('frontend.category.index', [
                'categories' => $categories,
                'category' => $sub_category,
                'url_aws' => $this->getImageStorage(),
                'level' => 2
            ]);
        }

        // Level 1 main category listing
        $categories = Category::where('parent', $category->cat_id)
            ->paginate(10);

        if (auth()->check()) {
            $wishlist = Wishlist::with('product')
                ->where('user_id', Auth::id())
                ->whereHas('product', function ($query) {
                    $query->where('is_deleted', 0);
                })
                ->get();
        } else {
            $wishlist = collect();
        }

        return view('frontend.category.index', [
            'categories' => $categories,
            'category' => $category,
            'wishlist' => $wishlist,
            'url_aws' => $this->getImageStorage(),
            'level' => 1
        ]);
    }
}
