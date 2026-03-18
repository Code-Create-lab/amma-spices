<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Blog listing page — /blog
     * Supports optional ?category= and ?tag= filters from tag/category links
     */
    public function index(Request $request)
    {
        $query = Blog::published()->latestFirst();

        // Filter by category (from category pill links)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by tag (from tag pill links — JSON contains search)
        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        $blogs = $query->paginate(9)->withQueryString();

        return view('frontend.blogs.index', compact('blogs'));
    }

    /**
     * Blog detail page — /blog/{slug}
     * Passes $blog and $related (3 other published posts)
     */
    public function show(string $slug)
    {
        // Find the published blog post — 404 if not found or not published
        $blog = Blog::published()
                    ->where('slug', $slug)
                    ->firstOrFail();

        // Related: same category first, fallback to latest — exclude current post
        $related = Blog::published()
                       ->where('id', '!=', $blog->id)
                       ->when($blog->category, function ($q) use ($blog) {
                           // Prefer same category
                           $q->orderByRaw("CASE WHEN category = ? THEN 0 ELSE 1 END", [$blog->category]);
                       })
                       ->latestFirst()
                       ->take(3)
                       ->get();

        return view('frontend.blogs.show', compact('blog', 'related'));
    }
}