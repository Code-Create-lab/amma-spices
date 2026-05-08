@extends('admin.layout.app', ['title' => 'Banners'])

@section('preload-section')
    <style>
        .banner-list-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .banner-list-card .card-header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            color: #0f172a;
            padding: 18px 20px !important;
        }

        .banner-page-title {
            color: #0f172a;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
        }

        .banner-page-subtitle {
            color: #64748b;
            font-size: 13px;
            margin: 5px 0 0;
        }

        .banner-add-btn {
            align-items: center;
            border-radius: 7px;
            display: inline-flex;
            font-weight: 700;
            gap: 7px;
            min-height: 36px;
            padding: 8px 14px !important;
        }

        .banner-table-wrap {
            padding: 18px 20px 10px;
        }

        .banner-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: 760px;
        }

        .banner-table thead th {
            background: #f8fafc !important;
            border-bottom: 1px solid #e5e7eb !important;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .banner-table tbody td {
            border-top: 1px solid #edf2f7;
            color: #334155;
            vertical-align: middle;
        }

        .banner-table tbody tr {
            transition: background-color 180ms ease;
        }

        .banner-table tbody tr:hover {
            background: #f8fafc;
        }

        .banner-media-cell {
            min-width: 430px;
            white-space: normal;
        }

        .banner-preview-grid {
            align-items: stretch;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .banner-preview {
            background: #f8fafc;
            border: 1px solid #dbe4ee;
            border-radius: 8px;
            color: #0f172a;
            cursor: pointer;
            display: block;
            overflow: hidden;
            padding: 8px;
            position: relative;
            text-decoration: none;
            transition: border-color 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        }

        .banner-preview:hover,
        .banner-preview:focus {
            background: #fff;
            border-color: #94a3b8;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.12);
            color: #0f172a;
            outline: none;
            text-decoration: none;
        }

        .banner-preview-desktop {
            width: 260px;
        }

        .banner-preview-mobile {
            width: 104px;
        }

        .banner-preview-missing {
            cursor: default;
        }

        .banner-preview-missing:hover,
        .banner-preview-missing:focus {
            background: #f8fafc;
            border-color: #dbe4ee;
            box-shadow: none;
        }

        .banner-preview-frame {
            align-items: center;
            background: #fff;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            overflow: hidden;
            width: 100%;
        }

        .banner-preview-frame-desktop {
            aspect-ratio: 1920 / 800;
        }

        .banner-preview-frame-mobile {
            aspect-ratio: 900 / 1200;
        }

        .banner-preview img {
            display: block;
            height: 100%;
            object-fit: contain;
            width: 100%;
        }

        .banner-preview-label {
            color: #64748b;
            display: block;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .banner-preview-empty {
            align-items: center;
            background: #eef2f7;
            border: 1px dashed #cbd5e1;
            border-radius: 5px;
            color: #64748b;
            display: flex;
            font-size: 12px;
            font-weight: 600;
            min-height: 100%;
            justify-content: center;
            padding: 8px;
            text-align: center;
            width: 100%;
        }

        .banner-status {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-weight: 700;
            gap: 7px;
            padding: 7px 10px;
        }

        .banner-status-dot {
            border-radius: 999px;
            display: inline-block;
            height: 7px;
            width: 7px;
        }

        .banner-status-active {
            background: #dcfce7;
            color: #166534;
        }

        .banner-status-active .banner-status-dot {
            background: #22c55e;
        }

        .banner-status-hidden {
            background: #e2e8f0;
            color: #475569;
        }

        .banner-status-hidden .banner-status-dot {
            background: #94a3b8;
        }

        .banner-actions {
            white-space: nowrap;
        }

        .banner-action-btn {
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #475569;
            cursor: pointer;
            display: inline-flex;
            height: 34px;
            justify-content: center;
            margin-right: 6px;
            transition: background-color 180ms ease, border-color 180ms ease, color 180ms ease;
            width: 34px;
        }

        .banner-action-btn:hover,
        .banner-action-btn:focus {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1d4ed8;
            outline: none;
            text-decoration: none;
        }

        .banner-action-danger:hover,
        .banner-action-danger:focus {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }

        @media (max-width: 575.98px) {
            .banner-list-card .card-header {
                padding: 16px !important;
            }

            .banner-table-wrap {
                padding: 14px 14px 8px;
            }

            .banner-page-actions {
                margin-top: 12px;
                width: 100%;
            }

            .banner-add-btn {
                justify-content: center;
                width: 100%;
            }

            .banner-media-cell {
                min-width: 320px;
            }

            .banner-preview-desktop {
                width: 200px;
            }

            .banner-preview-mobile {
                width: 80px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .banner-table tbody tr,
            .banner-preview,
            .banner-action-btn {
                transition: none;
            }
        }
    </style>
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
                <div class="card banner-list-card">
                    <div class="card-header card-header-primary">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h1 class="banner-page-title">{{ __('keywords.App Banners') }} {{ __('keywords.List') }}</h1>
                                <p class="banner-page-subtitle mb-0">Desktop and mobile banner previews use their real display shape.</p>
                            </div>
                            <div class="col-md-5 text-md-right banner-page-actions">
                                <a href="{{ route('banners.create') }}" class="btn btn-primary banner-add-btn">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                    <span>{{ __('keywords.Add') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="banner-table-wrap">
                        <table id="datatableDefaultBanners" class="table table-hover banner-table w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('keywords.Banner Image') }}</th>
                                    <th>Visibility</th>
                                    <th>{{ __('keywords.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($banners as $banner)
                                    @php
                                        $desktopBannerUrl = $banner->image ? asset('storage/' . ltrim($banner->image, '/')) : null;
                                        $mobileBannerUrl = $banner->mobile_banner ? asset('storage/' . ltrim($banner->mobile_banner, '/')) : null;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $banners->firstItem() + $loop->index }}</td>
                                        <td class="banner-media-cell">
                                            <div class="banner-preview-grid">
                                                @if ($desktopBannerUrl)
                                                    <a href="{{ $desktopBannerUrl }}" class="banner-preview banner-preview-desktop" target="_blank" rel="noopener" title="Open desktop banner">
                                                        <span class="banner-preview-label">Desktop</span>
                                                        <span class="banner-preview-frame banner-preview-frame-desktop">
                                                            <img src="{{ $desktopBannerUrl }}" alt="Desktop banner {{ $loop->iteration }}" loading="lazy">
                                                        </span>
                                                    </a>
                                                @else
                                                    <div class="banner-preview banner-preview-desktop banner-preview-missing">
                                                        <span class="banner-preview-label">Desktop</span>
                                                        <span class="banner-preview-frame banner-preview-frame-desktop">
                                                            <span class="banner-preview-empty">No banner</span>
                                                        </span>
                                                    </div>
                                                @endif

                                                @if ($mobileBannerUrl)
                                                    <a href="{{ $mobileBannerUrl }}" class="banner-preview banner-preview-mobile" target="_blank" rel="noopener" title="Open mobile banner">
                                                        <span class="banner-preview-label">Mobile</span>
                                                        <span class="banner-preview-frame banner-preview-frame-mobile">
                                                            <img src="{{ $mobileBannerUrl }}" alt="Mobile banner {{ $loop->iteration }}" loading="lazy">
                                                        </span>
                                                    </a>
                                                @else
                                                    <div class="banner-preview banner-preview-mobile banner-preview-missing">
                                                        <span class="banner-preview-label">Mobile</span>
                                                        <span class="banner-preview-frame banner-preview-frame-mobile">
                                                            <span class="banner-preview-empty">Not set</span>
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($banner->is_visible)
                                                <span class="banner-status banner-status-active">
                                                    <span class="banner-status-dot" aria-hidden="true"></span>
                                                    Active
                                                </span>
                                            @else
                                                <span class="banner-status banner-status-hidden">
                                                    <span class="banner-status-dot" aria-hidden="true"></span>
                                                    Hidden
                                                </span>
                                            @endif
                                        </td>
                                        <td class="banner-actions">
                                            <a href="{{ route('banners.edit', $banner->uuid) }}" rel="tooltip" class="banner-action-btn" aria-label="Edit banner">
                                                <i class="fa fa-edit" aria-hidden="true"></i>
                                            </a>
                                            <a href="{{ route('banners.delete', $banner->uuid) }}" rel="tooltip"
                                                class="banner-action-btn banner-action-danger" aria-label="Delete banner"
                                                onclick="return confirm('Are You sure!')">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">{{ __('keywords.No data found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="pull-right mb-1" style="float: right;">
                            {{ $banners->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('postload-section')
    {{-- You must include files that have no direct efect on the load of the page and can be loaded meanwhile other tasks can be performed by user --}}
    <script>
        $('#datatableDefaultBanners').DataTable({
            // dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8 text-right'<'d-flex justify-content-end'fB>>>t<'d-flex align-items-center'<'mr-auto'i><'mb-0'p>>",
            responsive: true,
            lengthMenu: false,
            autoWidth: false,
            select: true,
            scrollX: true,
            processing: true,
            searching: false,
            ordering: true,
            paging: false,
            columnDefs: [
                { targets: 0, width: '60px', className: 'text-center align-middle' },
                { targets: 1, width: '460px', orderable: false },
                { targets: 2, width: '130px', className: 'align-middle' },
                { targets: 3, width: '110px', orderable: false, className: 'align-middle text-center' }
            ],
            buttons: [
                {
                    extend: 'csv',
                    className: 'btn btn-default'
                }
            ]
        });
    </script>
@endsection
