<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleReview;
use Illuminate\Http\Request;

class GoogleReviewController extends Controller
{
    public function index()
    {
        $title = 'Homepage Reviews';
        $reviews = GoogleReview::orderBy('sort_order')->orderByDesc('id')->paginate(15);

        return view('admin.google_reviews.index', compact('reviews', 'title'));
    }

    public function create()
    {
        $title = 'Add Homepage Review';

        return view('admin.google_reviews.create', compact('title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reviewer_name' => 'required|string|max:100',
            'reviewer_role' => 'nullable|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'required|boolean',
        ]);

        GoogleReview::create($data);

        return redirect()->route('admin.google-reviews.index')->with('success', 'Homepage review added successfully.');
    }

    public function edit($id)
    {
        $title = 'Edit Homepage Review';
        $review = GoogleReview::findOrFail($id);

        return view('admin.google_reviews.edit', compact('review', 'title'));
    }

    public function update(Request $request, $id)
    {
        $review = GoogleReview::findOrFail($id);

        $data = $request->validate([
            'reviewer_name' => 'required|string|max:100',
            'reviewer_role' => 'nullable|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'required|boolean',
        ]);

        $review->update($data);

        return redirect()->route('admin.google-reviews.index')->with('success', 'Homepage review updated successfully.');
    }

    public function destroy($id)
    {
        $review = GoogleReview::findOrFail($id);
        $review->delete();

        return redirect()->route('admin.google-reviews.index')->with('success', 'Homepage review deleted successfully.');
    }
}
