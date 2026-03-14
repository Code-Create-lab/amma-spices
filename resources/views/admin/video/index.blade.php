@extends('admin.layout.app', ['title' => 'Videos Section'])

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
                        <h4 class="card-title ">{{ __('keywords.Videos') }}</h4>
                        <div class="col-md-6">
                            <a href="{{ route('videos.create') }}" class="btn btn-primary p-1 ml-auto"
                                style="width:15%;float:right;padding: 3px 0px 3px 0px;">{{ __('keywords.Add') }}</a>
                        </div>
                    </div>

                    <div class="card-header card-header-secondary">
                        <form class="forms-sample" action="{{ route('videos.store') }}" method="post"
                            enctype="multipart/form-data">
                            @csrf
                        </form>
                    </div>
                    <hr>
                    <div class="container"><br>
                        <table id="datatableDefault" class="table table-striped text-nowrap w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('keywords.Video') }}</th>
                                    <th>{{ __('keywords.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($videos->isNotEmpty())
                                    @php $i=1; @endphp
                                    @foreach ($videos as $video)
                                        <tr>
                                            <td class="text-center">{{ $i }}</td>
                                            <td>
                                                <video width="320" height="240" controls>
                                                    <source src="{{ asset('storage/'.$video->video) }}" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </td>
                                            <td class="td-actions text-center">
                                                <a href="{{ route('videos.edit', $video->uuid) }}" rel="tooltip">
                                                    <i class="fa fa-edit" style="color:orange;"></i>
                                                </a>&nbsp;
                                                <a href="{{ route('videos.delete', $video->uuid) }}" rel="tooltip"
                                                    onclick="return confirm('Are You sure! It will remove all the addresses & orders related to this User.')">
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
                            {{ $videos->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
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
    @endpush
@endsection
