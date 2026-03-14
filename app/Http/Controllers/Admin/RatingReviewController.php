<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RatingReview;
use App\Models\StoreOrders;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageStoragePicker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RatingReviewController extends Controller
{
        use ImageStoragePicker;

    public function addReview(Request $request, $orderItemId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'please fill all required fields');
        }

        try {
            DB::beginTransaction();

            $orderItem = StoreOrders::with('order', 'review', 'variation')->findOrFail($orderItemId);

            if ($orderItem->order->user_id !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }

            if ($orderItem->review) {
                return back()->with('error', 'You have already reviewed this product.');
            }

            RatingReview::create([
                'order_item_id' => $orderItem->store_order_id,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->review,
                'product_id' => $orderItem->variation->product_id,
                'is_approved' => 0,
            ]);

            DB::commit();

            return back()->with('success', 'Review submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review Submission Error: ' . $e->getMessage());

            return back()->with('error', 'Something went wrong while submitting your review.');
        }
    }


    // List all reviews
    public function index()
    {
        $reviews = RatingReview::with(['user', 'product'])
            ->orderByDesc('created_at')
            ->paginate(15);

            // print_r($reviews); exit;
        $title = "Review Rating";
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $url_aws = $this->getImageStorage();

        return view('admin.reviews.index', compact('reviews', 'title', "admin", "logo", "url_aws"));
    }

    // Approve review
    public function approve($id)
    {
        $review = RatingReview::findOrFail($id);
        $review->is_approved = 1;
        $review->save();

        return redirect()->back()->with('success', 'Review approved successfully.');
    }

    // Reject review
    public function reject($id)
    {
        $review = RatingReview::findOrFail($id);
        $review->is_approved = 2;
        $review->save();

        return redirect()->back()->with('success', 'Review rejected successfully.');
    }

    // Soft delete review
    public function destroy($id)
    {
        $review = RatingReview::findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Review deleted successfully.');
    }
}
