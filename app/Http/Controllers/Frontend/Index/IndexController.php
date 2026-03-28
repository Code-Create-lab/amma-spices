<?php

namespace App\Http\Controllers\Frontend\Index;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Video;
use App\Models\Wishlist;
use App\Models\Coupon;
use App\Models\GoogleReview;
use App\Models\Orders;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Blog;

class IndexController extends Controller
{
    public function index()
    {
        $products = Product::with(['variations', 'variations.variation_attributes', 'category'])
            ->where('is_deleted', 0)
            ->whereHas('variations', function ($query) {
                $query->where('is_deleted', 0); // or whatever your availability field is
                // OR if you check stock quantity:
                // $query->where('stock', '>', 0);
            })
            ->whereHas('category', function ($query) {
                $query->where('is_deleted', 0); // or whatever your availability field is
                // OR if you check stock quantity:
                // $query->where('stock', '>', 0);
            })
            ->orderBy('tags', 'desc')  // or 'desc'
            ->orderBy('product_id', 'desc')
            ->take(8)
            ->get();
        $best_seller_products = Product::orderBy('created_at', 'desc')
            ->where('is_deleted', 0)
            ->where('on_sale', 1)
            ->whereHas('variations', function ($query) {
                $query->where('is_deleted', 0); // or whatever your availability field is
                // OR if you check stock quantity:
                // $query->where('stock', '>', 0);
            })
            ->whereHas('category', function ($query) {
                $query->where('is_deleted', 0); // or whatever your availability field is
                // OR if you check stock quantity:
                // $query->where('stock', '>', 0);
            })
            ->get();

        // dd($best_seller_products);
        $categories = Category::where('is_deleted', 0)
            ->where('parent', 0)
            ->where('is_deleted', 0)
            ->with('sub_categories')
            ->get();
        $sub_categories = Category::where('is_deleted', 0)
            ->where('parent', "!=", 1)
            ->where('is_deleted', 0)
            ->with('sub_categories')
            ->get();
        $wishlist = collect();
        $user = auth()->user();
        if ($user) {
            $wishlist = Wishlist::where('user_id', $user->id)->get(); // Get user's wishlist
        }
        $videos = Video::get();
        $banners = Banner::get();
        $coupon = Coupon::where('show_on_web', 1)->first();


        // ── ADD THIS LINE ──────────────────────────────────────
        // Fetch latest 6 published blogs for the homepage carousel
        // (Owl shows 3 at a time; 6 gives a good loop experience)
        $blogs = Blog::published()
            ->latestFirst()
            ->take(6)
            ->get();

        $googleReviews = GoogleReview::where('is_active', 1)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $googleReviewStats = [
            'count' => GoogleReview::where('is_active', 1)->count(),
            'average' => (float) GoogleReview::where('is_active', 1)->avg('rating'),
        ];

            // dd($blogs);
        return view('frontend.index.index', [
            'products' => $products,
            'categories' => $categories,
            'best_seller_products' => $best_seller_products,
            'wishlist' => $wishlist,
            'videos' => $videos,
            'banners' => $banners,
            'coupon' => $coupon,
            'sub_categories' => $sub_categories,
            'blogs' => $blogs,       // ← add this
            'googleReviews' => $googleReviews,
            'googleReviewStats' => $googleReviewStats,
        ]);
    }


    public function generate_invoice($orderId)
    {
        // Eager-load relationships used in the Blade
        $order = Orders::with([
            'address',
            'orderItems.variation.product.tax',
            'orderItems.variation.variation_attributes.attribute.attribute',
        ])->findOrFail($orderId);

        /**
         * 1) INVOICE LOGO -> BASE64
         *    Change the path as per your project
         */
        $logoBase64 = null;
        $logoMime   = null;

        // Example: logo stored in public/images/logo.png
        $logoPath = public_path('images/logo.png');   // <- update this

        if (file_exists($logoPath)) {
            $logoMime   = mime_content_type($logoPath);
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        /**
         * 2) PRODUCT / VARIATION IMAGES -> BASE64
         *    Adjust the image source field as per your DB
         */
        foreach ($order->orderItems as $item) {
            $item->varient_image_base64 = null;
            $item->image_mime_type      = null;

            $imagePath = null;

            // Example 1: image path is stored on variation table (e.g. "products/variants/xxx.jpg")
            if (!empty($item->variation?->image)) {
                $imagePath = Storage::disk('public')->path($item->variation->image);
            }

            // Example 2 (optional): fallback to product main image if you have it
            // if (!$imagePath && !empty($item->variation?->product?->image)) {
            //     $imagePath = Storage::disk('public')->path($item->variation->product->image);
            // }

            if ($imagePath && file_exists($imagePath)) {
                $item->image_mime_type      = mime_content_type($imagePath);
                $item->varient_image_base64 = base64_encode(file_get_contents($imagePath));
            }
        }

        /**
         * 3) Generate PDF with base64 images
         */
        $pdf = Pdf::loadView('frontend.order.invoice', [
            'order'      => $order,
            'logoBase64' => $logoBase64,
            'logoMime'   => $logoMime,
        ]);

        return $pdf->download(' ORD-' . now()->format('Y') . '-' . $order->cart_id . '.pdf');
    }


    public function gallery()
    {
        // Fetch images for the gallery
        // $images = GalleryImage::all();

        return view('frontend.index.gallery');
    }
}
