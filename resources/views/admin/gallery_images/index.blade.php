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
                    <h1 class="card-title"><b>Gallery Images</b></h1>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('admin.gallery-images.create') }}" class="btn btn-success">Add Gallery Image</a>
                </div>
            </div>
        </div>
        <div class="container py-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($galleryImages as $galleryImage)
                        <tr>
                            <td>{{ $galleryImage->id }}</td>
                            <td>
                                <img
                                    src="{{ $galleryImage->image_url }}"
                                    alt="{{ $galleryImage->title }}"
                                    style="width: 100px; height: 70px; object-fit: cover; border-radius: 8px;">
                            </td>
                            <td>{{ $galleryImage->title }}</td>
                            <td>{{ $galleryImage->category_name }}</td>
                            <td>{{ $galleryImage->sort_order }}</td>
                            <td>
                                <span class="badge {{ $galleryImage->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $galleryImage->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.gallery-images.edit', $galleryImage->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('admin.gallery-images.destroy', $galleryImage->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this gallery image?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No gallery images added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $galleryImages->links() }}
        </div>
    </div>
</div>
@endsection
