@extends('admin.layout.app', ['title' => 'COD Charge Edit'])

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
                    <div class="card-header">Edit COD Charge</div>
                    <div class="card-body">
                        <form action="{{ route('cod-charges.update', $codCharge->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>COD Charge (₹)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="cod_charge"
                                    value="{{ $codCharge->cod_charge }}" required>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="1" {{ $codCharge->status == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $codCharge->status == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('cod-charges.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')

    @endpush
@endsection
