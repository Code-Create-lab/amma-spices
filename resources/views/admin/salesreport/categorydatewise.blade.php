@extends('admin.layout.app')
@section('preload-section')
    <style>
        .collo {
            overflow-y: hidden;
            overflow-x: scroll;
            -webkit-overflow-scrolling: touch;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
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

                <div class="card">
                    <div class="card-header card-header-primary">
                        <div class="row">
                            <div class="col-md-6">
                                <h1 class="card-title"><b>{{ $title }}</b></h1>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('category_sales') }}" class="btn btn-danger p-1 ml-auto"
                                    style="width:15%;float:right;padding: 3px 0px 3px 0px;">{{ __('keywords.Back') }}</a>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="container"> <br>
                        <table id="datatableDefault" class="table text-nowrap w-100 table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">{{ __('Category') }}</th>
                                    <th class="text-center">{{ __('Total orders') }}</th>
                                    <th class="text-center">{{ __('Total amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if (count($category) > 0)
                                    @php $i=1; @endphp
                                    @foreach ($category as $u)
                                        <tr>
                                            <td class="text-center">{{ $i }}</td>
                                            <td class="text-center">{{ $u->title }}</td>
                                            <td class="text-center">{{ $u->cnt }}</td>
                                            <td class="text-center">{{ $u->amount }}</td>
                                        </tr>
                                        @php $i++; @endphp
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ __('keywords.No data found') }}</td>
                                        @for ($i = 1; $i < 10; $i++)
                                            <td style="display: none">
                                            </td>
                                        @endfor
                                    </tr>
                                @endif
                            </tbody>
                        </table><br />

                    </div>
                </div>

            </div>
        </div>

    @endsection

    @section('postload-section')
        {{-- You must include files that have no direct efect on the load of the page and can be loaded meanwhile other tasks can be performed by user --}}
        <script>
            $('#datatableDefault').DataTable({
                dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8 text-right'<'d-flex justify-content-end'fB>>>t<'d-flex align-items-center'<'mr-auto'i><'mb-0'p>>",
                responsive: true,
                // lengthMenu: false,
                // lengthChange: false,
                // lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                // pageLength: 25,
                pageLength: 10,
                autoWidth: true,
                select: true,
                scrollX: true,
                processing: true,
                ordering: true,
                paging: true,
                buttons: [
                    // { extend: 'print', className: 'btn btn-default' },
                    {
                        extend: 'csv',
                        className: 'btn btn-default',
                        exportOptions: {
                            modifier: {
                                page: 'all',
                                search: 'applied',
                                order: 'applied'
                            }
                        }
                    }
                ]
            });
            table.buttons().container()
                .appendTo('#example_wrapper .col-md-6:eq(0)');
        </script>
    @endsection
