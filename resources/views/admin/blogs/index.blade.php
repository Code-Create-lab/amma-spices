@extends('admin.layout.app')

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

            <div class="col-lg-12"><br></div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <div class="row">
                            <div class="col-md-6">
                                <h1 class="card-title"><b>Blog List</b></h1>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.blog.create') }}"
                                   class="btn btn-primary p-1 ml-auto"
                                   style="width:15%; float:right; padding: 3px 0px 3px 0px;">
                                   Add
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="container"><br>
                        <table id="datatableDefault" class="table table-striped text-nowrap w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Thumbnail</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Published At</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($blogs) > 0)
                                    @php $i = 1; @endphp
                                    @foreach ($blogs as $blog)
                                        <tr>
                                            <td class="text-center">{{ $i }}</td>

                                            {{-- Thumbnail --}}
                                            <td>
                                                @if ($blog->thumbnail)
                                                    <img src="{{ asset('storage/' . $blog->thumbnail) }}"
                                                         alt="Blog Thumbnail"
                                                         style="width:50px; height:50px; border-radius:6px; object-fit:cover;">
                                                @else
                                                    <div style="width:50px; height:50px; border-radius:6px;
                                                                background:#e9ecef; display:flex;
                                                                align-items:center; justify-content:center;">
                                                        <i class="fa fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>

                                            {{-- Title --}}
                                            <td>
                                                <span title="{{ $blog->title }}">
                                                    {{ Str::limit($blog->title, 40) }}
                                                </span>
                                            </td>

                                            {{-- Category --}}
                                            <td>{{ $blog->category ?? '—' }}</td>

                                            {{-- Author --}}
                                            <td>{{ $blog->author ?? '—' }}</td>

                                            {{-- Status badge --}}
                                            <td>
                                                @if ($blog->status === 'published')
                                                    <span class="badge badge-success">Published</span>
                                                @elseif ($blog->status === 'draft')
                                                    <span class="badge badge-warning">Draft</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($blog->status ?? 'draft') }}</span>
                                                @endif
                                            </td>

                                            {{-- Published date --}}
                                            <td>
                                                {{ $blog->published_at ? \Carbon\Carbon::parse($blog->published_at)->format('d M Y') : '—' }}
                                            </td>

                                            {{-- Actions --}}
                                            <td class="td-actions text-right">
                                                <a href="{{ route('admin.blog.edit', $blog->id) }}"
                                                   rel="tooltip"
                                                   class="btn btn-success btn-sm"
                                                   title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.blog.destroy', $blog->id) }}"
                                                   onClick="return confirm('Are you sure you want to permanently remove this Blog post?')"
                                                   rel="tooltip"
                                                   class="btn btn-danger btn-sm"
                                                   title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @php $i++; @endphp
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fa fa-file-text-o fa-2x mb-2 d-block"></i>
                                            No blog posts found.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        <br>
                        <div class="pull-right mb-1" style="float: right;">
                            {{-- {{ $blogs->render('pagination::bootstrap-4') }} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('postload-section')
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