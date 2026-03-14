@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
        @if (session()->has('success'))
            <div class="alert alert-success">
            @if(is_array(session()->get('success')))
            <ul>
                @foreach (session()->get('success') as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
            @else
                {{ session()->get('success') }}
            @endif
            </div>
        @endif
        
        @if (count($errors) > 0)
            @if($errors->any())
            <div class="alert alert-danger" role="alert">
                {{$errors->first()}}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
        @endif
        </div>
        <div class="col-lg-12">
            <div class="card">    
                <div class="card-header card-header-primary">
                <div class="row">
                    <div class="col-md-6">
                    <h1 class="card-title"><b>Review Rating {{ __('keywords.List')}}</b></h1>
                    </div>
                    
                </div>
                </div><br>
                <div class="container">   
                    <table id="datatableDefault" class="table text-nowrap w-100 table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Product</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Approved</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                                <tr>
                                    <td>{{ $review->id }}</td>
                                    <td>{{ $review->user->name ?? 'N/A' }}</td>
                                    <td><a href="{{ route('single.product.view', $review->product->slug) }}">{{ $review->product->product_name }}</a></td>
                                    <td>
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $review->rating)
                                                <i class="fa fa-star text-warning"></i>
                                            @else
                                                <i class="fa fa-star text-secondary"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-1">({{ $review->rating }}/5)</span>
                                    </td>                                    
                                    <td>{{ Str::limit($review->comment, 50) }}</td>
                                    <td>
                                        @if($review->is_approved == 1)
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($review->is_approved == 2)
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $review->created_at->format('d M, Y') }}</td>
                                    <td>
                                        <form action="{{ route('reviews.approve', $review->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>

                                        <form action="{{ route('reviews.reject', $review->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">Reject</button>
                                        </form>

                                        <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" style="display:inline-block;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No reviews found</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $reviews->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
