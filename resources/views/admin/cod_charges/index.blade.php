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
                                <h1 class="card-title"><b>COD Charge {{ __('keywords.List') }}</b></h1>
                            </div>
                        </div>
                    </div><br>
                    <div class="container">
                        <table id="datatableDefault" class="table text-nowrap w-100 table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>COD Charge</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($codCharges as $index => $codCharge)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>₹{{ number_format($codCharge->cod_charge, 2) }}</td>
                                        <td>{{ $codCharge->status == '1' ? 'Active' : 'Inactive' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('cod-charges.edit', $codCharge->id) }}"
                                                class="btn btn-sm btn-success"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No COD Charge Config Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
