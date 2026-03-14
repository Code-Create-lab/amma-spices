<?php

namespace App\Http\Controllers\Frontend\Wishlist;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class WishlistController extends Controller
{
    public function index()
    {

        if (!auth()->check()) {


            $getWishLists = session()->get('wishlist');

            // dd($getWishLists );
            $wishlistArray = collect();

            if ($getWishLists) {

                foreach ($getWishLists as $wishList) {

                    $wishlists = Product::where('is_deleted', 0)->where('product_id', $wishList['product_id'])->get();
                    // dd($wishlists);
                    if ($wishlists->count() > 0) {

                        $wishlistArray->push($wishlists[0]);
                    }
                    // array_push($wishlistArray, $wishlists);

                }
            }
            // dd($wishlistArray,  $wishList['product_id']);


            //  $wishlists = Wishlist::with(['product'])->where('user_id', Auth::id())->paginate(8);
            return view('frontend.wishlist.index', [
                'wishlists' => $wishlistArray
            ]);
        } else {



            // $wishlists = Wishlist::with(['product' => function ($query) {
            //     $query->where('is_deleted', 0); // Exclude soft-deleted products
            // }])->where('user_id', Auth::id())->paginate(8);


            $wishlists = Wishlist::with('product')
                ->where('user_id', Auth::id())
                ->whereHas('product', function ($query) {
                    $query->where('is_deleted', 0);
                })
                ->get();
            $wishlistProductIds = Wishlist::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();


            // dd($wishlists);
            return view('frontend.wishlist.index', [
                'wishlistProductIds' => $wishlistProductIds,
                'wishlists' => $wishlists
            ]);
        }
    }

    public function addToWishlist(Request $request)
    {
        $request->validate([
            'product_id' => ['required'],
        ]);
        try {
            $wishlist = Wishlist::where('product_id', $request->product_id)->where('user_id', Auth::id())->first();
            if (empty($wishlist)) {
                $product = Product::where('product_id', $request->product_id)->first();
                Wishlist::create([
                    'user_id' => Auth::id(),
                    'quantity' => 1,
                    'product_id' => $request->product_id,
                    'store_id' => 1,
                    'product_name' => $product->product_name,
                    'description' => $product->description,
                    'price' => $product->base_price,
                    'mrp' => $product->base_mrp,
                    'product_image' => $product->product_image
                ]);
                $wishlist_count = Wishlist::where('user_id', Auth::id())->count();
                return Response::json([
                    'success' => true,
                    'message' => 'Product Added To Wishlist',
                    'count' => $wishlist_count
                ]);
            } else {
                return Response::json([
                    'success' => false,
                    'message' => 'Product Already In Wishlist'
                ]);
            }
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function removeFromWishlist(Request $request)
    {
        $request->validate([
            'product_id' => ['required'],
        ]);
        try {
            Wishlist::where('product_id', $request->product_id)->where('user_id', Auth::id())->delete();
            $wishlist_count = Wishlist::where('user_id', Auth::id())->count();
            return Response::json([
                'success' => true,
                'message' => 'Product Removed From Wishlist',
                'count' => $wishlist_count
            ]);
        } catch (Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
