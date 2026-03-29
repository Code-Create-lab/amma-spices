@csrf

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $galleryImage->title ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category_name" class="form-control" value="{{ old('category_name', $galleryImage->category_name ?? '') }}" placeholder="e.g. Events" required>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $galleryImage->sort_order ?? 0) }}" min="0">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{ (int) old('is_active', isset($galleryImage) ? (int) $galleryImage->is_active : 1) === 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ (int) old('is_active', isset($galleryImage) ? (int) $galleryImage->is_active : 1) === 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Gallery Image {{ $imageRequired ? '*' : '' }}</label>
            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" {{ $imageRequired ? 'required' : '' }}>
            <small class="form-text text-muted">
                {{ $imageRequired ? 'Upload an image for the gallery card.' : 'Leave blank to keep the current image.' }}
            </small>
        </div>
    </div>
</div>

@isset($galleryImage)
    <div class="form-group">
        <label>Current Image</label>
        <div>
            <img
                src="{{ $galleryImage->image_url }}"
                alt="{{ $galleryImage->title }}"
                style="width: 220px; max-width: 100%; border-radius: 10px; object-fit: cover;">
        </div>
    </div>
@endisset

<button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
<a href="{{ route('admin.gallery-images.index') }}" class="btn btn-secondary">Cancel</a>
