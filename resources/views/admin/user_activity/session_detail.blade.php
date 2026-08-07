@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Session Journey #{{ $session->id }}</h4>
            <a href="{{ route('admin.activity.sessions') }}" class="btn btn-outline-secondary btn-sm">Back to Sessions</a>
        </div>

        {{-- Summary cards --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Visitor</h6></div>
                    <div class="card-body py-2">
                        @if ($session->user_id)
                            <p class="mb-1"><strong><a href="{{ route('admin.activity.user', $session->user_id) }}">{{ $session->user_name ?: 'User #' . $session->user_id }}</a></strong></p>
                            @if ($session->user_phone)<p class="mb-1">{{ $session->user_phone }}</p>@endif
                            @if ($session->user_email)<p class="mb-1">{{ $session->user_email }}</p>@endif
                        @else
                            <p class="mb-1"><span class="badge badge-secondary">Guest</span></p>
                        @endif
                        <p class="mb-0 text-muted"><small>Visitor ID: {{ substr($session->visitor_id, 0, 8) }}…</small></p>
                        <p class="mb-0 text-muted"><small>IP: {{ $session->ip_address }}</small></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Device</h6></div>
                    <div class="card-body py-2">
                        <p class="mb-1"><strong>{{ ucfirst($session->device_type) }}</strong></p>
                        <p class="mb-1">{{ $session->browser }} on {{ $session->os }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Source</h6></div>
                    <div class="card-body py-2">
                        @if ($session->utm_source)
                            <p class="mb-1"><strong>{{ $session->utm_source }}</strong>
                                @if ($session->utm_medium) / {{ $session->utm_medium }} @endif
                            </p>
                            @if ($session->utm_campaign)<p class="mb-1">Campaign: {{ $session->utm_campaign }}</p>@endif
                        @elseif ($session->referrer)
                            <p class="mb-1 text-break"><strong>{{ parse_url($session->referrer, PHP_URL_HOST) ?: $session->referrer }}</strong></p>
                        @else
                            <p class="mb-1"><strong>Direct</strong></p>
                        @endif
                        <p class="mb-0 text-muted text-break"><small>Landed on: {{ $session->landing_page }}</small></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="card-title mb-0">Time</h6></div>
                    <div class="card-body py-2">
                        <p class="mb-1">Started: <strong>{{ \Carbon\Carbon::parse($session->started_at)->format('d M Y, h:i A') }}</strong></p>
                        <p class="mb-1">Last active: {{ \Carbon\Carbon::parse($session->last_activity_at)->format('h:i A') }}</p>
                        <p class="mb-1">Duration: <strong>{{ activity_duration_format($session->duration_seconds) }}</strong></p>
                        <p class="mb-0">Pages viewed: <strong>{{ $session->page_views }}</strong></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card">
            <div class="card-header card-header-primary">
                <h5 class="card-title mb-0">Step-by-step Journey</h5>
            </div>
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
                <table class="table table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px;">Step</th>
                            <th>Time</th>
                            <th>Event</th>
                            <th>Detail</th>
                            <th>Time on Page</th>
                            <th>Scrolled</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $index => $activity)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($activity->created_at)->format('h:i:s A') }}</td>
                                <td><span class="badge badge-{{ $badges[$activity->event_type] ?? 'secondary' }}">{{ str_replace('_', ' ', $activity->event_type) }}</span></td>
                                <td class="text-break" style="max-width: 380px;">
                                    @if ($activity->product_name)
                                        <strong>{{ $activity->product_name }}</strong>
                                        @if (!empty($activity->metadata['quantity']))
                                            <small class="text-muted">(qty: {{ $activity->metadata['quantity'] }})</small>
                                        @endif
                                    @elseif ($activity->search_query)
                                        Searched for "<strong>{{ $activity->search_query }}</strong>"
                                    @elseif ($activity->event_type === 'order_placed')
                                        Order placed
                                        @if (!empty($activity->metadata['order_id']))
                                            — <strong>#{{ $activity->metadata['order_id'] }}</strong>
                                        @endif
                                    @elseif (in_array($activity->event_type, \App\Models\UserActivity::SERVER_EVENTS))
                                        {{ $activity->event_type === 'address_added' ? 'Added address' : 'Selected address' }}
                                        @if (!empty($activity->metadata['city']))
                                            — <strong>{{ $activity->metadata['type'] ?? '' }}</strong>
                                            {{ $activity->metadata['city'] }}, {{ $activity->metadata['pincode'] ?? '' }}
                                        @endif
                                    @else
                                        {{ $activity->page_url }}
                                    @endif
                                    @if ($activity->product_name)
                                        <br><small class="text-muted">{{ $activity->page_url }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if (in_array($activity->event_type, \App\Models\UserActivity::PAGE_EVENTS))
                                        {{ activity_duration_format($activity->duration_seconds) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if (in_array($activity->event_type, \App\Models\UserActivity::PAGE_EVENTS))
                                        {{ $activity->scroll_depth }}%
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No activity recorded for this session</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
