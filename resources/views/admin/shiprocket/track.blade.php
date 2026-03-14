{{-- @extends('admin.layout.app')

@section('content') --}}

<div class="container-fluid">

    <div class="card mb-4">
        <div class="card-header bg-white border-bottom">
            <h4 class="mb-0 text-dark">Shipment Tracking</h4>
        </div>

        <div class="card-body">

            @php
                // Access nested tracking_data from Shiprocket response
                $trackingData = $data['tracking_data'] ?? [];
                $summary = $trackingData['shipment_track'][0] ?? null;
                $events = $trackingData['shipment_track_activities'] ?? [];
                $trackStatus = $trackingData['track_status'] ?? 0;
                $errorType = $data['error_type'] ?? null;
                $errorMessage = $data['error'] ?? null;
            @endphp


            {{-- CASE 1: Cancelled Shipment --}}
            @if($errorType === 'cancelled')
                <div class="alert alert-light border-left-danger">
                    <h5 class="alert-heading mb-3">Shipment Cancelled</h5>
                    <p class="mb-3">{{ $errorMessage }}</p>
                    
                    <div class="mb-3">
                        <strong>AWB Number:</strong> {{ $data['awb'] ?? 'N/A' }}<br>
                        <strong>Status:</strong> <span class="text-danger">CANCELLED</span>
                    </div>

                    <hr class="my-3">
                    
                    <p class="mb-2 text-muted small"><strong>Common reasons for cancellation:</strong></p>
                    <ul class="text-muted small mb-0">
                        <li>Order was cancelled before dispatch</li>
                        <li>Pickup was not completed</li>
                        <li>Item was out of stock</li>
                    </ul>
                </div>

            {{-- CASE 2: AWB Not Found --}}
            @elseif($errorType === 'not_found')
                <div class="alert alert-light border-left-warning">
                    <h5 class="alert-heading mb-3">Tracking Number Not Found</h5>
                    <p class="mb-3">{{ $errorMessage }}</p>
                    
                    <div class="mb-3">
                        <strong>AWB Number:</strong> {{ $data['awb'] ?? 'N/A' }}
                    </div>

                    <hr class="my-3">
                    
                    <p class="text-muted small mb-0">Please verify the tracking number is correct. If the shipment was recently created, tracking information may take a few hours to appear.</p>
                </div>

            {{-- CASE 3: Returned to Origin --}}
            @elseif($errorType === 'returned')
                <div class="alert alert-light border-left-info">
                    <h5 class="alert-heading mb-3">Shipment Returned</h5>
                    <p class="mb-0">{{ $errorMessage }}</p>
                </div>

            {{-- CASE 4: Tracking Not Ready --}}
            @elseif($trackStatus == 0 && !$errorType)
                <div class="alert alert-light border-left-info">
                    <h5 class="alert-heading mb-3">Tracking Not Available</h5>
                    <p class="mb-0">{{ $errorMessage ?? 'Tracking information will be available once the courier updates the shipment status.' }}</p>
                </div>

            {{-- CASE 5: General Error --}}
            @elseif($errorMessage && ($errorType === 'general_error' || $errorType === 'api_error'))
                <div class="alert alert-light border-left-danger">
                    <h5 class="alert-heading mb-3">Unable to Fetch Tracking</h5>
                    <p class="mb-3">{{ $errorMessage }}</p>
                    <button onclick="location.reload()" class="btn btn-sm btn-outline-primary">
                        Retry
                    </button>
                </div>
            @endif


            {{-- Show tracking details only if we have valid data --}}
            @if($summary && $trackStatus == 1)
                
                {{-- Summary Section --}}
                <div class="tracking-summary mb-4">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="tracking-info-item">
                                <label>AWB Number</label>
                                <div class="value">{{ $summary['awb_code'] ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="tracking-info-item">
                                <label>Courier</label>
                                <div class="value">{{ $summary['courier_name'] ?? ($summary['courier_company'] ?? 'N/A') }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="tracking-info-item">
                                <label>Status</label>
                                <div class="value">
                                    <span class="status-badge
                                        @php
                                            $status = strtolower($summary['current_status'] ?? '');
                                        @endphp
                                        @if(str_contains($status, 'deliver')) status-delivered
                                        @elseif(str_contains($status, 'transit') || str_contains($status, 'in-transit')) status-transit
                                        @elseif(str_contains($status, 'pickup') || str_contains($status, 'picked')) status-picked
                                        @elseif(str_contains($status, 'out for')) status-out-for-delivery
                                        @else status-default
                                        @endif">
                                        {{ $summary['current_status'] ?? 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="tracking-info-item">
                                <label>Expected Delivery</label>
                                <div class="value">
                                    @if(!empty($trackingData['etd']))
                                        {{ date('d M Y', strtotime($trackingData['etd'])) }}
                                    @else
                                        Not available
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Shipment Details --}}
                <div class="shipment-details mb-4 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <strong>Origin:</strong> {{ $summary['origin'] ?? '—' }}
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Destination:</strong> {{ $summary['destination'] ?? ($summary['delivered_to'] ?? '—') }}
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Consignee:</strong> {{ $summary['consignee_name'] ?? '—' }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4 mb-2">
                            <strong>Weight:</strong> {{ $summary['weight'] ?? 'N/A' }} kg
                        </div>
                        <div class="col-md-4 mb-2">
                            <strong>Packages:</strong> {{ $summary['packages'] ?? 'N/A' }}
                        </div>
                        @if(!empty($summary['delivered_date']))
                        <div class="col-md-4 mb-2">
                            <strong>Delivered:</strong> <span class="text-success">{{ date('d M Y', strtotime($summary['delivered_date'])) }}</span>
                        </div>
                        @endif
                    </div>
                </div>


                {{-- Tracking URL --}}
                @if(!empty($trackingData['track_url']))
                    <a href="{{ $trackingData['track_url'] }}" target="_blank" class="btn btn-outline-primary btn-sm mb-4">
                        View on Courier Website
                    </a>
                @endif


                {{-- Timeline --}}
                <h5 class="mb-3">Shipment Activity</h5>

                @if(empty($events))
                    <div class="alert alert-light">
                        No tracking events available yet.
                    </div>
                @else
                    <div class="timeline">
                        @foreach($events as $index => $event)
                            <div class="timeline-item {{ $index === 0 ? 'timeline-item-current' : '' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-date">{{ date('d M Y, h:i A', strtotime($event['date'])) }}</div>
                                    <div class="timeline-activity">{{ $event['activity'] }}</div>
                                    <div class="timeline-location">{{ $event['location'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

        </div>
    </div>
</div>

{{-- Styles --}}
<style>
/* Alert with colored left border */
.border-left-danger {
    border-left: 4px solid #dc3545;
}

.border-left-warning {
    border-left: 4px solid #ffc107;
}

.border-left-info {
    border-left: 4px solid #17a2b8;
}

.alert-light {
    background-color: #fafafa;
    border: 1px solid #e0e0e0;
}

.alert-heading {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

/* Tracking Info Items */
.tracking-info-item {
    border-left: 3px solid #e9ecef;
    padding-left: 12px;
}

.tracking-info-item label {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 4px;
    font-weight: 500;
}

.tracking-info-item .value {
    font-size: 1rem;
    color: #212529;
    font-weight: 500;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-delivered {
    background-color: #d4edda;
    color: #155724;
}

.status-transit {
    background-color: #fff3cd;
    color: #856404;
}

.status-picked {
    background-color: #d1ecf1;
    color: #0c5460;
}

.status-out-for-delivery {
    background-color: #cce5ff;
    color: #004085;
}

.status-default {
    background-color: #e9ecef;
    color: #495057;
}

/* Shipment Details */
.shipment-details {
    font-size: 0.95rem;
}

.shipment-details strong {
    color: #495057;
    font-weight: 600;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
    margin-top: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 8px;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 24px;
}

.timeline-marker {
    position: absolute;
    left: -26px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #dee2e6;
    border: 2px solid #fff;
}

.timeline-item-current .timeline-marker {
    width: 16px;
    height: 16px;
    left: -28px;
    top: 2px;
    background-color: #28a745;
}

.timeline-content {
    background-color: #f8f9fa;
    padding: 12px 16px;
    border-radius: 4px;
    border-left: 2px solid #dee2e6;
}

.timeline-item-current .timeline-content {
    border-left-color: #28a745;
    background-color: #f0f9f4;
}

.timeline-date {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 4px;
}

.timeline-activity {
    font-size: 1rem;
    color: #212529;
    font-weight: 600;
    margin-bottom: 4px;
}

.timeline-location {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Card Header */
.card-header {
    padding: 1rem 1.25rem;
}

.card-header h4 {
    font-size: 1.25rem;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .tracking-info-item {
        margin-bottom: 1rem;
    }
    
    .timeline {
        padding-left: 25px;
    }
    
    .timeline-marker {
        left: -21px;
    }
    
    .timeline-item-current .timeline-marker {
        left: -23px;
    }
}

/* Button */
.btn-outline-primary {
    border-color: #007bff;
    color: #007bff;
}

.btn-outline-primary:hover {
    background-color: #007bff;
    color: #fff;
}
</style>

{{-- @endsection --}}