<?php

namespace App\Http\Controllers\Frontend\Product;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function products_category_wise($category_slug,$sub_category_slug, Request $request)
    {
        // Get sub-category details with its parent
        $sub_category = Category::with(['sub_category_parent'])->where('slug', $sub_category_slug)->firstOrFail();

        // Base product query
        $products = Product::where('cat_id', $sub_category->cat_id)
            ->where('is_deleted', 0);

        // ✅ Sorting logic
        $sort = $request->input('sort');
        switch ($sort) {
            case 'latest':
                $products->orderBy('created_at', 'desc');
                break;
            case 'price_low_high':
                $products->orderBy('base_price', 'asc');
                break;
            case 'price_high_low':
                $products->orderBy('base_price', 'desc');
                break;
            case 'name_asc':
                $products->orderBy('product_name', 'asc');
                break;
        }

        // Paginate with query params preserved
        $products = $products->paginate(10)->appends($request->all());

        // Load wishlist for logged-in user
        $wishlist = collect();
        $user = auth()->user();
        if ($user) {
            $wishlist = Wishlist::where('user_id', $user->id)->get();
        }

        return view('frontend.product.category_wise', [
            'products' => $products,
            'wishlist' => $wishlist,
            'sub_category' => $sub_category,
        ]);
    }

    public function bestSellerProducts(Request $request)
    {
        $products = Product::where('is_deleted', 0);

        // 🔁 Apply sorting
        $sort = $request->input('sort');
        switch ($sort) {
            case 'latest':
                $products->orderBy('created_at', 'desc');
                break;
            case 'price_low_high':
                $products->orderBy('base_price', 'asc');
                break;
            case 'price_high_low':
                $products->orderBy('base_price', 'desc');
                break;
            case 'name_asc':
                $products->orderBy('product_name', 'asc');
                break;
        }

        $products = $products->paginate(10)->appends($request->all());

        $wishlist = collect();
        $user = auth()->user();
        if ($user) {
            $wishlist = Wishlist::where('user_id', $user->id)->get();
        }

        return view('frontend.product.best_seller_products', [
            'products' => $products,
            'wishlist' => $wishlist
        ]);
    }

    public function newProducts(Request $request)
    {
        $products = Product::where('is_deleted', 0);

        // 🔁 Apply sorting
        $sort = $request->input('sort');
        switch ($sort) {
            case 'latest':
                $products->orderBy('created_at', 'desc');
                break;
            case 'price_low_high':
                $products->orderBy('base_price', 'asc');
                break;
            case 'price_high_low':
                $products->orderBy('base_price', 'desc');
                break;
            case 'name_asc':
                $products->orderBy('product_name', 'asc');
                break;
            default:
                $products->orderBy('created_at', 'desc'); // Default: newest first
                break;
        }

        $products = $products->paginate(10)->appends($request->all());

        $wishlist = collect();
        $user = auth()->user();
        if ($user) {
            $wishlist = Wishlist::where('user_id', $user->id)->get();
        }

        return view('frontend.product.new_products', [
            'products' => $products,
            'wishlist' => $wishlist
        ]);
    }

}
