<?php

namespace App\Http\Controllers\Frontend\Product;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class ShopPageController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $wishlist = $user ? Wishlist::where('user_id', $user->id)->get() : collect();
    
        $categories = Category::with(['sub_categories'])
            ->where('level', 0)
            ->where('is_deleted', 0)
            ->get();
    
        $attributes = Attribute::with(['values'])->where('is_deleted',0)->get();
    
        $categoryId = $request->category_id;
        $products = Product::query()
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('cat_id', $categoryId);
            })
            ->where('is_deleted', 0);
    
        $attributeFilters = $request->input('attributes');
        if (!empty($attributeFilters)) {
            $products->whereHas('variations', function ($variationQuery) use ($attributeFilters) {
                foreach ($attributeFilters as $attributeId => $valueIds) {
                    $variationQuery->whereHas('variation_attributes', function ($q) use ($attributeId, $valueIds) {
                        $q->whereIn('attribute_id', $valueIds);
                    });
                }
            });
        }
    
        // ✅ Sorting Logic
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
    
        return view('frontend.product.shop_page', [
            'products' => $products,
            'categories' => $categories,
            'wishlist' => $wishlist,
            'attributes' => $attributes,
        ]);
    }
    
}
