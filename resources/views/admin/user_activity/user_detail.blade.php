@extends('admin.layout.app')

@section('preload-section')
    <style>
        .ua-stat-card { border-left: 4px solid #2a78d6; }
        .ua-stat-value { font-size: 22px; font-weight: 700; color: #0b0b0b; line-height: 1.2; }
        .ua-stat-label { font-size: 12px; color: #52514e; text-transform: uppercase; letter-spacing: .5px; }
    </style>
@endsection

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Activity — {{ $user->name ?: 'User #' . $user->id }}</h4>
            <a href="{{ route('admin.activity.sessions') }}" class="btn btn-outline-secondary btn-sm">Back to Sessions</a>
        </div>

        {{-- User info --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-3"><strong>Name:</strong> {{ $user->name ?: '—' }}</div>
                    <div class="col-md-3"><strong>Phone:</strong> {{ $user->user_phone ?? '—' }}</div>
                    <div class="col-md-3"><strong>Email:</strong> {{ $user->email ?: '—' }}</div>
                    <div class="col-md-3"><strong>Preferred Device:</strong> {{ $topDevice ? ucfirst($topDevice) : '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="row">
            @php
                $statCards = [
                    ['label' => 'Total Visits', 'value' => number_format($sessionStats->total_sessions)],
                    ['label' => 'Total Time on Site', 'value' => activity_duration_format($sessionStats->total_time)],
                    ['label' => 'Pages Viewed', 'value' => number_format($sessionStats->total_page_views)],
                    ['label' => 'Products Viewed', 'value' => number_format($eventCounts['product_view'] ?? 0)],
                    ['label' => 'Searches', 'value' => number_format($eventCounts['search'] ?? 0)],
                    ['label' => 'Add to Carts', 'value' => number_format($eventCounts['add_to_cart'] ?? 0)],
                    ['label' => 'Checkouts Started', 'value' => number_format($eventCounts['checkout_start'] ?? 0)],
                    ['label' => 'Orders Placed', 'value' => number_format($eventCounts['order_placed'] ?? 0)],
                ];
            @endphp
            @foreach ($statCards as $stat)
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card ua-stat-card h-100">
                        <div class="card-body py-3">
                            <div class="ua-stat-label">{{ $stat['label'] }}</div>
                            <div class="ua-stat-value">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            {{-- Most viewed products --}}
            <div class="col-lg-7 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Most Viewed Products</h5></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                                <tr><th>Product</th><th>Views</th><th>Avg Time</th><th>Last Viewed</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($topProducts as $product)
                                    <tr>
                                        <td class="text-truncate" style="max-width: 260px;">{{ $product->product_name }}</td>
                                        <td>{{ number_format($product->views) }}</td>
                                        <td>{{ activity_duration_format($product->avg_time) }}</td>
                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($product->last_viewed)->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No product views recorded</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent searches --}}
            <div class="col-lg-5 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Recent Searches</h5></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                                <tr><th>Query</th><th>When</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($searches as $search)
                                    <tr>
                                        <td>"{{ $search->search_query }}"</td>
                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($search->created_at)->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-3">No searches recorded</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sessions --}}
        <div class="card mb-3">
            <div class="card-header card-header-primary">
                <h5 class="card-title mb-0">Recent Sessions (up to 25)</h5>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped mb-0">
                    <thead class="thead-light">
                        <tr><th>Started</th><th>Device</th><th>Landing Page</th><th>Pages</th><th>Duration</th><th>Journey</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($session->started_at)->format('d M Y, h:i A') }}</td>
                                <td>{{ ucfirst($session->device_type) }} · {{ $session->browser }}</td>
                                <td class="text-truncate" style="max-width: 240px;">{{ $session->landing_page }}</td>
                                <td>{{ $session->page_views }}</td>
                                <td>{{ activity_duration_format($session->duration_seconds) }}</td>
                                <td><a href="{{ route('admin.activity.session.detail', $session->id) }}" class="btn btn-primary btn-sm">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No sessions recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
