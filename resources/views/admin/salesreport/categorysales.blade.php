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

                <div class="card-header card-header-primary">
                    <h4 class="card-title ">{{ __('keywords.Order List') }}</h4>
                </div>
                <div class="card-header card-header-secondary">
                    <form class="forms-sample" action="{{ route('category_datewise_orders') }}" method="get"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('keywords.From Date') }}</label>
                                    <input type="date" name="sel_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('keywords.To Date') }}</label>
                                    <input type="date" name="to_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-2"><br>
                                <div class="form-group">
                                    <label></label><br>
                                    <button type="submit" class="btn btn-primary">{{ __('keywords.Show Orders') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
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
