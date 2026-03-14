@extends('admin.layout.app')

@section('preload-section')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection


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
                                <h1 class=""><b>{{ __('keywords.Product') }} {{ __('keywords.List') }}</b></h1>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" id="exportExcel" class="btn btn-success mr-2">
                                    <i class="fa fa-file-excel"></i> Export to Excel
                                </button>
                                <a href="{{ route('AddProduct') }}" class="btn btn-primary p-1"
                                    style="padding: 3px 10px;">{{ __('keywords.Add') }}</a>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="category_filter">{{ __('keywords.Category') }}</label>
                                        <select class="form-control" id="category_filter">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->cat_id }}">{{ $category->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="type_filter">{{ __('keywords.Type') }}</label>
                                        <select class="form-control" id="type_filter">
                                            <option value="">All Types</option>
                                            @foreach ($productTypes as $type)
                                                <option value="{{ $type }}">{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="button" id="filterBtn" class="btn btn-primary">
                                                <i class="fa fa-filter"></i> Apply Filters
                                            </button>
                                            <button type="button" id="clearBtn" class="btn btn-secondary">
                                                <i class="fa fa-refresh"></i> Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container">
                        <table id="datatableDefault" class="table table-striped text-nowrap w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>{{ __('keywords.Product') }} {{ __('keywords.Name') }}</th>
                                    {{-- <th>{{ __('keywords.Product Id') }}</th> --}}
                                    <th>{{ __('keywords.Category') }}</th>
                                    <th>{{ __('keywords.Type') }}</th>
                                    <th>{{ __('keywords.Product') }} {{ __('keywords.Image') }}</th>
                                    <th class="text-right">{{ __('keywords.Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <style>
                mark {
                    background-color: yellow;
                    padding: 0;
                }
            </style>
        </div>
    </div>
    <div>
    </div>
@endsection

@section('postload-section')
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            // Initialize Select2 on dropdowns
            $('#category_filter').select2({
                placeholder: 'All Categories',
                allowClear: true
            });

            $('#type_filter').select2({
                placeholder: 'All Types',
                allowClear: true
            });

            let table = $('#datatableDefault').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('productlist') }}",
                    data: function(d) {
                        d.category_filter = $('#category_filter').val();
                        d.type_filter = $('#type_filter').val();
                    }
                },
                dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8'f>>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'
                    },
                    {
                        data: 'product_name'
                    },
                    // {
                    //     data: 'product_id'
                    // },
                    {
                        data: 'category_title'
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-right'
                    }
                ],
                language: {
                    search: "Search products:",
                    searchPlaceholder: "Name, ID, Category, Type..."
                }
            });

            // Apply filters button
            $('#filterBtn').click(function() {
                table.draw();
            });

            // Clear filters button
            $('#clearBtn').click(function() {
                $('#category_filter').val('').trigger('change');
                $('#type_filter').val('').trigger('change');
                table.draw();
            });

            // Trigger search on category change
            $('#category_filter').on('change', function() {
                table.draw();
            });

            // Trigger search on type change
            $('#type_filter').on('change', function() {
                table.draw();
            });

            // Server-side Excel export
            $('#exportExcel').click(function() {
                let categoryFilter = $('#category_filter').val();
                let typeFilter = $('#type_filter').val();
                let search = $('.dataTables_filter input').val();

                let url = "{{ route('products.export') }}";
                let params = [];

                if (categoryFilter) params.push('category_filter=' + categoryFilter);
                if (typeFilter) params.push('type_filter=' + typeFilter);
                if (search) params.push('search=' + search);

                if (params.length > 0) {
                    url += '?' + params.join('&');
                }

                window.location.href = url;
            });
        });
    </script>
@endsection