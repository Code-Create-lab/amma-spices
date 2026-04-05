@extends('frontend.layouts.app', ['title' => ''])

@section('content')
<style>
    /* ── Order Layout Overrides (theme-safe) ── */
    .order-page-wrap {
        background: #FFF7ED;
        min-height: 60vh;
        padding-bottom: 40px;
    }

    /* Card */
    .fk-order-card {
        background: #fff;
        border: 1px solid #e8d9c8;
        border-radius: 6px;
        margin-bottom: 0;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(75,59,46,0.07);
        transition: box-shadow 0.2s;
    }
    .fk-order-card:hover {
        box-shadow: 0 4px 14px rgba(75,59,46,0.13);
    }

    /* Card Header — clickable */
    .fk-order-header {
        background: #4B3B2E;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        cursor: pointer;
        user-select: none;
        position: relative;
    }
    .fk-order-header:hover {
        background: #3a2d23;
    }

    /* Chevron icon */
    .fk-chevron {
        margin-left: auto;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(255,247,237,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.3s ease, background 0.2s;
    }
    .fk-chevron svg {
        transition: transform 0.3s ease;
        color: #FFF7ED;
    }
    .fk-order-card.open .fk-chevron svg {
        transform: rotate(180deg);
    }
    .fk-order-card.open .fk-chevron {
        background: rgba(255,247,237,0.25);
    }

    /* Stop click propagation on buttons inside header */
    .fk-header-actions a,
    .fk-header-actions span {
        position: relative;
        z-index: 2;
    }

    .fk-order-id {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }
    .fk-order-id .lbl {
        font-size: 10px;
        color: #c9b49a;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 600;
    }
    .fk-order-id .val {
        font-size: 14px;
        font-weight: 700;
        color: #FFF7ED;
        letter-spacing: 0.4px;
    }

    .fk-order-date {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }
    .fk-order-date .lbl {
        font-size: 10px;
        color: #c9b49a;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 600;
    }
    .fk-order-date .val {
        font-size: 13px;
        color: #FFF7ED;
        font-weight: 500;
    }

    .fk-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Status Badges */
    .badge-n {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .badge-n.bg-danger  { background: #ffeaea !important; color: #c0392b !important; }
    .badge-n.bg-success { background: #eafaf1 !important; color: #1e8449 !important; }
    .badge-n.bg-warning { background: #fff3cd !important; color: #7d6008 !important; }
    .badge-n:not(.bg-danger):not(.bg-success):not(.bg-warning) {
        background: rgba(255,247,237,0.18);
        color: #FFF7ED;
        border: 1px solid rgba(255,247,237,0.3);
    }

    .track-order-link .badge-n.bg-warning {
        cursor: pointer;
        transition: background 0.15s;
        text-decoration: none;
    }
    .track-order-link:hover .badge-n.bg-warning {
        background: #ffe08a !important;
    }

    .invoice-dow {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        background: transparent;
        color: #FFF7ED;
        border: 1px solid rgba(255,247,237,0.5);
        border-radius: 3px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        transition: all 0.15s;
    }
    .invoice-dow:hover {
        background: #FFF7ED;
        color: #4B3B2E;
    }

    /* ── Accordion Body ── */
    .fk-order-body-wrap {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .fk-order-card.open .fk-order-body-wrap {
        max-height: 3000px; /* large enough for any content */
    }

    /* Collapsed preview strip */
    .fk-order-preview {
        padding: 10px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        border-top: 1px solid #f3e9df;
        background: #fffcf8;
        font-size: 12px;
        color: #8a7060;
        transition: opacity 0.2s;
    }
    .fk-order-card.open .fk-order-preview {
        display: none;
    }
    .fk-order-preview .preview-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .fk-order-preview .preview-item strong {
        color: #4B3B2E;
        font-size: 12px;
    }
    .fk-order-preview .expand-hint {
        margin-left: auto;
        color: #4B3B2E;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        opacity: 0.6;
    }

    /* Body */
    .fk-order-body {
        padding: 20px;
    }

    /* Section Heading */
    .fk-section-head {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #4B3B2E;
        border-left: 3px solid #4B3B2E;
        padding-left: 8px;
        margin-bottom: 14px;
    }

    /* Shipping Grid */
    .fk-shipping-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px 28px;
        background: #FFF7ED;
        border: 1px solid #e8d9c8;
        border-radius: 4px;
        padding: 14px 18px;
        margin-bottom: 22px;
    }
    .fk-shipping-grid .full { grid-column: 1 / -1; }

    .cu_row {
        display: flex;
        gap: 8px;
        align-items: baseline;
    }
    .customer-label {
        font-size: 12px;
        color: #8a7060;
        font-weight: 600;
        min-width: 88px;
        flex-shrink: 0;
    }
    .cus-details {
        font-size: 13px;
        color: #2c1f14;
    }

    /* Items Table */
    .fk-table-wrap {
        border: 1px solid #e8d9c8;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .fk-table-wrap table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .fk-table-wrap thead th {
        background: #4B3B2E;
        color: #FFF7ED;
        padding: 9px 14px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .fk-table-wrap tbody tr {
        border-bottom: 1px solid #f3e9df;
    }
    .fk-table-wrap tbody tr:last-child { border-bottom: none; }
    .fk-table-wrap tbody tr:hover { background: #fffaf5; }
    .fk-table-wrap tbody td {
        padding: 13px 14px;
        vertical-align: middle;
        color: #2c1f14;
    }

    .prod-img-link img {
        width: 54px;
        height: 54px;
        object-fit: cover;
        border: 1px solid #e8d9c8;
        border-radius: 4px;
        flex-shrink: 0;
    }
    .prod-info { margin-left: 12px; }
    .prod-info .fw-bold a {
        font-size: 13px;
        font-weight: 600;
        color: #2c1f14;
        text-decoration: none;
    }
    .prod-info .fw-bold a:hover { color: #4B3B2E; text-decoration: underline; }
    .prod-info small { color: #8a7060; font-size: 11px; }

    /* Price Summary */
    .fk-bottom-row {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        align-items: flex-start;
    }

    .price-summary {
        flex: 1;
        min-width: 220px;
        background: #FFF7ED;
        border: 1px solid #e8d9c8;
        border-radius: 4px;
        padding: 14px 18px;
    }
    .price-summary .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        font-size: 13px;
        border-bottom: 1px dashed #e8d9c8;
    }
    .price-summary .price-row:last-child { border-bottom: none; }
    .price-summary .price-row .label { color: #8a7060; }
    .price-summary .price-row .value { font-weight: 500; color: #2c1f14; }
    .price-summary .price-row.discount .value { color: #1e8449; font-weight: 600; }
    .price-summary .price-row.GrandTotal {
        margin-top: 4px;
        border-top: 2px solid #4B3B2E;
        border-bottom: none;
        padding-top: 10px;
    }
    .price-summary .price-row.GrandTotal .label { font-size: 14px; font-weight: 700; color: #4B3B2E; }
    .price-summary .price-row.GrandTotal .value { font-size: 16px; font-weight: 700; color: #4B3B2E; }

    /* Payment Details */
    .Payment-de { flex: 1; min-width: 220px; }
    .Payment_head {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #4B3B2E;
        border-left: 3px solid #4B3B2E;
        padding-left: 8px;
        margin-bottom: 12px;
    }
    .payment-box {
        background: #FFF7ED;
        border: 1px solid #e8d9c8;
        border-radius: 4px;
        padding: 14px 18px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 20px;
        font-size: 13px;
        color: #2c1f14;
    }
    .payment-method { display: flex; flex-direction: column; gap: 2px; }
    .py-label {
        font-size: 11px;
        color: #8a7060;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .py-value { font-size: 12px; color: #2c1f14; word-break: break-all; }
    .payment-box .badge { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 3px; }
    .payment-box .bg-success { background: #eafaf1 !important; color: #1e8449 !important; }
    .payment-box .bg-danger  { background: #ffeaea !important; color: #c0392b !important; }
    .payment-box .bg-warning { background: #fff3cd !important; color: #7d6008 !important; }

    /* Review */
    .review-pop .p-3 { background: #fffaf5 !important; border-color: #e8d9c8 !important; font-size: 12px; }
    .btn.btn-outline-primary.reviewModal {
        font-size: 12px;
        /* color: #4B3B2E; */
        border-color: #4B3B2E;
        padding: 4px 12px;
        border-radius: 3px;
        font-weight: 600;
    }
    .btn.btn-outline-primary.reviewModal:hover { background: #4B3B2E; color: #FFF7ED; }

    /* Empty State */
    .no-orders { background: #fff; border: 1px solid #e8d9c8; border-radius: 6px; padding: 60px 20px; }
    .no-orders h3 { color: #4B3B2E; font-size: 20px; margin-bottom: 8px; }
    .no-orders p  { color: #8a7060; font-size: 14px; }

    /* Divider */
    .fk-divider { border: none; border-top: 1px solid #e8d9c8; margin: 14px 0; }

    /* ── Single Column Orders List ── */
    .fk-orders-grid {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    @media (max-width: 768px) {
        .fk-shipping-grid { grid-template-columns: 1fr; }
        .payment-box      { grid-template-columns: 1fr; }
        .fk-bottom-row    { flex-direction: column; }
        .fk-order-header  { gap: 10px; }
    }
</style>

<main class="main">
    <div class="page-header">
        <div class="container">
            <div class="row" style="display: block;">
                <div class="heading">
                    <h2 class="title text-center">My Orders</h2>
                    <span class="seprater-img"><img src="{{ asset('assets/img/seprater.png') }}"></span>
                </div>
            </div>
        </div>
    </div>

    <nav aria-label="breadcrumb" class="breadcrumb-nav">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Orders</li>
            </ol>
        </div>
    </nav>

    <div class="page-content order-page-wrap">
        <div class="dashboard">
            <div class="container">
                <div class="row">

                    @if (Route::currentRouteName() != 'customer.track-orders.index')
                        @include('frontend.layouts.sidebar')
                    @endif

                    <div class="col-md-8 mx-auto">

                        @if ($orders->isNotEmpty())
                            <div class="fk-orders-grid">
                            @foreach ($orders as $order)

                                @php
                                    $itemCount   = $order->orderItems->count();
                                    $firstItem   = $order->orderItems->first();
                                @endphp

                                <div class="fk-order-card" id="order-card-{{ $order->cart_id }}">

                                    {{-- ── Clickable Header ── --}}
                                    <div class="fk-order-header"
                                         onclick="toggleOrder('{{ $order->cart_id }}', event)">

                                        <div class="fk-order-id">
                                            <span class="lbl">Order ID</span>
                                            <span class="val">#{{ $order->cart_id }}</span>
                                        </div>

                                        <div class="fk-order-date">
                                            <span class="lbl">Placed On</span>
                                            <span class="val">📅 {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</span>
                                        </div>

                                        <div class="fk-header-actions">
                                            <span class="badge-n
                                                @if ($order->order_status === 'Cancelled') bg-danger text-white
                                                @elseif($order->order_status === 'Completed') bg-success text-white
                                                @else @endif">
                                                {{ ucfirst($order->order_status) }}
                                            </span>

                                            @if (in_array($order->shipment?->status, [
                                                'READY TO SHIP','PICKUP SCHEDULED','PICKED UP',
                                                'IN TRANSIT','OUT FOR DELIVERY','DELIVERED',
                                            ]))
                                                <a href="{{ route('tracking', ['o' => $order->cart_id, 's' => $order->shipment?->awb]) }}"
                                                   class="track-order-link"
                                                   onclick="event.stopPropagation()">
                                                    <span class="badge-n bg-warning text-dark">🚚 Track</span>
                                                </a>
                                            @endif

                                            @if ($order->order_status === 'Completed')
                                                <a class="invoice-dow" target="_blank"
                                                   href="{{ route('order.invoice', $order->cart_id) }}"
                                                   onclick="event.stopPropagation()">
                                                    <i class="la la-arrow-down"></i> Invoice
                                                </a>
                                            @endif
                                        </div>

                                        {{-- Chevron --}}
                                        <div class="fk-chevron">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                 stroke="#FFF7ED" stroke-width="2.5"
                                                 stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </div>

                                    </div>{{-- /.fk-order-header --}}

                                    {{-- ── Collapsed Preview Strip ── --}}
                                    <div class="fk-order-preview">
                                        @if ($firstItem)
                                            <div class="preview-item">
                                                <img src="{{ asset('storage/' . $firstItem->varient_image) }}"
                                                     style="width:28px;height:28px;object-fit:cover;border-radius:3px;border:1px solid #e8d9c8;">
                                                <span>
                                                    <strong>{{ Str::limit($firstItem->product_name, 22) }}</strong>
                                                    @if ($itemCount > 1)
                                                        <span style="color:#8a7060;">+{{ $itemCount - 1 }} more</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                        <div class="preview-item" style="margin-left:auto;">
                                            <strong style="color:#4B3B2E;">₹{{ $order->total_price }}</strong>
                                        </div>
                                        <div class="expand-hint">
                                            View Details ›
                                        </div>
                                    </div>

                                    {{-- ── Accordion Body ── --}}
                                    <div class="fk-order-body-wrap" id="body-{{ $order->cart_id }}">
                                        <div class="fk-order-body">

                                            {{-- Shipping --}}
                                            <div class="fk-section-head">Shipping Details</div>
                                            @if ($order->address)
                                                <div class="fk-shipping-grid">
                                                    <div class="cu_row">
                                                        <label class="customer-label">Name:</label>
                                                        <span class="cus-details">{{ $order->address->receiver_name }}</span>
                                                    </div>
                                                    <div class="cu_row">
                                                        <label class="customer-label">Email:</label>
                                                        <span class="cus-details">{{ $order->address->receiver_email }}</span>
                                                    </div>
                                                    <div class="cu_row">
                                                        <label class="customer-label">Mobile:</label>
                                                        <span class="cus-details">{{ $order->address->receiver_phone }}</span>
                                                    </div>
                                                    <div class="cu_row">
                                                        <label class="customer-label">Alternate:</label>
                                                        <span class="cus-details">{{ $order->address->alternate_phone }}</span>
                                                    </div>
                                                    <div class="cu_row full">
                                                        <label class="customer-label">Address:</label>
                                                        <span class="cus-details">
                                                            {{ $order->address->landmark }}
                                                            {{ $order->address->house_no }}
                                                            {{ $order->address->society }}
                                                            {{ $order->address->city }}
                                                            {{ $order->address->state }}
                                                        </span>
                                                    </div>
                                                    <div class="cu_row">
                                                        <label class="customer-label">Pin Code:</label>
                                                        <span class="cus-details">{{ $order->address->pincode }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <p style="color:#8a7060;font-size:13px;margin-bottom:16px;"><em>No shipping address found.</em></p>
                                            @endif

                                            <hr class="fk-divider">

                                            {{-- Items --}}
                                            <div class="fk-section-head">Order Items</div>
                                            <div class="fk-table-wrap">
                                                <table class="table align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Item</th>
                                                            <th>Qty</th>
                                                            <th>Price</th>
                                                            <th>Total</th>
                                                            @if ($order->order_status == 'Completed')
                                                                <th>Review</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php $subTotal = 0; @endphp
                                                        @foreach ($order->orderItems as $item)
                                                            @php
                                                                $lineTotal = ($item->variation->price == 0
                                                                    ? $item->variation->mrp
                                                                    : $item->variation->price) * $item->quantity;
                                                                $subTotal += $lineTotal;
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <a class="prod-img-link" href="{{ route('single.product.view', $item->variation->product->slug) }}">
                                                                            <img src="{{ asset('storage/' . $item->varient_image) }}"
                                                                                 alt="{{ $item->product_name }}">
                                                                        </a>
                                                                        <div class="prod-info">
                                                                            <div class="fw-bold">
                                                                                <a href="{{ route('single.product.view', $item->variation->product->slug) }}">
                                                                                    {{ $item->product_name }}
                                                                                </a>
                                                                            </div>
                                                                            @if ($item->variation->variation_attributes->isNotEmpty())
                                                                                <small>
                                                                                    {{ $item->variation->variation_attributes->map(fn($a) => $a->attribute->attribute->name . ': ' . $a->attribute->name)->implode(', ') }}
                                                                                </small>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>{{ $item->quantity }}</td>
                                                                <td>₹{{ $item->variation->price == 0 ? $item->variation->mrp : $item->variation->price }}</td>
                                                                <td>₹{{ $lineTotal }}</td>
                                                                @if ($order->order_status == 'Completed')
                                                                <td>
                                                                    <div class="review-pop">
                                                                        @if ($item->review)
                                                                            <div class="p-3 border rounded">
                                                                                <strong>Your Review:</strong>
                                                                                <div style="color:#f0a500;font-size:1.1rem;">
                                                                                    @for ($i = 1; $i <= 5; $i++)
                                                                                        {{ $i <= $item->review->rating ? '★' : '☆' }}
                                                                                    @endfor
                                                                                </div>
                                                                                <p class="mb-0">{{ $item->review->review }}</p>
                                                                            </div>
                                                                        @else
                                                                            @if ($order->order_status === 'Completed')
                                                                                <button class="btn btn-sm btn-outline-primary reviewModal"
                                                                                        data-toggle="modal"
                                                                                        data-target="#reviewModal-{{ $item->store_order_id }}"
                                                                                        onclick="event.stopPropagation()">
                                                                                    ★ Add Review
                                                                                </button>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <hr class="fk-divider">

                                            {{-- Bottom: Price Summary + Payment --}}
                                            <div class="fk-bottom-row">

                                                {{-- Payment Details --}}
                                                <div class="Payment-de">
                                                    <h6 class="Payment_head">Payment Details</h6>
                                                    <div class="payment-box">
                                                        @if ($order->payment)
                                                            <div class="payment-method">
                                                                <span class="py-label">Payment ID</span>
                                                                <span class="py-value">{{ $order->payment->payment_id ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="payment-method">
                                                                <span class="py-label">Transaction #</span>
                                                                <span class="py-value">{{ $order->payment->transaction_number ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="payment-method">
                                                                <span class="py-label">Status</span>
                                                                <span>
                                                                    <span class="badge
                                                                        @if ($order->payment->payment_status === 'SUCCESS') bg-success
                                                                        @elseif($order->payment->payment_status === 'FAILED') bg-danger
                                                                        @else bg-warning text-dark @endif">
                                                                        {{ ucfirst($order->payment->payment_status ?? 'N/A') }}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                            <div class="payment-method">
                                                                <span class="py-label">Payment Date</span>
                                                                <span class="py-value">{{ \Carbon\Carbon::parse($order->payment->created_at ?? $order->created_at)->format('d M Y, h:i A') }}</span>
                                                            </div>
                                                        @endif
                                                        <div class="payment-method">
                                                            <span class="py-label">Method</span>
                                                            <span class="py-value">{{ ucfirst($order->payment->method ?? $order->payment_method) }}</span>
                                                        </div>
                                                        <div class="payment-method">
                                                            <span class="py-label">Amount</span>
                                                            <span class="py-value" style="font-size:14px;font-weight:700;color:#4B3B2E;">₹{{ $order->payment->amount ?? $order->total_price }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Price Summary --}}
                                                <div class="price-summary">
                                                    <div class="fk-section-head" style="margin-bottom:10px;">Price Summary</div>
                                                    <div class="price-row">
                                                        <span class="label">Sub Total</span>
                                                        <span class="value">₹{{ $subTotal }}</span>
                                                    </div>
                                                    <div class="price-row discount">
                                                        <span class="label">
                                                            Coupon
                                                            @if ($order->coupon)
                                                                <span style="font-size:10px;background:#e8d9c8;color:#4B3B2E;padding:1px 6px;border-radius:2px;margin-left:4px;">{{ $order->coupon->coupon_name }}</span>
                                                            @endif
                                                        </span>
                                                        <span class="value">−₹{{ $order->coupon_discount }}</span>
                                                    </div>
                                                    <div class="price-row">
                                                        <span class="label">Shipping Fee</span>
                                                        <span class="value">₹{{ $order->delivery_charge }}</span>
                                                    </div>
                                                    @if ($order->gift_wrap_value > 0)
                                                        <div class="price-row">
                                                            <span class="label">Gift Wrap</span>
                                                            <span class="value">₹{{ $order->gift_wrap_value }}</span>
                                                        </div>
                                                    @endif
                                                    @if ($order->cod_charge > 0)
                                                        <div class="price-row">
                                                            <span class="label">COD Charge</span>
                                                            <span class="value">₹{{ $order->cod_charge }}</span>
                                                        </div>
                                                    @endif
                                                    <div class="price-row GrandTotal">
                                                        <span class="label">Grand Total</span>
                                                        <span class="value">₹{{ $order->total_price }}</span>
                                                    </div>
                                                </div>

                                            </div>{{-- /.fk-bottom-row --}}

                                        </div>{{-- /.fk-order-body --}}
                                    </div>{{-- /.fk-order-body-wrap --}}

                                </div>{{-- /.fk-order-card --}}

                            @endforeach
                            </div>{{-- /.fk-orders-grid --}}

                            {{-- Pagination --}}
                            <div class="mt-4">
                                {{ $orders->links('pagination::bootstrap-5') }}
                            </div>

                        @else
                            <div class="no-orders text-center py-5">
                                <h3>No Orders Found</h3>
                                <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                            </div>
                        @endif

                    </div>{{-- /.col --}}
                </div>{{-- /.row --}}
            </div>
        </div>
    </div>

    {{-- ── Review Modals ── --}}
    @foreach ($orders as $order)
        @foreach ($order->orderItems as $item)
            <div class="modal fade" id="reviewModal-{{ $item->store_order_id }}"
                 tabindex="-1"
                 aria-labelledby="reviewModalLabel-{{ $item->store_order_id }}"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content" style="border:none;border-radius:6px;overflow:hidden;">
                        <div class="modal-header" style="background:#4B3B2E;border:none;">
                            <h5 class="modal-title" style="color:#FFF7ED;font-size:15px;font-weight:700;">
                                Rate &amp; Review
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    style="color:#FFF7ED;opacity:1;background:none;border:none;font-size:20px;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="padding:20px;">
                            <form action="{{ route('frontend.order.review.store', $item->store_order_id) }}" method="POST">
                                @csrf
                                <p style="font-size:13px;font-weight:600;color:#4B3B2E;margin-bottom:16px;">
                                    {{ $item->product_name }}
                                </p>
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:13px;font-weight:600;color:#4B3B2E;">
                                        Rating <span class="text-danger">*</span>
                                    </label>
                                    <div class="star-rating d-flex align-items-center"
                                         data-item-id="{{ $item->store_order_id }}">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="la la-star star me-1"
                                               data-value="{{ $i }}"
                                               style="font-size:30px;cursor:pointer;color:#ccc;"></i>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating"
                                           id="rating-input-{{ $item->store_order_id }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="review-{{ $item->store_order_id }}"
                                           class="form-label" style="font-size:13px;font-weight:600;color:#4B3B2E;">
                                        Your Review <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control"
                                              id="review-{{ $item->store_order_id }}"
                                              name="review" rows="4"
                                              placeholder="Share your experience..."
                                              style="border-color:#e8d9c8;font-size:13px;"
                                              required></textarea>
                                </div>
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-dismiss="modal">Cancel</button>
                                    <button type="submit"
                                            style="background:#4B3B2E;color:#FFF7ED;border:none;padding:8px 22px;border-radius:4px;font-size:13px;font-weight:700;cursor:pointer;">
                                        Submit Review
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

</main>

@push('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Frontend\Profile\ProfileUpdateRequest', '#profile-update-form') !!}
@endpush

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* ── Accordion Toggle ── */
    function toggleOrder(cartId, event) {
        // Don't toggle when clicking links/buttons inside header
        if (event.target.closest('a') || event.target.closest('button')) return;

        const card    = document.getElementById('order-card-' + cartId);
        const isOpen  = card.classList.contains('open');

        card.classList.toggle('open', !isOpen);
    }

    /* ── Star Rating ── */
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.star-rating').forEach(function (container) {
            const stars  = container.querySelectorAll('.star');
            const itemId = container.dataset.itemId;
            const input  = document.getElementById('rating-input-' + itemId);

            stars.forEach(star => {
                star.addEventListener('mouseover', function () {
                    const val = parseInt(this.dataset.value);
                    stars.forEach((s, i) => s.style.color = i < val ? '#f0a500' : '#ccc');
                });
                star.addEventListener('mouseout', function () {
                    const val = parseInt(input.value) || 0;
                    stars.forEach((s, i) => s.style.color = i < val ? '#f0a500' : '#ccc');
                });
                star.addEventListener('click', function () {
                    const val = parseInt(this.dataset.value);
                    input.value = val;
                    stars.forEach((s, i) => s.style.color = i < val ? '#f0a500' : '#ccc');
                });
                star.addEventListener('touchstart', function (e) {
                    e.preventDefault();
                    const val = parseInt(this.dataset.value);
                    input.value = val;
                    stars.forEach((s, i) => s.style.color = i < val ? '#f0a500' : '#ccc');
                });
            });
        });
    });
</script>

@endsection