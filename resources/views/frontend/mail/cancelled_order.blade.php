<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Cancelled</title>
</head>

<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background:#ffffff; color:#000000;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:30px 15px;">

                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding-bottom:25px;">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="Amma's Spices"
                                style="max-width:140px;">
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td style="padding-bottom:20px;">
                            <h2 style="margin:0; font-size:22px; font-weight:600;">
                                Order Cancelled
                            </h2>
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td style="font-size:14px; line-height:22px; padding-bottom:20px;">
                            Hi {{ $order->customer_name }},<br><br>
                            Your order has been successfully cancelled. If any payment was made, the refund (if
                            applicable)
                            will be processed according to our refund policy.
                        </td>
                    </tr>

                    <!-- Order Info -->
                    <tr>
                        <td style="font-size:14px; line-height:22px; padding-bottom:20px;">
                            <strong>Order ID:</strong> {{ $order->cart_id }}<br>
                            <strong>Order Date:</strong> {{ $order->order_date }}<br>
                            <strong>Payment Method:</strong> {{ $order->payment_method }}<br>
                            <strong>Payment Status:</strong> Cancelled
                        </td>
                    </tr>

                    <!-- Order Items -->
                    <tr>
                        <td style="padding-bottom:10px;">
                            <strong>Order Items</strong>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <table width="100%" cellpadding="8" cellspacing="0"
                                style="border-collapse:collapse; font-size:14px;">
                                <tr style="border-bottom:1px solid #cccccc;">
                                    <th align="left">Product</th>
                                    <th align="center">Qty</th>
                                    <th align="right">Price</th>
                                </tr>

                                @foreach ($order->orderItems as $item)
                                    <tr style="border-bottom:1px solid #eeeeee;">
                                        <td>{{ $item->product_name }}</td>
                                        <td align="center">{{ $item->quantity }}</td>
                                        <td align="right">₹{{ number_format($item->price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <!-- Totals -->
                    <tr>
                        <td style="padding-top:15px; font-size:14px; line-height:22px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="right">Subtotal:</td>
                                    <td align="right" width="120">
                                        ₹{{ number_format($order->price_without_delivery + ($order->coupon_discount ?? 0), 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">Shipping:</td>
                                    <td align="right">₹{{ number_format($order->delivery_charge, 2) }}</td>
                                </tr>
                                @if ($order->coupon_discount > 0)
                                    <tr>
                                        <td align="right">Coupon Discount:</td>
                                        <td align="right" width="120">
                                            ₹{{ number_format($order->coupon_discount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td align="right"><strong>Total:</strong></td>
                                    <td align="right"><strong>₹{{ number_format($order->total_price, 2) }}</strong>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Shipping Address -->
                    <tr>
                        <td style="padding-top:25px; font-size:14px; line-height:22px;">
                            <strong>Shipping Address</strong><br><br>
                            {{ $order->address->receiver_name }}<br>
                            {{ $order->address->house_no ?? '' }} {{ $order->address->society ?? '' }}<br>
                            {{ $order->address->city }}, {{ $order->address->state }} -
                            {{ $order->address->pincode }}<br>
                            Phone: {{ $order->address->receiver_phone }}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding-top:30px; font-size:13px; line-height:20px; border-top:1px solid #dddddd;">
                            If you have any questions, contact us at
                            <strong>info@bodhiblisssoap.com</strong> or <strong>+91 900 8741 100</strong>.
                            <br><br>
                            © {{ date('Y') }} Amma's Spices All rights reserved.
                        </td>
                    </tr>

                </table>
                <!-- End Container -->

            </td>
        </tr>
    </table>

</body>

</html>
