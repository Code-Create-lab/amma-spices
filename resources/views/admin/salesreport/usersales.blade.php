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
                    <form class="forms-sample" action="{{ route('user_datewise_orders') }}" method="get"
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
                    <div class="mt-2">
                        <a href="{{ route('user_sales.export') }}" class="btn btn-success">
                            Export to Excel
                        </a>
                    </div>
                </div>
                <hr>
                <div class="container"> <br>
                    <table id="datatableDefault" class="table text-nowrap w-100 table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">{{ __('keywords.Name') }}</th>
                                <th class="text-center">{{ __('keywords.Email') }}</th>
                                <th class="text-center">{{ __('keywords.Phone') }}</th>
                                <th class="text-center">{{ __('Total orders') }}</th>
                                <th class="text-center">{{ __('Total amount') }}</th>
                                <th class="text-center">{{ __('Highest Amount') }}</th>
                                <th class="text-center">{{ __('Lowest Amount') }}</th>
                                <th class="text-center">{{ __('keywords.Address') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                            @if (count($user) > 0)
                                @php $i=1; @endphp
                                @foreach ($user as $u)
                                    <tr>
                                        @if ($u->user_phone == '9916870397')
                                            
                                        {{-- @dd($u) --}}
                                        @endif
                                        <td class="text-center">{{ $i }}</td>
                                        <td class="text-center">{{ $u->name != "" ? $u->name : ($u->address[0]->receiver_name ?? null)}}</td>
                                        <td class="text-center">{{ $u->user_phone }}</td>
                                        <td class="text-center">{{ $u->email }}</td>
                                        <td class="text-center">{{ $u->cnt }}</td>
                                        <td class="text-center">{{ round($u->amount, 2) }}</td>
                                        <td class="text-center">{{ round($u->highest, 2) }}</td>
                                        <td class="text-center">{{ round($u->lowest, 2) }}</td>
                                        <td>{{ $u->house_no }},{{ $u->society }},<br>
                                            @if ($u->landmark != null)
                                                {{ $u->landmark }},
                                                <br>
                                            @endif
                                            {{ $u->pincode }}
                                        </td>

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
    </script>
@endsection
