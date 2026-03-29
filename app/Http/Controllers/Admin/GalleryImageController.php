<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryImageController extends Controller
{
    public function index()
    {
        $title = 'Gallery Images';
        $galleryImages = GalleryImage::ordered()->paginate(15);

        return view('admin.gallery_images.index', compact('galleryImages', 'title'));
    }

    public function create()
    {
        $title = 'Add Gallery Image';

        return view('admin.gallery_images.create', compact('title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category_name' => 'required|string|max:100',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'required|boolean',
        ]);

        $data['category_slug'] = $this->makeCategorySlug($data['category_name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['image'] = $request->file('image')->store('gallery', 'public');

        GalleryImage::create($data);

        return redirect()
            ->route('admin.gallery-images.index')
            ->with('success', 'Gallery image added successfully.');
    }

    public function edit($id)
    {
        $title = 'Edit Gallery Image';
        $galleryImage = GalleryImage::findOrFail($id);

        return view('admin.gallery_images.edit', compact('galleryImage', 'title'));
    }

    public function update(Request $request, $id)
    {
        $galleryImage = GalleryImage::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category_name' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'required|boolean',
        ]);

        $data['category_slug'] = $this->makeCategorySlug($data['category_name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($galleryImage->image);
            $data['image'] = $request->file('image')->store('gallery', 'public');
        }

        $galleryImage->update($data);

        return redirect()
            ->route('admin.gallery-images.index')
            ->with('success', 'Gallery image updated successfully.');
    }

    public function destroy($id)
    {
        $galleryImage = GalleryImage::findOrFail($id);

        $this->deleteStoredImage($galleryImage->image);
        $galleryImage->delete();

        return redirect()
            ->route('admin.gallery-images.index')
            ->with('success', 'Gallery image deleted successfully.');
    }

    protected function makeCategorySlug(string $categoryName): string
    {
        return Str::slug($categoryName) ?: 'gallery';
    }

    protected function deleteStoredImage(?string $imagePath): void
    {
        if (!$imagePath || Str::startsWith($imagePath, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($imagePath);
    }
}
