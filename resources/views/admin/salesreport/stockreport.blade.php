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
                    {{ session()->get('success') }}
                </div>
            @endif
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Stock Report</h4>
                </div>
                <div class="card-header card-header-secondary">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label>Category</label>
                                <select id="categoryFilter" class="form-control">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->cat_id }}" {{ request('category') == $cat->cat_id ? 'selected' : '' }}>
                                            {{ $cat->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button id="filterBtn" class="btn btn-primary">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a id="exportBtn" href="{{ route('stock_report.export', ['category' => request('category')]) }}" class="btn btn-success">
                                Export to Excel
                            </a>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="container"><br>
                    <table id="datatableDefault" class="table text-nowrap w-100 table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Product Name</th>
                                <th class="text-center">Variation</th>
                                <th class="text-center">Category</th>
                                <th class="text-center">Stock Qty</th>
                                <th class="text-center">Ordered Qty</th>
                                <th class="text-center">Available Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($stockData) > 0)
                                @php $i = 1; @endphp
                                @foreach ($stockData as $row)
                                    <tr>
                                        <td class="text-center">{{ $i }}</td>
                                        <td class="text-center">{{ $row->product_name }}</td>
                                        <td class="text-center">{{ $row->uuid }}</td>
                                        <td class="text-center">{{ $row->cat_name }}</td>
                                        <td class="text-center">{{ $row->stock }}</td>
                                        <td class="text-center">{{ $row->total_ordered }}</td>
                                        <td class="text-center">{{ $row->stock }}</td>
                                    </tr>
                                    @php $i++; @endphp
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center">No data found</td>
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
    <script>
        $('#datatableDefault').DataTable({
            dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8 text-right'f>>t<'d-flex align-items-center'<'mr-auto'i><'mb-0'p>>",
            responsive: true,
            pageLength: 10,
            autoWidth: true,
            select: true,
            scrollX: true,
            processing: true,
            ordering: true,
            paging: true,
        });

        $('#filterBtn').on('click', function () {
            var categoryId = $('#categoryFilter').val();
            var url = "{{ route('stock_report') }}";
            if (categoryId) {
                url += '?category=' + categoryId;
            }
            window.location.href = url;
        });

        $('#categoryFilter').on('change', function () {
            var categoryId = $(this).val();
            var exportUrl = "{{ route('stock_report.export') }}";
            if (categoryId) {
                exportUrl += '?category=' + categoryId;
            }
            $('#exportBtn').attr('href', exportUrl);
        });
    </script>
@endsection
