@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-header card-header-primary">
            <div class="row">
                <div class="col-md-6">
                    <h1 class="card-title"><b>Homepage Google Reviews</b></h1>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('admin.google-reviews.create') }}" class="btn btn-success">Add Review</a>
                </div>
            </div>
        </div>
        <div class="container py-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>{{ $review->id }}</td>
                            <td>{{ $review->reviewer_name }}</td>
                            <td>{{ $review->reviewer_role ?: 'N/A' }}</td>
                            <td>{{ $review->rating }}/5</td>
                            <td>{{ \Illuminate\Support\Str::limit($review->review_text, 70) }}</td>
                            <td>{{ $review->sort_order }}</td>
                            <td>
                                <span class="badge {{ $review->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $review->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.google-reviews.edit', $review->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('admin.google-reviews.destroy', $review->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No homepage reviews added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
