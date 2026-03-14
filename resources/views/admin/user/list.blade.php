@extends('admin.layout.app')

@section('preload-section')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.js"></script>
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Alerts --}}
    <div class="row">
        <div class="col-lg-12">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ is_array(session('success')) ? implode(',', session('success')) : session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Card --}}
    <div class="card">
        <div class="card-header card-header-primary">
            <h4 class="card-title">{{ __('keywords.App Users') }}</h4>
        </div>

        {{-- Date Filter --}}
        <div class="card-header card-header-secondary">
            <form id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label>{{ __('keywords.From Date') }}</label>
                        <input type="date" id="from_date" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>{{ __('keywords.To Date') }}</label>
                        <input type="date" id="to_date" class="form-control">
                    </div>

                    <div class="col-md-3 mt-4">
                        <button type="button" id="filter"
                            class="btn btn-primary">{{ __('keywords.Show Users') }}</button>
                    </div>

                    <div class="col-md-3 mt-4">
                        <button type="button" id="exportExcel"
                            class="btn btn-success">
                            <i class="fa fa-file-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <hr>

        {{-- Table --}}
        <div class="container-fluid">
            <table id="datatableDefault" class="table table-striped text-nowrap w-100">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('keywords.User Name') }}</th>
                        <th>{{ __('keywords.User Phone') }}</th>
                        <th>{{ __('keywords.User Email') }}</th>
                        <th>{{ __('keywords.Registration Date') }}</th>
                        {{-- <th>{{ __('keywords.Is Verified') }}</th> --}}
                        <th>{{ __('keywords.Active/Block') }}</th>
                        <th>{{ __('keywords.Actions') }}</th>
                        <th>{{ __('keywords.Details') }}</th>
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
        searching: false,
        ajax: {
            url: "{{ route('userlist') }}",
            data: function (d) {
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },
        dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'user_phone' },
            { data: 'email' },
            { data: 'reg_date' },
            // { data: 'is_verified', orderable: false, searchable: false },
            { data: 'block', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false },
            { data: 'details', orderable: false, searchable: false }
        ]
    });

    $('#filter').click(function () {
        table.draw();
    });

    // Server-side Excel export
    $('#exportExcel').click(function () {
        let fromDate = $('#from_date').val();
        let toDate = $('#to_date').val();
        let search = $('.dataTables_filter input').val();

        let url = "{{ route('users.export') }}";
        let params = [];

        if (fromDate) params.push('from_date=' + fromDate);
        if (toDate) params.push('to_date=' + toDate);
        if (search) params.push('search=' + search);

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        window.location.href = url;
    });

});
</script>
@endsection
