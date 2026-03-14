<?php

namespace App\Http\Controllers\Frontend\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\RatingReview;
use App\Models\RecentlyViewedProducts;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SingleProductController extends Controller
{
    public function index($slug)
    {

        // dd("asdasd");
        $product = Product::where('slug', $slug)->where('is_deleted', 0)->firstOrFail();
        $response = (new ProductResource($product))->toArray(request());
        // dd($response);
        $related_products = Product::where('is_deleted', 0)->where('cat_id', $product->cat_id)->get();

        $reviews = RatingReview::where('product_id', $product->product_id)->where('is_approved', 1)->where('is_deleted', 0)->orderBy('id', 'desc')->get();
        $avg_rating = $reviews->avg('rating');
        $total_reviews = $reviews->count();
        $top_reviews = $reviews->take(6);
        // dd($reviews,$avg_rating);
        // dd($related_products,$product);
        $tags = explode(',', $product->tags);

        $matchingProducts = Product::where('product_id', '!=', $product->product_id)
            ->where(function ($query) use ($tags) {
                foreach ($tags as $tag) {
                    $query->orWhere('tags', 'LIKE', '%' . trim($tag) . '%');
                }
            })
            ->get();
        $wishlist = collect();
        $user = auth()->user();
        if ($user) {
            $wishlist = Wishlist::where('user_id', $user->id)->get(); // Get user's wishlist
        }

        //    $reviews = RatingReview::where('product_id', $product->product_id)->where('is_approved',1)->where('is_deleted',0)->get();

        $id = $product->product_id;

        if (auth()->check()) {
            // Store in the database for logged-in users
            $userId = auth()->id();

            // STEP 1: Merge session recently viewed into database
            $sessionViewed = session()->get('recently_viewed', []);

            if (!empty($sessionViewed)) {
                foreach ($sessionViewed as $sessionProductId) {
                    $alreadyExists = RecentlyViewedProducts::where('user_id', $userId)
                        ->where('product_id', $sessionProductId)
                        ->exists();

                    if (!$alreadyExists) {
                        RecentlyViewedProducts::create([
                            'user_id' => $userId,
                            'product_id' => $sessionProductId,
                        ]);
                    }
                }
                // Clear session recently viewed after merging
                // session()->forget('recently_viewed');
            }

            // Check if the product is already in the list of recently viewed
            $recentlyViewed = RecentlyViewedProducts::where('user_id', $userId)
                ->where('product_id', $id)
                ->first();

            if (!$recentlyViewed) {
                // Add to the database
                RecentlyViewedProducts::create([
                    'user_id' => $userId,
                    'product_id' => $id,
                ]);
            }

            $recentlyViewedIds = RecentlyViewedProducts::where('user_id', Auth::id())
                ->pluck('product_id')
                ->toArray();

            $recentlyViewedProducts = Product::where('is_deleted', 0)->whereIn('product_id', $recentlyViewedIds)
                ->orderByRaw("FIELD(product_id, " . implode(',', $recentlyViewedIds) . ")")
                ->get();
        } else {
            // Get existing viewed products from session
            $viewed = session()->get('recently_viewed', []);

            // Remove if already exists to prevent duplication
            if (($key = array_search($id, $viewed)) !== false) {
                unset($viewed[$key]);
            }

            // Add current product ID to the beginning
            array_unshift($viewed, $id);

            // Limit to 5 or any number you prefer
            $viewed = array_slice($viewed, 0, 20);

            // Store back in session
            session(['recently_viewed' => $viewed]);

            $recentlyViewedIds = session()->get('recently_viewed', []);
            $recentlyViewedProducts = Product::where('is_deleted', 0)->whereIn('product_id', $recentlyViewedIds)
                ->orderByRaw("FIELD(product_id, " . implode(',', $recentlyViewedIds) . ")")
                ->get();
        }

        $today = Carbon::today();
        $coupons = Coupon::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        // dd($recentlyViewedProducts);
        return view('frontend.product.single_view_product', [
            'response' => $response,
            'matchingProducts' => $matchingProducts,
            'related_products' => $related_products,
            'wishlist' => $wishlist,
            'coupons' => $coupons,
            'recentlyViewedProducts' => $recentlyViewedProducts,
            'avg_rating' => $avg_rating,
            'total_reviews' => $total_reviews,
            'top_reviews' => $top_reviews,
        ]);
    }
}
