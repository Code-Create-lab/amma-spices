{{-- resources/views/frontend/order/tracking.blade.php --}}
@extends('frontend.layouts.app')

<style>
    /* Keep your existing qun- styles here */
    .qun-manifested-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: linear-gradient(90deg, rgba(34, 197, 94, 0.06), rgba(34, 197, 94, 0.02));
        border: 1px solid rgba(34, 197, 94, 0.12);
        padding: .75rem 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }

    .qun-manifested-badge {
        background: #10b981;
        color: #fff;
        padding: .45rem .7rem;
        border-radius: 8px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }

    .qun-manifested-meta {
        font-size: .92rem;
        color: #0f1724;
    }

    .qun-manifested-meta .muted {
        color: #6b7280;
        font-size: .86rem;
    }

    /* Error state styles */
    .qun-error-banner {
        border-left: 4px solid #dc3545;
        background: #fff6f6;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .qun-error-banner.warning {
        border-left-color: #ffc107;
        background: #fffbf0;
    }

    .qun-error-banner.info {
        border-left-color: #17a2b8;
        background: #f0f9ff;
    }

    .qun-error-heading {
        font-size: 1.1rem;
        font-weight: 700;
        color: #7f1d1d;
        margin-bottom: 0.5rem;
    }

    .qun-error-banner.warning .qun-error-heading {
        color: #854d0e;
    }

    .qun-error-banner.info .qun-error-heading {
        color: #0c5460;
    }

    .qun-error-message {
        color: #333;
        margin-bottom: 0.75rem;
    }

    .qun-error-details {
        font-size: 0.9rem;
        color: #6b7280;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
    }

    .qun-error-list {
        margin: 0.5rem 0 0 0;
        padding-left: 1.25rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .qun-error-list li {
        margin-bottom: 0.25rem;
    }
</style>

@section('content')
    @php
        // ✅ SAFE VARIABLE INITIALIZATION - ADD THIS AT THE TOP
        $errorType = $errorType ?? null;
        $errorMessage = $errorMessage ?? null;
        $order = $order ?? null;
        $awb = $awb ?? null;
        $response = $response ?? [];
        $tracking = $tracking ?? [];
        $scanDetails = $scanDetails ?? [];
        $lastScan = $lastScan ?? null;
        $statusCode = $statusCode ?? 200;
        $displayAwb = $displayAwb ?? $awb;
    @endphp

    @if ($order && $awb)
        <div class="container tracking-container">
             <div class="text-left">
                   
                    <a href="{{ route('customer.orders.index') }}" class="btnqun-btn">Back to My
                        Orders</a>
                </div>
                <div class="tecking-no-d">
                    <p class="mbcnn">Tracking — <small class="text-muted">AWB:</small>
                        <strong>{{ $displayAwb ?? $awb }}</strong>
                    </p>
                    <p class="text-ord-no">Order: {{ $order->cart_id ?? $order->order_id }}</p>
            </div>

            {{-- Error States Handling --}}
            @if ($errorType === 'cancelled')
                <div class="qun-error-banner">
                    <div class="qun-error-heading">Shipment Cancelled</div>
                    <div class="qun-error-message">{{ $errorMessage }}</div>
                    
                    <div class="qun-error-details">
                        <strong>AWB Number:</strong> {{ $awb }}<br>
                        <strong>Order ID:</strong> {{ $order->cart_id ?? $order->order_id }}<br>
                        <strong>Status:</strong> <span class="badge qun-badge qun-badge--danger">CANCELLED</span>
                    </div>
                    
                    <div class="qun-error-details">
                        <strong>Common reasons for cancellation:</strong>
                        <ul class="qun-error-list">
                            <li>Order was cancelled before dispatch</li>
                            <li>Pickup was not completed</li>
                            <li>Item was out of stock</li>
                            <li>Customer requested cancellation</li>
                        </ul>
                    </div>
                </div>

            @elseif ($errorType === 'not_found')
                <div class="qun-error-banner warning">
                    <div class="qun-error-heading">Tracking Number Not Found</div>
                    <div class="qun-error-message">{{ $errorMessage }}</div>
                    
                    <div class="qun-error-details">
                        <strong>AWB Number:</strong> {{ $awb ?? 'Not available' }}
                    </div>
                    
                    <div class="qun-error-details">
                        Please verify the tracking number is correct. If the shipment was recently created, 
                        tracking information may take a few hours to appear in the system.
                    </div>
                </div>

            @elseif ($errorType === 'returned')
                <div class="qun-error-banner info">
                    <div class="qun-error-heading">Shipment Returned to Origin</div>
                    <div class="qun-error-message">{{ $errorMessage }}</div>
                    
                    <div class="qun-error-details">
                        This shipment has been returned to the origin location. Please contact support for more details.
                    </div>
                </div>

            @elseif ($errorType === 'no_data')
                <div class="qun-error-banner info">
                    <div class="qun-error-heading">Tracking Not Available</div>
                    <div class="qun-error-message">{{ $errorMessage }}</div>
                    
                    <div class="qun-error-details">
                        Tracking information will be available once the courier updates the shipment status. 
                        Please check back in a few hours.
                    </div>
                </div>

            @elseif ($errorType === 'api_error' || $errorType === 'general_error')
                <div class="qun-error-banner">
                    <div class="qun-error-heading">Unable to Fetch Tracking</div>
                    <div class="qun-error-message">{{ $errorMessage }}</div>
                    
                    <div class="qun-error-details">
                        <button onclick="location.reload()" class="btn btn-sm btn-dark-gray qun-btn">
                            Try Again
                        </button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @php
                // Only process tracking data if we have valid tracking info (no errors)
                if (!$errorType && !empty($tracking)) {
                    // decode response if needed
                    if (is_string($response)) {
                        $resp = json_decode($response, true);
                    } else {
                        $resp = $response ?? [];
                    }
                    
                    // ✅ SAFE: Get tracking data
                    $trackingData = $resp['tracking_data'] ?? [];
                    
                    // Extract scan details from response
                    if (!empty($scanDetails)) {
                        $scanDetailsArray = $scanDetails;
                    } else {
                        $scanDetailsArray = $trackingData['shipment_track_activities'] ?? [];
                    }

                    // order_date_time -> manifest_date_time
                    $manifestedDtRaw = $tracking['order_date_time']['manifest_date_time'] ?? null;
                    $manifestedDt = null;
                    try {
                        if (
                            !empty($manifestedDtRaw) &&
                            $manifestedDtRaw !== '0000-00-00' &&
                            trim($manifestedDtRaw) !== ''
                        ) {
                            $manifestedDt = \Carbon\Carbon::parse($manifestedDtRaw);
                        }
                    } catch (\Throwable $e) {
                        $manifestedDt = null;
                    }

                    // Cancellation detection
                    $curStatusLower = strtolower($tracking['current_status'] ?? '');
                    $isCancelled = false;
                    if (stripos($curStatusLower, 'cancel') !== false || !empty($tracking['cancel_status'])) {
                        $isCancelled = true;
                    }
                } else {
                    // No tracking data, set defaults
                    $trackingData = [];
                    $scanDetailsArray = [];
                    $manifestedDt = null;
                    $isCancelled = false;
                }
            @endphp

            {{-- Only show tracking details if we have valid data (no error states) --}}
            @if (!$errorType && !empty($tracking))
                <div class="row">
                    {{-- LEFT: Summary --}}
                    <div class="col-md-4">
                        <div class="card  qun-card">
                            <div class="card-body">
                                <h5 class="card-title qun-title">Shipment Summary</h5>

                                @if ($isCancelled)
                                    <div
                                        style="border-left:4px solid #dc3545;padding:.75rem;border-radius:8px;background:#fff6f6;margin-bottom:.8rem;">
                                        <div style="display:flex;justify-content:space-between;align-items:center;">
                                            <div>
                                                <div style="font-weight:700;color:#7f1d1d;">Shipment Cancelled</div>
                                                <div class="small qun-muted">This shipment has been marked cancelled.</div>
                                            </div>
                                            <div style="text-align:right">
                                                <span class="badge qun-badge qun-badge--danger">Cancelled</span>
                                            </div>
                                        </div>
                                        <div class="mt-2 qun-small-muted">
                                            <strong>Cancel status:</strong> {{ $tracking['cancel_status'] ?? '—' }}
                                            @if (!empty($tracking['current_status']))
                                                &nbsp;•&nbsp; <strong>Current status:</strong>
                                                {{ $tracking['current_status'] }}
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if (isset($order->shipment) && $order->shipment->status == 'success')
                                    @php
                                        $shipmentResponse = json_decode($order->shipment->response, true);
                                        $trackingUrl = $shipmentResponse['data'][1]['tracking_url'] ?? null;
                                    @endphp
                                    @if ($trackingUrl)
                                        <p> Tracking URL : <a class="ml-2 badge badge-success qun-link"
                                                href="{{ $trackingUrl }}" target="_blank">
                                                {{ $trackingUrl }}
                                            </a>
                                        </p>
                                    @endif
                                @endif

                                <p class="mb-2">
                                    <span
                                        class="badge qun-badge qun-badge--info">{{ $tracking['logistic'] ?? '—' }}</span>
                                    @php
                                        $status = strtolower($tracking['current_status'] ?? '');
                                        if (stripos($status, 'cancel') !== false || $isCancelled) {
                                            $statusClass = 'qun-badge--danger';
                                        } elseif (strpos($status, 'delivered') !== false) {
                                            $statusClass = 'qun-badge--success';
                                        } elseif (
                                            strpos($status, 'in transit') !== false ||
                                            strpos($status, 'intransit') !== false
                                        ) {
                                            $statusClass = 'qun-badge--primary';
                                        } else {
                                            $statusClass = 'qun-badge--warning';
                                        }
                                    @endphp
                                    <span
                                        class="ml-2 badge {{ $statusClass }} qun-badge">{{ $tracking['current_status'] ?? 'Unknown' }}</span>
                                </p>

                                <dl class="row small mb-0 qun-small-muted">
                                    <dt class="col-5">AWB</dt>
                                    <dd class="col-7">{{ $tracking['awb_no'] ?? ($displayAwb ?? $awb) }}</dd>

                                    <dt class="col-5">Last Updated</dt>
                                    <dd class="col-7">
                                        {{ $lastScan['status_date_time'] ?? ($lastScan['scan_date_time'] ?? '—') }}
                                    </dd>

                                    <dt class="col-5">Message</dt>
                                    <dd class="col-7">{{ $tracking['message'] ?? '—' }}</dd>

                                    @if (!empty($tracking['promise_delivery_date']) || !empty($tracking['expected_delivery_date']))
                                        <dt class="col-5">Promise Date</dt>
                                        <dd class="col-7">
                                            {{ $tracking['promise_delivery_date'] ?? ($tracking['expected_delivery_date'] ?? '—') }}
                                        </dd>
                                    @endif

                                    @if (!empty($tracking['cancel_status']))
                                        <dt class="col-5">Cancel Status</dt>
                                        <dd class="col-7">{{ $tracking['cancel_status'] }}</dd>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        @if (!empty($tracking['customer_details']))
                            @php $c = $tracking['customer_details']; @endphp
                            <div class="card mb-3 qun-card">
                                <div class="card-body">
                                    <h5 class="card-title qun-title">Delivery To</h5>
                                    <p class="mb-1 qun-delivery-name"><strong>{{ $c['customer_name'] ?? '—' }}</strong>
                                    </p>
                                    <p class="mb-1 small qun-delivery-address">
                                        {{ $c['customer_address1'] ?? '' }}
                                        {{ $c['customer_address2'] ? ', ' . $c['customer_address2'] : '' }}
                                        <br>{{ $c['customer_city'] ?? '' }}, {{ $c['customer_state'] ?? '' }} -
                                        {{ $c['customer_pincode'] ?? '' }}
                                    </p>
                                    <p class="mb-0 small qun-muted">Mobile:
                                        {{ $c['customer_mobile'] ?? ($c['customer_phone'] ?? '—') }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Help CTA --}}
                        <div class="card qun-card">
                            <div class="card-body text-center">
                                <p class="mb-2"><strong>Need help?</strong></p>
                                <p class="small text-muted mb-2">If you think the tracking is incorrect or the delivery
                                    is delayed, contact our support.</p>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: Timeline / Details --}}
                    <div class="col-md-8">
                        <div class="card mb-3 qun-card">
                            <div class="card-body">
                                <h5 class="card-title qun-title">Latest Status</h5>

                                {{-- If a manifest date exists, show the manifested banner --}}
                                @if ($manifestedDt)
                                    <div class="qun-manifested-banner">
                                        <div class="qun-manifested-badge">
                                            ✓ Manifested
                                        </div>
                                        <div class="qun-manifested-meta">
                                            <div><strong>Manifested on</strong></div>
                                            <div class="muted">
                                                Date: {{ $manifestedDt->format('d M Y') }} &nbsp; • &nbsp;
                                                Time: {{ $manifestedDt->format('h:i a') }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($lastScan && !empty($lastScan['status']))
                                    <div class="border rounded p-3 mb-3 qun-scan-item">
                                        <p class="mb-1"><strong>{{ $lastScan['status'] ?? '—' }}</strong></p>
                                        <p class="mb-1 small text-muted qun-small-muted">
                                            {{ $lastScan['status_date_time'] ?? ($lastScan['scan_date_time'] ?? '') }}</p>
                                        @if (!empty($lastScan['remark']))
                                            <p class="mb-0">{{ $lastScan['remark'] }}</p>
                                        @endif
                                        <p class="mb-0 small text-muted qun-muted">Location:
                                            {{ $lastScan['scan_location'] ?? '-' }}</p>
                                    </div>
                                @else
                                    @if (!$manifestedDt)
                                        <div class="alert alert-info">No status available yet for this shipment.</div>
                                    @endif
                                @endif

                                <h5 class="card-title qun-title">Scan History</h5>

                                @if (count($scanDetailsArray))
                                    <div class="mb-3">
                                        @foreach ($scanDetailsArray as $s)
                                            <div class="qun-scan-item qun-card mb-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong>{{ $s['status'] ?? ($s['activity'] ?? '-') }}</strong>
                                                        <div class="small text-muted">{{ $s['remark'] ?? '' }}</div>
                                                        @if (!empty($s['status_code']))
                                                            <div class="small text-muted">Code: {{ $s['status_code'] }}</div>
                                                        @endif
                                                        @if (!empty($s['status_reason']))
                                                            <div class="small text-muted">Reason: {{ $s['status_reason'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="text-right small text-muted qun-small-muted">
                                                        {{ $s['scan_date_time'] ?? ($s['status_date_time'] ?? ($s['date'] ?? '')) }}<br>
                                                        <span
                                                            class="text-muted">{{ $s['scan_location'] ?? ($s['location'] ?? '-') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-light">No scan history available yet.</div>
                                @endif

                                {{-- Progress bar logic --}}
                                @php
                                    $statusSteps = [
                                        'manifested' => 1,
                                        'in transit' => 2,
                                        'out for delivery' => 3,
                                        'delivered' => 4,
                                    ];

                                    $curStep = 0;
                                    $curStatus = strtolower($tracking['current_status'] ?? '');
                                    foreach ($statusSteps as $k => $v) {
                                        if (strpos($curStatus, $k) !== false) {
                                            $curStep = $v;
                                            break;
                                        }
                                    }

                                    if ($curStep === 0 && !empty($lastScan['status'])) {
                                        $ls = strtolower($lastScan['status']);
                                        foreach ($statusSteps as $k => $v) {
                                            if (strpos($ls, $k) !== false) {
                                                $curStep = $v;
                                                break;
                                            }
                                        }
                                    }

                                    if ($manifestedDt && $curStep < 1) {
                                        $curStep = max($curStep, 1);
                                    }

                                    $steps = [
                                        1 => 'Manifested',
                                        2 => 'In Transit',
                                        3 => 'Out for Delivery',
                                        4 => 'Delivered',
                                    ];

                                    if ($isCancelled) {
                                        $percent = 0;
                                    } else {
                                        $percent = $curStep > 0 ? round((($curStep - 1) / (count($steps) - 1)) * 100) : 0;
                                    }
                                @endphp

                                <div class="shipment-progress-wrapper mt-3 qun-progress">
                                    <div class="qun-track" role="progressbar" aria-valuemin="0"
                                        aria-valuemax="{{ count($steps) }}" aria-valuenow="{{ $curStep }}">
                                        @if ($isCancelled)
                                            <div class="qun-fill"
                                                style="width:0%; background: linear-gradient(90deg, rgba(220,53,69,0.9), rgba(220,53,69,0.9)); opacity:.12;">
                                            </div>
                                        @else
                                            <div class="qun-fill" style="width: {{ $percent == 0.0 ? 15.0 : $percent }}%;">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="qun-steps d-flex" style="margin-top:.6rem;">
                                        @if ($isCancelled)
                                            <div style="flex:1; text-align:center;">
                                                <div class="qun-step-marker"
                                                    style="width:40px;height:40px;border-radius:8px;background:#fff6f6;border:1px solid rgba(220,53,69,0.16);color:#7f1d1d;display:inline-flex;align-items:center;justify-content:center;font-weight:700;">
                                                    ✕
                                                </div>
                                                <div class="qun-step-label" style="margin-top:.5rem;color:#7f1d1d;">
                                                    Cancelled
                                                </div>
                                            </div>
                                        @else
                                            @foreach ($steps as $idx => $label)
                                                @php
                                                    $isCompleted = $idx < $curStep;
                                                    $isActive = $idx === $curStep;
                                                @endphp
                                                <div style="flex:1;text-align:center;">
                                                    <div class="qun-step-marker @if ($isCompleted) qun-step--completed @elseif($isActive) qun-step--active @else qun-step--pending @endif"
                                                        style="width:36px;height:36px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;">
                                                        @if ($isCompleted)
                                                            ✓
                                                        @elseif($isActive)
                                                            ●
                                                        @else
                                                            {{ $idx }}
                                                        @endif
                                                    </div>

                                                    <div class="qun-step-label" style="margin-top:.5rem;">
                                                        {{ $label }}
                                                        @if ($idx === 1 && $manifestedDt)
                                                            <div class="small qun-muted" style="margin-top:.25rem;">
                                                                {{ $manifestedDt->format('d M Y, h:i a') }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                {{-- Raw JSON (support/admin only) --}}
                                @if (auth()->check() && ((auth()->user()->is_support ?? false) || (auth()->user()->is_admin ?? false)))
                                    <div class="card mt-3 qun-card">
                                        <div class="card-body">
                                            <h6 class="card-title qun-title">Raw Response (support)</h6>
                                            <pre class="qun-rawpre">{{ json_encode($response, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- No order found fallback --}}
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="card qun-card shadow-sm">
                        <div class="card-body py-5">

                            <div style="font-size:48px; margin-bottom:1rem;">📦</div>

                            <h4 class="mb-2">No Order Found</h4>

                            <p class="text-muted mb-4">
                                We couldn't find any order matching the provided details.
                                Please check the Order ID or AWB number and try again.
                            </p>

                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('customer.orders.index') }}" class="btn btn-dark-gray btn-large btn-more">
                                    Go to My Orders
                                </a>

                                <a href="{{ url()->previous() }}" class="btn btn-dark-gray btn-large btn-more">
                                    Go Back
                                </a>
                            </div>

                        </div>
                    </div>

                    <p class="small text-muted mt-3">
                        If you believe this is an error, please contact support.
                    </p>
                </div>
            </div>
        </div>
    @endif
@endsection