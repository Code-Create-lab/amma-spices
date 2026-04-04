@if (!$isAdmin)
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Confirmation - Amma's Spices</title>
    </head>

    <body style="margin:0; padding:0; background:#f5f5f5; font-family: Arial, Helvetica, sans-serif;">

        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5; padding:30px 10px;">
            <tr>
                <td align="center">

                    <!-- Main Container -->
                    <table width="700" cellpadding="0" cellspacing="0"
                        style="background:#ffffff; width:100%; max-width:700px; border:1px solid #e5e7eb;">

                        <!-- Header -->
                        <tr>
                            <td align="center" style="padding:30px; border-bottom:1px solid #e5e7eb;">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="Amma's Spices" height="80">
                            </td>
                        </tr>

                        <!-- Intro -->
                        <tr>
                            <td style="padding:30px;">
                                <h2 style="margin:0 0 10px 0; font-size:20px; color:#111827;">
                                    Order Placed Successfully
                                </h2>
                                <p style="margin:0; font-size:14px; color:#374151; line-height:1.6;">
                                    Hi {{ $order->address->receiver_name ?? 'Customer' }},<br>
                                    Thank you for your order. We have received your purchase and are preparing it for
                                    shipment.
                                </p>
                            </td>
                        </tr>

                        <!-- Order Info -->
                        <tr>
                            <td style="padding:0 30px 30px 30px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:14px; color:#374151;">
                                            <strong>Order ID:</strong> #{{ $order->cart_id ?? 'N/A' }}<br>
                                            <strong>Order Date:</strong>
                                            {{ $order->order_date ?? now()->format('d M Y, h:i A') }}<br>
                                            <strong>Payment Method:</strong>
                                            {{ $order->payment_method ?? 'Online' }}<br>
                                            <strong>Payment Status:</strong>
                                            {{ ucfirst($order->payment_status ?? 'pending') }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Items -->
                        <tr>
                            <td style="padding:0 30px 30px 30px;">
                                <h3 style="margin:0 0 15px 0; font-size:16px; color:#111827;">
                                    Order Items
                                </h3>

                                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                    <thead>
                                        <tr>
                                            <th align="left"
                                                style="padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px;">
                                                Product</th>
                                            <th align="center"
                                                style="padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px;">
                                                Qty</th>
                                            <th align="right"
                                                style="padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px;">
                                                Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $subtotal = 0; @endphp
                                        @foreach ($order->orderItems ?? [] as $item)
                                            @php
                                                $price =
                                                    ($item->variation->price == 0
                                                        ? $item->variation->mrp
                                                        : $item->variation->price) ?? 0;
                                                $qty = $item->quantity ?? 1;
                                                $total = $price * $qty;
                                                $subtotal += $total;
                                            @endphp
                                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                                <td style="padding:12px 10px; font-size:14px; color:#374151;">
                                                    {{ $item->product_name }}
                                                </td>
                                                <td align="center" style="padding:12px 10px; font-size:14px;">
                                                    {{ $qty }}
                                                </td>
                                                <td align="right" style="padding:12px 10px; font-size:14px;">
                                                    ₹{{ number_format($total, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <!-- Totals -->
                        <tr>
                            <td style="padding:0 30px 30px 30px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:14px;">Subtotal</td>
                                        <td align="right" style="font-size:14px;">₹{{ number_format($subtotal, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:14px;">Shipping</td>
                                        <td align="right" style="font-size:14px;">
                                            ₹{{ number_format($order->delivery_charge ?? 0, 2) }}
                                        </td>
                                    </tr>
                                    @if ($order->coupon_discount ?? 0)
                                        <tr>
                                            <td style="font-size:14px;">Discount</td>
                                            <td align="right" style="font-size:14px;">
                                                -₹{{ number_format($order->coupon_discount, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="padding-top:10px; font-size:16px; font-weight:bold;">
                                            Total
                                        </td>
                                        <td align="right" style="padding-top:10px; font-size:16px; font-weight:bold;">
                                            ₹{{ number_format($order->total_price ?? 0, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Address -->
                        @if ($order->address ?? false)
                            <tr>
                                <td style="padding:0 30px 30px 30px;">
                                    <h3 style="margin:0 0 10px 0; font-size:16px;">Shipping Address</h3>
                                    <p style="margin:0; font-size:14px; color:#374151; line-height:1.6;">
                                        {{ $order->address->receiver_name }}<br>
                                        {{ $order->address->house_no ?? '' }} {{ $order->address->society ?? '' }}<br>
                                        {{ $order->address->city }}, {{ $order->address->state }} -
                                        {{ $order->address->pincode }}<br>
                                        Phone: {{ $order->address->receiver_phone }}
                                    </p>
                                </td>
                            </tr>
                        @endif

                        <!-- Footer -->
                        <tr>
                            <td align="center"
                                style="padding:25px; border-top:1px solid #e5e7eb; font-size:12px; color:#6b7280;">
                                <p style="margin:0;">
                                    If you have any questions, contact us at
                                    <strong>info@ammasspices.com </strong> or <strong>+91 880 0952 006</strong>.
                                </p>
                                <p style="margin:10px 0 0 0;">
                                    © {{ date('Y') }} Amma's Spices. All rights reserved.
                                </p>
                            </td>
                        </tr>

                    </table>

                </td>
            </tr>
        </table>

    </body>

    </html>
@else
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>New Order Received - Amma's Spices Soap Co Admin</title>
    </head>

    <body style="margin:0; padding:0; background:#f5f5f5; font-family: Arial, Helvetica, sans-serif;">

        <table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 10px; background:#f5f5f5;">
            <tr>
                <td align="center">

                    <!-- Container -->
                    <table width="760" cellpadding="0" cellspacing="0"
                        style="max-width:760px; width:100%; background:#ffffff; border:1px solid #e5e7eb;">

                        <!-- Header -->
                        <tr>
                            <td align="center" style="padding:25px; border-bottom:1px solid #e5e7eb;">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="Amma's Spices Soap Co"
                                    height="80">
                                <p style="margin:8px 0 0 0; font-size:12px; color:#6b7280;">
                                    Admin Dashboard – Order Notification
                                </p>
                            </td>
                        </tr>

                        <!-- Title -->
                        <tr>
                            <td style="padding:25px;">
                                <h2 style="margin:0 0 10px 0; font-size:20px; color:#111827;">
                                    New Order Received
                                </h2>
                                <p style="margin:0; font-size:14px; color:#374151;">
                                    A new order has been placed and requires review.
                                </p>
                            </td>
                        </tr>

                        <!-- Order Info -->
                        <tr>
                            <td style="padding:0 25px 25px 25px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:14px; color:#374151; line-height:1.6;">
                                            <strong>Order ID:</strong> #{{ $order->cart_id ?? 'N/A' }}<br>
                                            <strong>Order Date:</strong>
                                            {{ $order->order_date ?? now()->format('d M Y, h:i A') }}<br>
                                            <strong>Payment Method:</strong>
                                            {{ $order->payment_method ?? 'Online' }}<br>
                                            <strong>Payment Status:</strong>
                                            {{ strtoupper($order->payment_status ?? 'pending') }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Customer Info -->
                        <tr>
                            <td style="padding:0 25px 25px 25px;">
                                <h3 style="margin:0 0 10px 0; font-size:16px; color:#111827;">
                                    Customer Details
                                </h3>
                                <p style="margin:0; font-size:14px; color:#374151; line-height:1.6;">
                                    <strong>Name:</strong> {{ $order->address->receiver_name ?? 'N/A' }}<br>
                                    <strong>Email:</strong> {{ $order->address->receiver_email ?? 'N/A' }}<br>
                                    <strong>Phone:</strong> {{ $order->address->receiver_phone ?? 'N/A' }}
                                </p>
                            </td>
                        </tr>

                        <!-- Items -->
                        <tr>
                            <td style="padding:0 25px 25px 25px;">
                                <h3 style="margin:0 0 10px 0; font-size:16px; color:#111827;">
                                    Order Items
                                </h3>

                                <table width="100%" cellpadding="0" cellspacing="0"
                                    style="border-collapse:collapse;">
                                    <thead>
                                        <tr>
                                            <th align="left"
                                                style="padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px;">
                                                Product</th>
                                            <th align="center"
                                                style="padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px;">
                                                Qty</th>
                                            <th align="right"
                                                style="padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px;">
                                                Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $subtotal = 0; @endphp
                                        @foreach ($order->orderItems ?? [] as $item)
                                            @php
                                                $price =
                                                    ($item->variation->price == 0
                                                        ? $item->variation->mrp
                                                        : $item->variation->price) ?? 0;
                                                $qty = $item->quantity ?? 1;
                                                $total = $price * $qty;
                                                $subtotal += $total;
                                            @endphp
                                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                                <td style="padding:10px; font-size:14px; color:#374151;">
                                                    {{ $item->product_name }}
                                                </td>
                                                <td align="center" style="padding:10px; font-size:14px;">
                                                    {{ $qty }}
                                                </td>
                                                <td align="right" style="padding:10px; font-size:14px;">
                                                    ₹{{ number_format($total, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <!-- Financial Summary -->
                        <tr>
                            <td style="padding:0 25px 25px 25px;">
                                <h3 style="margin:0 0 10px 0; font-size:16px; color:#111827;">
                                    Payment Summary
                                </h3>

                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:14px;">Subtotal</td>
                                        <td align="right" style="font-size:14px;">₹{{ number_format($subtotal, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:14px;">Shipping</td>
                                        <td align="right" style="font-size:14px;">
                                            ₹{{ number_format($order->delivery_charge ?? 0, 2) }}
                                        </td>
                                    </tr>
                                    @if ($order->coupon_discount ?? 0)
                                        <tr>
                                            <td style="font-size:14px;">Discount</td>
                                            <td align="right" style="font-size:14px;">
                                                -₹{{ number_format($order->coupon_discount, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="padding-top:10px; font-size:16px; font-weight:bold;">
                                            Total
                                        </td>
                                        <td align="right"
                                            style="padding-top:10px; font-size:16px; font-weight:bold;">
                                            ₹{{ number_format($order->total_price ?? 0, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Address -->
                        @if ($order->address ?? false)
                            <tr>
                                <td style="padding:0 25px 25px 25px;">
                                    <h3 style="margin:0 0 10px 0; font-size:16px;">
                                        Delivery Address
                                    </h3>
                                    <p style="margin:0; font-size:14px; color:#374151; line-height:1.6;">
                                        {{ $order->address->receiver_name }}<br>
                                        {{ $order->address->house_no ?? '' }} {{ $order->address->society ?? '' }}<br>
                                        {{ $order->address->city }}, {{ $order->address->state }} -
                                        {{ $order->address->pincode }}<br>
                                        Phone: {{ $order->address->receiver_phone }}
                                    </p>
                                </td>
                            </tr>
                        @endif

                        <!-- Footer -->
                        <tr>
                            <td align="center"
                                style="padding:20px; border-top:1px solid #e5e7eb; font-size:12px; color:#6b7280;">
                                <p style="margin:0;">
                                    This is an automated notification from the Amma's Spices Soap Co Admin Dashboard.
                                </p>
                                <p style="margin:8px 0 0 0;">
                                    © {{ date('Y') }} Amma's Spices Soap Co. All rights reserved.
                                </p>
                            </td>
                        </tr>

                    </table>

                </td>
            </tr>
        </table>

    </body>

    </html>

@endif
