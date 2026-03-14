@extends('admin.layout.app', ['title' => 'Enquiry'])

@section('preload-section')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.js"></script>
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
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
                        <h4 class="card-title ">{{ __('keywords.Enquiries') }}</h4>
                    </div>

                    <div class="container"><br>
                        <table id="datatableDefault" class="table table-striped text-nowrap w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('keywords.Full Name') }}</th>
                                    <th>{{ __('keywords.User Phone') }}</th>
                                    <th>{{ __('keywords.User Email') }}</th>
                                    <th>{{ __('keywords.User Message') }}</th>
                                    <th>{{ __('keywords.Product Detail') }}</th>
                                    <th>{{ __('keywords.GST NO') }}</th>
                                    <th>{{ __('keywords.Created At') }}</th>
                                    <th>{{ __('keywords.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($enquiries->isNotEmpty())
                                    @php $i=1; @endphp
                                    @foreach ($enquiries as $enquiry)
                                        <tr>
                                            <td class="text-center">{{ $i }}</td>
                                            <td>{{ $enquiry->full_name }}</td>
                                            <td>{{ $enquiry->phone_number }}</td>
                                            <td>{{ $enquiry->email }}</td>
                                            <td>{{ $enquiry->message }}</td>
                                            <td>{{ $enquiry->product_detail }}</td>
                                            <td>{{ $enquiry->gst_no }}</td>
                                            <td>{{ \Carbon\Carbon::parse($enquiry->created_at)->format('jS, F Y h:i A') }}
                                            </td>
                                            <td>
                                                <a href="{{ route('enquiry.delete', $enquiry->uuid) }}" rel="tooltip"
                                                    onclick="return confirm('Are You sure!')">
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
                            {{ $enquiries->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
    </div>

    </div>
@endsection

@section('postload-section')
    {{-- You must include files that have no direct efect on the load of the page and can be loaded meanwhile other tasks can be performed by user --}}
    <script>
        $('#datatableDefault').DataTable({
            dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8 text-right'<'d-flex justify-content-end'B>>>t<'d-flex align-items-center'<'mr-auto'i><'mb-0'p>>",
            responsive: true,
            lengthMenu: false,
            autoWidth: true,
            select: true,
            scrollX: true,
            processing: true,
            ordering: true,
            paging: false,
            buttons: [
                {
                    extend: 'csv',
                    className: 'btn btn-default'
                }
            ]
        });
    </script>
@endsection
