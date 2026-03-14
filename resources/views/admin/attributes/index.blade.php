@extends('admin.layout.app', ['title' => 'Attributes'])

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
                                <h1 class="card-title"><b>{{ __('keywords.Attribute') }} {{ __('keywords.List') }}</b></h1>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('attributes.create') }}" class="btn btn-primary p-1 ml-auto"
                                    style="width:15%;float:right;padding: 3px 0px 3px 0px;">{{ __('keywords.Add') }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="container"><br>
                        <table id="attributes-table" class="table table-striped text-nowrap w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('keywords.Attribute') }} {{ __('keywords.Name') }}</th>
                                    <th>{{ __('keywords.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($attributes->isNotEmpty())
                                    @php $i=1; @endphp
                                    @foreach ($attributes as $attribute)
                                        <tr>
                                            <td class="text-center">{{ $i }}</td>
                                            <td>{{ $attribute->name }}</td>
                                            <td>
                                                <a href="{{ route('attributes.edit', $attribute->uuid) }}" rel="tooltip">
                                                    <i class="fa fa-edit" style="color:orange;"></i>
                                                </a>&nbsp;
                                                <a href="{{ route('attributes.delete', $attribute->uuid) }}" rel="tooltip"
                                                    onclick="return confirm('Are You sure!.')">
                                                    <i class="fa fa-trash" style="color:red;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @php $i++; @endphp
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ __('keywords.No data found') }}</td>
                                        @for ($i = 1; $i < 9; $i++)
                                            <td style="display:none"></td>
                                        @endfor
                                    </tr>
                                @endif
                            </tbody>
                        </table><br />
                        <div class="pull-right mb-1" style="float: right;">
                            {{ $attributes->links('pagination::bootstrap-4') }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            $('#attributes-table').DataTable({
                // dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8 text-right'<'d-flex justify-content-end'fB>>>t<'d-flex align-items-center'<'mr-auto'i><'mb-0'p>>",
                responsive: true,
                // lengthMenu: false,
                autoWidth: true,
                select: true,
                scrollX: true,
                processing: true,
                ordering: true,
                // paging: true,
            });
        </script>
    @endpush
@endsection
