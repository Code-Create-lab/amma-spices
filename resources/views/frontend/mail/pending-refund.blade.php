{{-- resources/views/emails/pending-refund.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Refund Notification</title>
</head>

<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border: 1px solid #ddd;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding-bottom:25px;">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="ZAKH" style="max-width:140px;">
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px 20px;">
                            <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: bold;">Action Required: Pending
                                Refund</h3>

                            <p style="margin: 0 0 15px 0;">Hello Admin,</p>

                            <p style="margin: 0 0 15px 0;">
                                A refund could not be processed automatically due to insufficient balance in the
                                Razorpay account.
                            </p>

                            <!-- Order Details -->
                            <table width="100%" cellpadding="8" cellspacing="0"
                                style="margin: 20px 0; border: 1px solid #ddd;">
                                <tr style="background-color: #f9f9f9;">
                                    <td colspan="2"
                                        style="padding: 10px; font-weight: bold; border-bottom: 1px solid #ddd;">
                                        Order Details
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 40%;">Order ID:</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">
                                        #{{ $order->cart_id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Payment ID:</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $payment->id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Refund Amount:</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">
                                        ₹{{ number_format($order->total_price, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Customer Name:</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                        {{ $order->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Customer Email:</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                        {{ $order->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px;">Requested Date:</td>
                                    <td style="padding: 8px;">{{ now()->format('d M Y, h:i A') }}</td>
                                </tr>
                            </table>

                            <!-- Action Required -->
                            <div
                                style="margin: 20px 0; padding: 15px; background-color: #fff9e6; border-left: 3px solid #ffc107;">
                                <p style="margin: 0 0 10px 0; font-weight: bold;">Required Action:</p>
                                <ol style="margin: 0; padding-left: 20px;">
                                    <li>Add funds to your Razorpay account</li>
                                    <li>Process the refund manually from the admin panel</li>
                                    <li>Or run the automated refund command: <code>php artisan refunds:process</code>
                                    </li>
                                </ol>
                            </div>

                            <p style="margin: 15px 0 0 0;">
                                The customer has been notified that their refund is being processed and will be credited
                                within 7-10 business days.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="padding: 20px; background-color: #f9f9f9; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
                            <p style="margin: 0 0 5px 0;">This is an automated notification from ZAKHEC Admin System</p>
                            <p style="margin: 0;">Please do not reply to this email</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
