@extends('admin.layout.app')

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
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <div class="row">
                            <div class="col-md-6">
                                <h1 class="card-title"><b>Shipping Fee {{ __('keywords.List') }}</b></h1>
                                {{-- <div class="col-md-6">
                                    <a href="{{ route('shippings.create') }}" class="btn btn-primary p-1 ml-auto"
                                        style="width:15%;float:right;padding: 3px 0px 3px 0px;">{{ __('keywords.Add') }}</a>
                                </div> --}}
                            </div>

                        </div>
                    </div><br>
                    <div class="container">
                        <table id="datatableDefault" class="table text-nowrap w-100 table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Shipping Charge</th>
                                    <th>Minimum Cart Value</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shippings as $index => $shipping)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>₹{{ number_format($shipping->shipping_charge, 2) }}</td>
                                        <td>₹{{ number_format($shipping->minimum_cart_value, 2) }}</td>
                                        <td>{{ $shipping->status == '1' ? 'Active' : 'Inactive' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('shippings.edit', $shipping->id) }}"
                                                class="btn btn-sm btn-success"><i class="fa fa-edit"></i></a>
                                            {{-- <form action="{{ route('shippings.destroy', $shipping->id) }}" method="POST" style="display:inline-block;">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                        </form> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No Shipping Config Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- {{ $shippings->links() }} --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
