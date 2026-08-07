@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">

        <div class="card">
            <div class="card-header card-header-primary d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Product Analytics</h4>
                <a href="{{ route('admin.activity.dashboard') }}" class="btn btn-light btn-sm">Back to Dashboard</a>
            </div>

            <div class="card-header card-header-secondary">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <label>From Date</label>
                            <input type="date" id="from_date" class="form-control" value="{{ now()->subDays(6)->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <label>To Date</label>
                            <input type="date" id="to_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-3 mt-4">
                            <button type="button" id="filter" class="btn btn-primary">Apply</button>
                        </div>
                    </div>
                </form>
            </div>

            <hr>

            <div class="container-fluid">
                <table id="datatableDefault" class="table table-striped text-nowrap w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Views</th>
                            <th>Unique Viewers</th>
                            <th>Avg Time on Page</th>
                            <th>Added to Cart</th>
                            <th>Wishlisted</th>
                            <th>View &rarr; Cart</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('postload-section')
    <script>
        $(function () {
            let table = $('#datatableDefault').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                order: [],
                ajax: {
                    url: "{{ route('admin.activity.products') }}",
                    data: function (d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                    }
                },
                dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'product_name' },
                    { data: 'views' },
                    { data: 'unique_viewers' },
                    { data: 'avg_time' },
                    { data: 'carts' },
                    { data: 'wishlists' },
                    { data: 'conversion', orderable: false, searchable: false }
                ]
            });

            $('#filter').click(function () {
                table.draw();
            });
        });
    </script>
@endsection
