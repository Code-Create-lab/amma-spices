<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {

        $title = "Blog List";
        // 
        $blogs = Blog::latest()->paginate(20);
        return view('admin.blogs.index', compact('blogs', 'title'));
    }

    /**
     * Show create form
     */
    public function create()
    {

        $title = "Blog List";

        return view('admin.blogs.create',  compact('title'));
    }

    /**
     * Store new blog post
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'status'           => 'required|in:draft,published',
            'thumbnail'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category'         => 'nullable|string|max:100',
            'author'           => 'nullable|string|max:100',
            'author_role'      => 'nullable|string|max:255',
            'excerpt'          => 'nullable|string|max:500',
            'read_time'        => 'nullable|integer|min:1|max:60',
            'tags'             => 'nullable|string',
            'published_at'     => 'nullable|date',
            'meta_title'       => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
        ]);

        // ── Build data array ──────────────────────────────
        $data = [
            'title'            => $request->title,
            'slug'             => $this->uniqueSlug($request->slug ?: $request->title),
            'category'         => $request->category,
            'author'           => $request->author           ?: "Amma's Kitchen",
            'author_role'      => $request->author_role      ?: 'Traditional South Indian Recipes & Spice Expert',
            'excerpt'          => $request->excerpt          ?: null,
            'content'          => $request->content,
            'read_time'        => $request->read_time        ?: null,
            'status'           => $request->status,
            'meta_title'       => $request->meta_title       ?: null,
            'meta_description' => $request->meta_description ?: null,
        ];

        // ── Tags: comma string → array ────────────────────
        $data['tags'] = $request->tags
            ? array_values(array_filter(array_map('trim', explode(',', $request->tags))))
            : null;

        // ── Published date ────────────────────────────────
        $data['published_at'] = $request->status === 'published'
            ? ($request->published_at ? \Carbon\Carbon::parse($request->published_at) : now())
            : null;

        // ── Thumbnail upload ──────────────────────────────
        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            $data['thumbnail'] = $request->file('thumbnail')
                ->store('blogs', 'public');
        }

        Blog::create($data);

        return redirect()
            ->route('admin.blog.index')
            ->with('success', 'Blog post ' . ($request->status === 'published' ? 'published' : 'saved as draft') . ' successfully.');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {

        $title = "Blog List";
        $blog = Blog::findOrFail($id);

        // Convert tags array back to comma string for the input field
        $blog->tags_string = $blog->tags ? implode(', ', $blog->tags) : '';

        return view('admin.blogs.edit', compact('blog', 'title'));
    }

    /**
     * Update blog post
     */
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'status'           => 'required|in:draft,published',
            'thumbnail'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category'         => 'nullable|string|max:100',
            'author'           => 'nullable|string|max:100',
            'author_role'      => 'nullable|string|max:255',
            'excerpt'          => 'nullable|string|max:500',
            'read_time'        => 'nullable|integer|min:1|max:60',
            'tags'             => 'nullable|string',
            'published_at'     => 'nullable|date',
            'meta_title'       => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
        ]);

        $data = [
            'title'            => $request->title,
            'slug'             => $this->uniqueSlug($request->slug ?: $request->title, $blog->id),
            'category'         => $request->category,
            'author'           => $request->author           ?: "Amma's Kitchen",
            'author_role'      => $request->author_role      ?: 'Traditional South Indian Recipes & Spice Expert',
            'excerpt'          => $request->excerpt          ?: null,
            'content'          => $request->content,
            'read_time'        => $request->read_time        ?: null,
            'status'           => $request->status,
            'meta_title'       => $request->meta_title       ?: null,
            'meta_description' => $request->meta_description ?: null,
        ];

        $data['tags'] = $request->tags
            ? array_values(array_filter(array_map('trim', explode(',', $request->tags))))
            : null;

        $data['published_at'] = $request->status === 'published'
            ? ($request->published_at ? \Carbon\Carbon::parse($request->published_at) : ($blog->published_at ?? now()))
            : null;

        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            // Delete old thumbnail
            if ($blog->thumbnail) {
                Storage::disk('public')->delete($blog->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')
                ->store('blogs', 'public');
        }

        $blog->update($data);

        return redirect()
            ->route('admin.blog.index')
            ->with('success', 'Blog post updated successfully.');
    }

    /**
     * Soft delete
     */
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        // Delete thumbnail from storage
        if ($blog->thumbnail) {
            Storage::disk('public')->delete($blog->thumbnail);
        }

        $blog->delete();

        return redirect()
            ->route('admin.blog.index')
            ->with('success', 'Blog post deleted successfully.');
    }
 
    // ── PRIVATE HELPERS ───────────────────────────────────

    /**
     * Generate a unique slug, appending -2, -3 etc. if taken.
     * Ignores the current blog's own ID when updating.
     */
    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $i    = 2;

        while (
            Blog::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
