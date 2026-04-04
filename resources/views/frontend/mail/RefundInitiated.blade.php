<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Initiated</title>
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
                                Refund Initiated
                            </h2>
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td style="font-size:14px; line-height:22px; padding-bottom:20px;">
                            Hi {{ $order->user->name ?? 'Customer' }},<br><br>
                            Your refund has been initiated successfully. Depending on your bank or payment provider,
                            it may take <strong>5–7 business days</strong> for the amount to be credited to your
                            account.
                        </td>
                    </tr>

                    <!-- Refund Details -->
                    <tr>
                        <td style="font-size:14px; line-height:22px; padding-bottom:20px;">
                            <strong>Order ID:</strong> #{{ $order->cart_id ?? ($order->order_id ?? 'N/A') }}<br>
                            <strong>Company:</strong> Coco and Co<br>
                            <strong>Refund Amount:</strong>
                            ₹{{ number_format($order->refund->refund_amount ?? ($order->total_price ?? 0), 2) }}<br>
                            <strong>Refund ID:</strong> {{ $order->refund->refund_id ?? 'N/A' }}<br>
                            <strong>Initiated On:</strong>
                            {{ $order->refund->refunded_at
                                ? \Carbon\Carbon::parse($order->refund->refunded_at)->format('Y-m-d')
                                : now()->format('Y-m-d') }}<br>
                            <strong>Original Payment Amount:</strong>
                            ₹{{ number_format($order->refund->amount ?? ($order->total_price ?? 0), 2) }}<br>
                            <strong>Payment ID:</strong> {{ $order->refund->payment_id ?? 'N/A' }}<br>
                            <strong>Payment Method:</strong> {{ strtoupper($order->payment->method ?? 'N/A') }}<br>
                            <strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}<br>
                            <strong>Mobile Number:</strong> {{ $order->user->user_phone ?? 'N/A' }}
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

                                @if ($order->orderItems && $order->orderItems->isNotEmpty())
                                    @foreach ($order->orderItems as $item)
                                        <tr style="border-bottom:1px solid #eeeeee;">
                                            <td>{{ $item->product_name ?? 'Product' }}</td>
                                            <td align="center">{{ $item->quantity ?? 1 }}</td>
                                            <td align="right">
                                                ₹{{ number_format($item->variation->price ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" align="center">No items found</td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Message -->
                    <tr>
                        <td align="center" style="padding-top:25px; font-size:14px; line-height:22px;">
                            If you have any questions regarding this refund, please contact our support team.
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="padding-top:30px; font-size:13px; line-height:20px; border-top:1px solid #dddddd;">
                            Contact us at <strong>info@ammasspices.com </strong> or <strong>+91 880 0952 006</strong>.
                            <br><br>
                            © {{ date('Y') }} Amma's Spices. All rights reserved.
                        </td>
                    </tr>

                </table>
                <!-- End Container -->

            </td>
        </tr>
    </table>

</body>

</html>
