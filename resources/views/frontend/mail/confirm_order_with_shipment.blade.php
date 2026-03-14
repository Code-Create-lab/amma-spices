<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Amma's Spices</title>
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
                            <img src="{{ asset('assets/images/logo.png') }}" alt="Amma's Spices" height="70">
                        </td>
                    </tr>

                    <!-- Intro -->
                    <tr>
                        <td style="padding:30px;">
                            <h2 style="margin:0 0 10px; font-size:20px; color:#111827;">
                                Your Order is Confirmed 🎉
                            </h2>
                            <p style="margin:0; font-size:14px; color:#374151; line-height:1.6;">
                                Hi {{ $order->address->receiver_name ?? 'Customer' }},<br>
                                Great news! Your order has been confirmed and is being prepared for shipment.
                            </p>
                        </td>
                    </tr>

                    <!-- Order Info -->
                    <tr>
                        <td style="padding:0 30px 25px;">
                            <p style="font-size:14px; color:#374151; line-height:1.6; margin:0;">
                                <strong>Order ID:</strong> #{{ $order->cart_id ?? 'N/A' }}<br>
                                <strong>Order Date:</strong>
                                {{ $order->order_date ?? now()->format('d M Y, h:i A') }}<br>
                                <strong>Payment Method:</strong> {{ $order->payment_method ?? 'Online' }}
                            </p>
                        </td>
                    </tr>

                    <!-- Tracking -->
                    <tr>
                        <td align="center" style="padding:10px 30px 30px;">
                            <p style="font-size:14px; color:#374151; margin-bottom:15px;">
                                You can track your shipment using the link below:
                            </p>

                            <a href="{{ $shippingUrlInhouse }}"
                                style="display:inline-block; padding:12px 22px; background:#111827; color:#ffffff;
                                  text-decoration:none; font-size:14px; border-radius:4px;">
                                Track Your Order
                            </a>

                            <p style="font-size:12px; color:#6b7280; margin-top:12px;">
                                If the button doesn’t work, copy and paste this link into your browser:<br>
                                <span style="word-break:break-all;">{{ $shippingUrlInhouse }}</span>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="padding:25px; border-top:1px solid #e5e7eb; font-size:12px; color:#6b7280;">
                            <p style="margin:0;">
                                Need help? Contact us at
                                <strong>info@bodhiblisssoap.com</strong> or <strong>+91 900 8741 100</strong>
                            </p>
                            <p style="margin:10px 0 0;">
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
