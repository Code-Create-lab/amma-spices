@extends('admin.layout.app', ['title' => 'Shiiping Fee Edit'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                @if (session()->has('success'))
                    <div class="alert alert-success">
                        @if (is_array(session()->get('success')))
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
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first() }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                @endif
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Edit Shipping</div>
                    <div class="card-body">
                        <form action="{{ route('shippings.update', $shipping->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Shipping Charge (₹)</label>
                                <input type="number" step="0.01" class="form-control" name="shipping_charge" value="{{ $shipping->shipping_charge }}" required>
                            </div>
                            <div class="form-group">
                                <label>Minimum Cart Value (₹)</label>
                                <input type="number" step="0.01" class="form-control" name="minimum_cart_value" value="{{ $shipping->minimum_cart_value }}" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('shippings.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        
    @endpush
@endsection
