@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">

        <div class="card">
            <div class="card-header card-header-primary d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Visitor Sessions</h4>
                <a href="{{ route('admin.activity.dashboard') }}" class="btn btn-light btn-sm">Back to Dashboard</a>
            </div>

            <div class="card-header card-header-secondary">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-2">
                            <label>From Date</label>
                            <input type="date" id="from_date" class="form-control" value="{{ now()->subDays(6)->toDateString() }}">
                        </div>
                        <div class="col-md-2">
                            <label>To Date</label>
                            <input type="date" id="to_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-2">
                            <label>Device</label>
                            <select id="device_type" class="form-control">
                                <option value="">All</option>
                                <option value="desktop">Desktop</option>
                                <option value="mobile">Mobile</option>
                                <option value="tablet">Tablet</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Visitor Type</label>
                            <select id="visitor_type" class="form-control">
                                <option value="">All</option>
                                <option value="registered">Registered Users</option>
                                <option value="guest">Guests</option>
                            </select>
                        </div>
                        <div class="col-md-2 mt-4">
                            <button type="button" id="filter" class="btn btn-primary">Apply Filters</button>
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
                            <th>Started</th>
                            <th>Visitor</th>
                            <th>Device</th>
                            <th>Source</th>
                            <th>Landing Page</th>
                            <th>Pages</th>
                            <th>Duration</th>
                            <th>Journey</th>
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
                    url: "{{ route('admin.activity.sessions') }}",
                    data: function (d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.device_type = $('#device_type').val();
                        d.visitor_type = $('#visitor_type').val();
                    }
                },
                dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'started_at' },
                    { data: 'user_label', orderable: false },
                    { data: 'device_label', orderable: false },
                    { data: 'source', orderable: false },
                    { data: 'landing_page', orderable: false },
                    { data: 'page_views' },
                    { data: 'duration_seconds' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            $('#filter').click(function () {
                table.draw();
            });
        });
    </script>
@endsection
