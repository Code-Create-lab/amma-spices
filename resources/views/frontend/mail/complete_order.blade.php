<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Completed - Amma's Spices</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family: Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 10px; background:#f5f5f5;">
        <tr>
            <td align="center">

                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="max-width:600px; width:100%; background:#ffffff; border:1px solid #e5e7eb;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding:25px; border-bottom:1px solid #e5e7eb;">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="Amma's Spices" height="36">
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td style="padding:25px;">
                            <h2 style="margin:0 0 10px 0; font-size:20px; color:#111827;">
                                Order Completed
                            </h2>
                            <p style="margin:0; font-size:14px; color:#374151; line-height:1.6;">
                                Hi {{ $order->address->receiver_name ?? 'Customer' }},<br>
                                Your order has been successfully delivered. Thank you for shopping with Amma's Spices
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
                                        {{ $order->created_at ?? now()->format('d M Y') }}<br>
                                        <strong>Payment Method:</strong> {{ $order->payment_method ?? 'N/A' }}<br>
                                        <strong>Status:</strong> Completed
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Items -->
                    <tr>
                        <td style="padding:0 25px 25px 25px;">
                            <h3 style="margin:0 0 10px 0; font-size:16px; color:#111827;">
                                Order Items
                            </h3>

                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th align="left"
                                            style="padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px;">
                                            Product</th>
                                        <th align="center"
                                            style="padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px;">Qty
                                        </th>
                                        <th align="right"
                                            style="padding:10px; border-bottom:1px solid #e5e7eb; font-size:13px;">Price
                                        </th>
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

                    <!-- Summary -->
                    <tr>
                        <td style="padding:0 25px 25px 25px;">
                            <h3 style="margin:0 0 10px 0; font-size:16px; color:#111827;">
                                Order Summary
                            </h3>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size:14px;">Subtotal</td>
                                    <td align="right" style="font-size:14px;">
                                        ₹{{ number_format($order->price_without_delivery + ($order->coupon_discount ?? 0), 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size:14px;">Delivery Charges</td>
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
                                        Total Paid
                                    </td>
                                    <td align="right" style="padding-top:10px; font-size:16px; font-weight:bold;">
                                        ₹{{ number_format($order->rem_price ?? 0, 2) }}
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
                                If you have any questions, contact us at <strong>info@ammasspices.com </strong>
                                or <strong>+91 880 0952 006</strong>.
                            </p>
                            <p style="margin:8px 0 0 0;">
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
