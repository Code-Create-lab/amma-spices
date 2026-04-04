<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family: Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:30px 10px; background:#f5f5f5;">

                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; width:100%; max-width:700px; border:1px solid #e5e7eb;padding: 20px;">

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
                                Payment Failed
                            </h2>
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td style="font-size:14px; line-height:22px; padding-bottom:20px;">
                            Hi {{ $order->address->receiver_name ?? 'Valued Customer' }},<br><br>
                            We were unable to process your payment. Your order has not been confirmed.
                            Please try again or contact our support team if the issue persists.
                        </td>
                    </tr>

                    <!-- Payment Details -->
                    <tr>
                        <td style="font-size:14px; line-height:22px; padding-bottom:20px;">
                            <strong>Order ID:</strong> #{{ $order->cart_id ?? ($order->order_id ?? 'N/A') }}<br>
                            <strong>Company:</strong> Amma's Spices<br>
                            <strong>Payment Amount:</strong> ₹{{ number_format($order->total_price ?? 0, 2) }}<br>
                            <strong>Payment ID:</strong> {{ $order->payment->payment_id ?? 'N/A' }}<br>
                            <strong>Payment Method:</strong> {{ strtoupper($order->payment->method ?? 'N/A') }}<br>
                            <strong>Payment Status:</strong> FAILED<br>
                            <strong>Payment Date:</strong>
                            {{ $order->payment && $order->payment->created_at
                                ? \Carbon\Carbon::parse($order->payment->created_at)->format('Y-m-d')
                                : now()->format('Y-m-d') }}
                        </td>
                    </tr>

                    <!-- Failure Reason -->
                    @if (!empty($order->payment->failure_reason))
                        <tr>
                            <td style="font-size:14px; line-height:22px; padding-bottom:20px;">
                                <strong>Failure Reason:</strong> {{ $order->payment->failure_reason }}
                            </td>
                        </tr>
                    @endif

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
                                                ₹{{ number_format(($item->variation->price == 0 ? $item->variation->mrp : $item->variation->price) ?? 0, 2) }}
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

                    <!-- Retry Info -->
                    <tr>
                        <td align="center" style="padding-top:25px; font-size:14px; line-height:22px;">
                            You may retry the payment from your account dashboard or place the order again.
                            If the problem continues, our support team will be happy to assist you.
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="padding-top:30px; font-size:13px; line-height:20px; border-top:1px solid #dddddd;">
                            <strong>info@ammasspices.com </strong> or <strong>+91 880 0952 006</strong>.
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
