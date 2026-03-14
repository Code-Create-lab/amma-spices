<?php

namespace App\Http\Controllers\Frontend\Search;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        $results = Product::where('product_name', 'LIKE', "%{$query}%")
            ->select('product_name', 'slug','product_image')
            ->where('is_deleted',0)
            ->take(5)
            ->get();

        return response()->json($results);
    }
    public function searchResults(Request $request)
    {
        $query = $request->q;
        $user = auth()->user();
        $wishlist = $user ? Wishlist::where('user_id', $user->id)->get() : collect();

        $products = Product::with(['variations'])
            ->where('product_name', 'LIKE', "%{$query}%")
            ->where('is_deleted',0)
            ->paginate(10);

        return view('frontend.product.search_results', [
            'products' => $products,
            'query' => $query,
            'wishlist' => $wishlist
        ]);
    }
}
