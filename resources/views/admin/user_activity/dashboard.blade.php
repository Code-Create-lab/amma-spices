@extends('admin.layout.app')

@section('preload-section')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .ua-stat-card { border-left: 4px solid #2a78d6; }
        .ua-stat-value { font-size: 26px; font-weight: 700; color: #0b0b0b; line-height: 1.2; }
        .ua-stat-label { font-size: 12px; color: #52514e; text-transform: uppercase; letter-spacing: .5px; }
        .ua-live-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #0ca30c; margin-right: 6px; animation: uaPulse 1.5s infinite; }
        @keyframes uaPulse { 0% { opacity: 1 } 50% { opacity: .35 } 100% { opacity: 1 } }
        .ua-table-sm td, .ua-table-sm th { padding: .45rem .6rem; font-size: 13px; }
        .ua-chart-box { position: relative; height: 300px; }
    </style>
@endsection

@section('content')
    <div class="container-fluid">

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('admin.activity.dashboard') }}" class="form-inline">
                    <label class="mr-2">From</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm mr-3">
                    <label class="mr-2">To</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm mr-3">
                    <button type="submit" class="btn btn-primary btn-sm mr-2">Apply</button>
                    <a href="{{ route('admin.activity.dashboard') }}" class="btn btn-outline-secondary btn-sm mr-3">Last 7 Days</a>
                    <a href="{{ route('admin.activity.dashboard', ['from_date' => now()->toDateString(), 'to_date' => now()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm mr-2">Today</a>
                    <a href="{{ route('admin.activity.dashboard', ['from_date' => now()->subDays(29)->toDateString(), 'to_date' => now()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">Last 30 Days</a>
                </form>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="row">
            @php
                $statCards = [
                    ['label' => 'Live Right Now', 'value' => $cards['live_now'], 'live' => true],
                    ['label' => 'Unique Visitors', 'value' => number_format($cards['unique_visitors'])],
                    ['label' => 'Sessions', 'value' => number_format($cards['sessions'])],
                    ['label' => 'Page Views', 'value' => number_format($cards['page_views'])],
                    ['label' => 'Avg Session Time', 'value' => activity_duration_format($cards['avg_session'])],
                    ['label' => 'Logged-in Visitors', 'value' => number_format($cards['logged_in_visitors'])],
                    ['label' => 'Add to Carts', 'value' => number_format($cards['add_to_carts'])],
                    ['label' => 'Orders Placed', 'value' => number_format($cards['orders_placed'])],
                ];
            @endphp
            @foreach ($statCards as $stat)
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card ua-stat-card h-100">
                        <div class="card-body py-3">
                            <div class="ua-stat-label">{{ $stat['label'] }}</div>
                            <div class="ua-stat-value">
                                @if (!empty($stat['live']))
                                    <span class="ua-live-dot"></span><span id="liveCount">{{ $stat['value'] }}</span>
                                @else
                                    {{ $stat['value'] }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Charts --}}
        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Visitors &amp; Page Views</h5></div>
                    <div class="card-body">
                        <div class="ua-chart-box"><canvas id="uaTrendChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Devices</h5></div>
                    <div class="card-body">
                        <div class="ua-chart-box"><canvas id="uaDeviceChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top pages / top products --}}
        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Top Pages</h5></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped ua-table-sm mb-0">
                            <thead class="thead-light">
                                <tr><th>Page</th><th>Views</th><th>Unique</th><th>Avg Time</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($topPages as $page)
                                    <tr>
                                        <td class="text-truncate" style="max-width: 260px;">{{ $page->page_url }}</td>
                                        <td>{{ number_format($page->views) }}</td>
                                        <td>{{ number_format($page->unique_visitors) }}</td>
                                        <td>{{ activity_duration_format($page->avg_time) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No data for this period</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Top Products</h5>
                        <a href="{{ route('admin.activity.products') }}" class="btn btn-outline-primary btn-sm">Full Report</a>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped ua-table-sm mb-0">
                            <thead class="thead-light">
                                <tr><th>Product</th><th>Views</th><th>Avg Time</th><th>Carts</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($topProducts as $product)
                                    <tr>
                                        <td class="text-truncate" style="max-width: 260px;">{{ $product->product_name }}</td>
                                        <td>{{ number_format($product->views) }}</td>
                                        <td>{{ activity_duration_format($product->avg_time) }}</td>
                                        <td>{{ number_format($product->carts) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No data for this period</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sources / recent activity --}}
        <div class="row">
            <div class="col-lg-5 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Traffic Sources</h5></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped ua-table-sm mb-0">
                            <thead class="thead-light">
                                <tr><th>Source</th><th>Sessions</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($sources as $source)
                                    <tr>
                                        <td class="text-truncate" style="max-width: 280px;">{{ $source->source }}</td>
                                        <td>{{ number_format($source->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-3">No data for this period</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Latest Activity</h5></div>
                    <div class="card-body table-responsive p-0">
                        @php
                            $badges = [
                                'page_view' => 'secondary', 'product_view' => 'primary', 'search' => 'info',
                                'add_to_cart' => 'success', 'remove_from_cart' => 'danger',
                                'wishlist_add' => 'warning', 'wishlist_remove' => 'dark',
                                'checkout_start' => 'warning', 'order_placed' => 'success',
                                'address_added' => 'info', 'address_selected' => 'primary',
                            ];
                        @endphp
                        <table class="table table-striped ua-table-sm mb-0">
                            <thead class="thead-light">
                                <tr><th>When</th><th>Visitor</th><th>Event</th><th>Detail</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($recentActivities as $activity)
                                    <tr>
                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans(null, true) }} ago</td>
                                        <td>
                                            <a href="{{ route('admin.activity.session.detail', $activity->visitor_session_id) }}">
                                                {{ $activity->user_name ?: 'Guest' }}
                                            </a>
                                        </td>
                                        <td><span class="badge badge-{{ $badges[$activity->event_type] ?? 'secondary' }}">{{ str_replace('_', ' ', $activity->event_type) }}</span></td>
                                        <td class="text-truncate" style="max-width: 240px;">
                                            {{ $activity->product_name ?: ($activity->search_query ? '"' . $activity->search_query . '"' : $activity->page_url) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No activity recorded yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Live sessions --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><span class="ua-live-dot"></span>Active Visitors (last 5 minutes)</h5>
                <small class="text-muted">auto-refreshes every 10s</small>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped ua-table-sm mb-0" id="liveTable">
                    <thead class="thead-light">
                        <tr><th>Visitor</th><th>Device</th><th>Current Page</th><th>Pages</th><th>Duration</th><th>Last Seen</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" class="text-center text-muted py-3">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@section('postload-section')
    <script>
        $(function () {
            var trend = @json($trend);
            var devices = @json($devices);

            new Chart(document.getElementById('uaTrendChart'), {
                type: 'line',
                data: {
                    labels: trend.labels,
                    datasets: [
                        {
                            label: 'Unique Visitors',
                            data: trend.visitors,
                            borderColor: '#2a78d6',
                            backgroundColor: '#2a78d6',
                            borderWidth: 2,
                            pointRadius: 3,
                            tension: 0.3
                        },
                        {
                            label: 'Page Views',
                            data: trend.page_views,
                            borderColor: '#008300',
                            backgroundColor: '#008300',
                            borderWidth: 2,
                            pointRadius: 3,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { labels: { color: '#52514e' } },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#e1e0d9' },
                            ticks: { color: '#898781', precision: 0 }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#898781' }
                        }
                    }
                }
            });

            new Chart(document.getElementById('uaDeviceChart'), {
                type: 'doughnut',
                data: {
                    labels: devices.map(function (d) { return d.device_type.charAt(0).toUpperCase() + d.device_type.slice(1); }),
                    datasets: [{
                        data: devices.map(function (d) { return d.total; }),
                        backgroundColor: ['#2a78d6', '#008300', '#e87ba4', '#eda100'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { color: '#52514e' } }
                    }
                }
            });

            function escapeHtml(text) {
                return $('<span>').text(text == null ? '' : text).html();
            }

            function refreshLive() {
                $.getJSON("{{ route('admin.activity.live') }}", function (res) {
                    $('#liveCount').text(res.count);
                    var $body = $('#liveTable tbody').empty();
                    if (!res.rows.length) {
                        $body.append('<tr><td colspan="7" class="text-center text-muted py-3">No visitors online right now</td></tr>');
                        return;
                    }
                    res.rows.forEach(function (row) {
                        var user = row.is_guest
                            ? '<span class="badge badge-secondary">Guest</span>'
                            : escapeHtml(row.user);
                        $body.append(
                            '<tr>' +
                            '<td>' + user + '</td>' +
                            '<td>' + escapeHtml(row.device) + '</td>' +
                            '<td class="text-truncate" style="max-width:240px;">' + escapeHtml(row.current_page) + '</td>' +
                            '<td>' + escapeHtml(row.pages) + '</td>' +
                            '<td>' + escapeHtml(row.duration) + '</td>' +
                            '<td>' + escapeHtml(row.last_seen) + '</td>' +
                            '<td><a href="' + row.url + '" class="btn btn-primary btn-sm">View</a></td>' +
                            '</tr>'
                        );
                    });
                });
            }

            refreshLive();
            setInterval(refreshLive, 10000);
        });
    </script>
@endsection
