@csrf

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Reviewer Name</label>
            <input type="text" name="reviewer_name" class="form-control" value="{{ old('reviewer_name', $review->reviewer_name ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Reviewer Role</label>
            <input type="text" name="reviewer_role" class="form-control" value="{{ old('reviewer_role', $review->reviewer_role ?? '') }}" placeholder="e.g. Repeat customer">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Rating</label>
            <select name="rating" class="form-control" required>
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ (int) old('rating', $review->rating ?? 5) === $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $review->sort_order ?? 0) }}" min="0">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Status</label>
            <select name="is_active" class="form-control" required>
                <option value="1" {{ (int) old('is_active', isset($review) ? (int) $review->is_active : 1) === 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ (int) old('is_active', isset($review) ? (int) $review->is_active : 1) === 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Review Text</label>
    <textarea name="review_text" class="form-control" rows="6" required>{{ old('review_text', $review->review_text ?? '') }}</textarea>
</div>

<button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
<a href="{{ route('admin.google-reviews.index') }}" class="btn btn-secondary">Cancel</a>
