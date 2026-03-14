@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-3 align-items-center">
            <div class="col-md-8">
                <h3>Tracking — AWB: <strong>{{ $displayAwb ?? $awb }}</strong></h3>
                <small class="text-muted">Order: {{ $order->order_id ?? $order->id }}</small>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('admin.orders.tracking', ['order' => $order->order_id, 'awb' => $awb]) }}"
                    class="btn btn-outline-primary btn-sm">Refresh</a>
                <a href="{{ route('admin_all_orders') }}" class="btn btn-secondary btn-sm">Back to Orders</a>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @php
            // Ensure $response is decoded
            if (is_string($response)) {
                $resp = json_decode($response, true);
            } else {
                $resp = $response ?? [];
            }
            $keyAwb = $displayAwb ?? $awb;
            $tracking = $resp['data'][$keyAwb] ?? $resp['data'][$awb] ?? ($tracking ?? []);
            $lastScan = $tracking['last_scan_details'] ?? null;
            $scanDetails = $tracking['scan_details'] ?? [];

            // Parse manifest date/time if available (use Carbon if present)
            $manifestedDtRaw = $tracking['order_date_time']['manifest_date_time'] ?? null;
            $manifestedDt = null;
            try {
                if (!empty($manifestedDtRaw) && $manifestedDtRaw !== '0000-00-00' && trim($manifestedDtRaw) !== '') {
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

            // Progress mapping (1..4)
            $statusSteps = [
                'manifested' => 1,
                'in transit' => 2,
                'out for delivery' => 3,
                'delivered' => 4,
            ];

            // determine current step
            $curStep = 0;
            $currentStatus = strtolower($tracking['current_status'] ?? '');
            foreach ($statusSteps as $k => $v) {
                if (strpos($currentStatus, $k) !== false) {
                    $curStep = $v;
                    break;
                }
            }
            // fallback using lastScan
            if ($curStep === 0 && !empty($lastScan['status'])) {
                $ls = strtolower($lastScan['status']);
                foreach ($statusSteps as $k => $v) {
                    if (strpos($ls, $k) !== false) {
                        $curStep = $v;
                        break;
                    }
                }
            }
            // if manifest datetime exists, consider step 1 completed
            if ($manifestedDt && $curStep < 1) {
                $curStep = max($curStep, 1);
            }

            $steps = [
                1 => 'Manifested',
                2 => 'In Transit',
                3 => 'Out for Delivery',
                4 => 'Delivered',
            ];

            // percent for horizontal fill (if you use it)
            $percent = $isCancelled ? 0 : ($curStep > 0 ? round((($curStep - 1) / (count($steps) - 1)) * 100) : 0);

            // dd($percent,$curStep,count($steps));
        @endphp

        <div class="row">
            {{-- SUMMARY CARD --}}
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Shipment Summary</h5>

                        @if ($order->shipmentTracking?->tracking_url)
                            <p> Tracking URL : <a class="ml-2 badge badge-success"
                                    href="{{ $order->shipmentTracking?->tracking_url }} " target="_blank">
                                    {{ $order->shipmentTracking?->tracking_url }} </a></p>
                        @endif

                        {{-- Cancelled indicator --}}
                        @if($isCancelled)
                            <div class="mb-2 p-2" style="border-left:4px solid #dc3545;background:#fff6f6;border-radius:6px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-danger">Shipment Cancelled</strong>
                                        <div class="small text-muted">Marked cancelled via carrier/portal.</div>
                                    </div>
                                    <div>
                                        <span class="badge badge-danger">Cancelled</span>
                                    </div>
                                </div>
                                <div class="mt-2 small text-muted">
                                    <strong>Cancel status:</strong> {{ $tracking['cancel_status'] ?? '—' }}
                                </div>
                            </div>
                        @endif

                        <div class="mb-2">
                            <span class="badge badge-info">{{ $tracking['logistic'] ?? '—' }}</span>
                            <span class="ml-2 badge {{ strtolower($tracking['current_status'] ?? '') == 'delivered' ? 'badge-success' : ($isCancelled ? 'badge-danger' : 'badge-warning') }}">
                                {{ $tracking['current_status'] ?? 'Unknown' }}
                            </span>
                        </div>

                        <dl class="row">
                            <dt class="col-sm-5">AWB</dt>
                            <dd class="col-sm-7">{{ $tracking['awb_no'] ?? ($displayAwb ?? $awb) }}</dd>

                            @if (!empty($awbList) && count($awbList) > 1)
                                <dt class="col-sm-5">AWB List</dt>
                                <dd class="col-sm-7"><small class="text-muted">{{ implode(', ', $awbList) }}</small></dd>
                            @endif

                            <dt class="col-sm-5">Order Type</dt>
                            <dd class="col-sm-7">{{ $tracking['order_type'] ?? '—' }}</dd>
                            <dt class="col-sm-5">Message</dt>
                            <dd class="col-sm-7">{{ $tracking['message'] ?? '—' }}</dd>
                            <dt class="col-sm-5">Last Updated</dt>
                            <dd class="col-sm-7">
                                {{ $lastScan['status_date_time'] ?? ($lastScan['scan_date_time'] ?? '—') }}</dd>

                            @if(!empty($tracking['promise_delivery_date']) || !empty($tracking['expected_delivery_date']))
                                <dt class="col-sm-5">Promise Date</dt>
                                <dd class="col-sm-7">{{ $tracking['promise_delivery_date'] ?? $tracking['expected_delivery_date'] ?? '—' }}</dd>
                            @endif

                            @if(!empty($tracking['cancel_status']))
                                <dt class="col-sm-5">Cancel Status</dt>
                                <dd class="col-sm-7">{{ $tracking['cancel_status'] }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- ORDER DETAILS --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Order Details</h5>
                        @php $od = $tracking['order_details'] ?? [] @endphp
                        <dl class="row small">
                            <dt class="col-sm-6">Order #</dt>
                            <dd class="col-sm-6">{{ $od['order_number'] ?? '—' }}</dd>
                            <dt class="col-sm-6">Weight (g)</dt>
                            <dd class="col-sm-6">{{ $od['phy_weight'] ?? '—' }}</dd>
                            <dt class="col-sm-6">Net Payment</dt>
                            <dd class="col-sm-6">{{ $od['net_payment'] ?? '—' }}</dd>
                            <dt class="col-sm-6">Dimensions (L×W×H)</dt>
                            <dd class="col-sm-6">
                                {{ $od['ship_length'] ?? '—' }} × {{ $od['ship_width'] ?? '—' }} ×
                                {{ $od['ship_height'] ?? '—' }}
                            </dd>
                        </dl>
                    </div>
                </div>

                {{-- CUSTOMER --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Customer</h5>
                        @php $c = $tracking['customer_details'] ?? [] @endphp
                        <p class="mb-1"><strong>{{ $c['customer_name'] ?? '—' }}</strong></p>
                        <p class="mb-1 small">
                            {{ $c['customer_address1'] ?? '' }}
                            {{ $c['customer_address2'] ? ', ' . $c['customer_address2'] : '' }}
                            <br>{{ $c['customer_city'] ?? '' }}, {{ $c['customer_state'] ?? '' }} -
                            {{ $c['customer_pincode'] ?? '' }}
                        </p>
                        <p class="mb-0 small">Mobile: {{ $c['customer_mobile'] ?? ($c['customer_phone'] ?? '—') }}</p>
                    </div>
                </div>
            </div>

            {{-- TIMELINE / SCANS --}}
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Last status</h5>

                        {{-- Manifested banner (if manifest datetime exists) --}}
                        @if($manifestedDt)
                            <div class="mb-3 p-3" style="background: linear-gradient(90deg, rgba(40,167,69,0.06), rgba(40,167,69,0.02)); border:1px solid rgba(40,167,69,0.12); border-radius:8px;">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div style="background:#28a745;color:#fff;border-radius:8px;padding:8px 12px;font-weight:700;">
                                            ✓ Manifested
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div><strong>Manifested on</strong></div>
                                        <div class="small text-muted">
                                            Date: {{ $manifestedDt->format('d M Y') }} &nbsp; • &nbsp;
                                            Time: {{ $manifestedDt->format('h:i a') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($lastScan && !empty($lastScan['status']))
                            <div class="border rounded p-3 mb-3">
                                <p class="mb-1"><strong>{{ $lastScan['status'] ?? '—' }}</strong></p>
                                <p class="mb-1 small text-muted">
                                    {{ $lastScan['status_date_time'] ?? ($lastScan['scan_date_time'] ?? '') }}</p>
                                <p class="mb-0">{{ $lastScan['remark'] ?? '' }}</p>
                                <p class="mb-0 small text-muted">Location: {{ $lastScan['scan_location'] ?? '-' }}</p>
                            </div>
                        @else
                            @if(!$manifestedDt)
                                <div class="alert alert-info">No last scan info available.</div>
                            @endif
                        @endif

                        <h5 class="card-title">Scan History</h5>
                        @if (count($scanDetails))
                            <ul class="timeline">
                                @foreach ($scanDetails as $s)
                                    <li>
                                        <div class="timeline-item">
                                            <span class="time">{{ $s['scan_date_time'] ?? ($s['status_date_time'] ?? '') }}</span>
                                            <h6 class="mt-1 mb-1">{{ $s['status'] ?? '-' }}</h6>
                                            <p class="mb-0 small">{{ $s['remark'] ?? '' }}</p>
                                            <p class="mb-0 text-muted small">Location: {{ $s['scan_location'] ?? '-' }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Also show table for precise view --}}
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date / Time</th>
                                            <th>Status</th>
                                            <th>Location</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($scanDetails as $s)
                                            <tr>
                                                <td>{{ $s['scan_date_time'] ?? ($s['status_date_time'] ?? '') }}</td>
                                                <td>{{ $s['status'] ?? '' }} <span
                                                        class="small text-muted">({{ $s['status_code'] ?? '' }})</span>
                                                </td>
                                                <td>{{ $s['scan_location'] ?? '' }}</td>
                                                <td>{{ $s['remark'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-light">No scan history available.</div>
                        @endif
                    </div>
                </div>

                {{-- Progress / Steps (professional horizontal look) --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Progress</h5>

                        {{-- If cancelled, show cancelled state --}}
                        @if($isCancelled)
                            <div class="mb-3 p-3" style="border-left:4px solid #dc3545;background:#fff6f6;border-radius:6px;">
                                <strong class="text-danger">Shipment Cancelled</strong>
                                <div class="small text-muted">Cancelled shipments do not show progress.</div>
                            </div>
                        @endif

                        {{-- Horizontal track --}}
                        <div style="height:10px;background:#eef2f7;border-radius:6px;overflow:hidden;margin-bottom:12px;">
                            <div style="height:100%; width: {{ ($percent == 0.00) ?  15.0  : $percent }}%; background: linear-gradient(90deg,#2b8cff,#28a745); transition: width .5s;"></div>
                        </div>

                        <div class="d-flex justify-content-between">
                            @foreach($steps as $idx => $label)
                                @php
                                    $isCompleted = $idx < $curStep;
                                    $isActive = $idx === $curStep;
                                @endphp
                                <div class="text-center" style="flex:1;">
                                    <div style="width:36px;height:36px;margin:0 auto;border-radius:50%;display:flex;align-items:center;justify-content:center;
                                        @if($isCompleted) background:#28a745;color:#fff; @elseif($isActive) border:2px solid #2b8cff;color:#2b8cff;background:#fff; @else border:1px solid #e6e9ef;color:#9aa0a6;background:#fff; @endif">
                                        @if($isCompleted) ✓ @elseif($isActive) ● @else {{ $idx }} @endif
                                    </div>
                                    <div class="small text-muted mt-2">{{ $label }}</div>

                                    {{-- show manifested date/time under manifested label --}}
                                    @if($idx === 1 && $manifestedDt)
                                        <div class="small text-muted mt-1">{{ $manifestedDt->format('d M Y, h:i a') }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Raw JSON (collapsible) --}}
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Raw Response</h6>
                        <pre style="max-height:300px; overflow:auto; background:#f8f9fa; padding:10px;">{{ json_encode($resp, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Small timeline CSS --}}
    @push('styles')
        <style>
            .timeline {
                list-style: none;
                padding-left: 0;
                margin: 0;
            }

            .timeline li {
                position: relative;
                padding-left: 20px;
                margin-bottom: 18px;
            }

            .timeline li:before {
                content: "";
                position: absolute;
                left: 3px;
                top: 6px;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #007bff;
            }

            .timeline-item .time {
                font-size: 12px;
                color: #6c757d;
            }
        </style>
    @endpush

@endsection
