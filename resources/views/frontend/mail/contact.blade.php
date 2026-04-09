<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Us Message</title>
    <style>
        @media print {

            .hidden-print,
            .hidden-print * {
                display: none !important;
            }
        }
    </style>
</head>

<body
    style="margin: 0 !important; padding: 0 !important; font-family: Arial, sans-serif; background-color: #f5f5f5; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
    <div style="padding: 20px 0;">
        <div
            style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">

            <!-- Header -->
            <div
                style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); background-color: #3498db; padding: 20px; text-align: center; color: #ffffff;">
                <div
                    style="background-color: #ffffff; border-radius: 8px; width: 120px; height: auto; margin: 0 auto 15px auto; padding: 10px;">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="ZAKH Logo"
                        style="width: 100px; height: auto; display: block; margin: 0 auto; border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic;" />
                </div>
                <br><br>
                <h1 style="color: #ffffff; font-size: 22px; font-weight: bold; margin: 0 0 5px 0; text-align: center;">
                    📧 New Contact Message</h1>
                <p style="color: #ffffff; font-size: 16px; margin: 0; text-align: center;">You have received a new
                    message from your contact form!</p>
            </div>

            <!-- Content -->
            <div style="padding: 30px;">
                <p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.6;">Hello Admin,</p>
                <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6;">You have received a new contact form
                    submission from your website. Here are the details:</p>

                <!-- Message Info -->
                <div style="background-color: #e8f4fd; border-left: 4px solid #3498db; padding: 15px; margin: 20px 0;">
                    <h3 style="color: #2980b9; font-weight: bold; margin: 0 0 10px 0;">ℹ️ Message Details</h3>
                    <div style="color: #1e3a8a; line-height: 1.4;">
                        <strong>Received Date:</strong>
                        {{ $data['created_at'] ?? now()->format('F j, Y \a\t g:i A') }}<br>
                        {{-- <strong>Message ID:</strong> #{{ $data->id  }}<br> --}}
                        <strong>Status:</strong> <span style="color: #e67e22; font-weight: bold;">New Message</span>
                    </div>
                </div>

                <!-- Contact Information -->
                <div style="background-color: #f8f9ff; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <!-- Contact Header -->
                    <div style="border-bottom: 2px solid #e1e5e9; padding-bottom: 15px; margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td
                                    style="font-size: 18px; font-weight: bold; color: #2c3e50; text-align: left; padding: 0;">
                                    Contact Information</td>
                                <td
                                    style="color: #3498db; font-size: 14px; font-weight: bold; text-align: right; padding: 0;">
                                    NEW</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Contact Details -->
                    <div style="margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 12px 0; color: #2c3e50; font-weight: bold; width: 30%;">👤 Name:
                                </td>
                                <td style="padding: 12px 0; color: #7f8c8d; font-size: 16px;">{{ $data['full_name'] }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 0; color: #2c3e50; font-weight: bold;">📧 Email:</td>
                                <td style="padding: 12px 0; color: #3498db; font-size: 16px;">
                                    <a href="mailto:{{ $data['email'] }}"
                                        style="color: #3498db; text-decoration: none;">{{ $data['email'] }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 0; color: #2c3e50; font-weight: bold;">📱 Mobile:</td>
                                <td style="padding: 12px 0; color: #7f8c8d; font-size: 16px;">
                                    <a href="tel:{{ $data['phone_number'] }}"
                                        style="color: #27ae60; text-decoration: none;">{{ $data['phone_number'] }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 0; color: #2c3e50; font-weight: bold;">📝 Subject:</td>
                                <td style="padding: 12px 0; color: #7f8c8d; font-size: 16px; font-weight: bold;">
                                    {{ $data['subject'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 0; color: #2c3e50; font-weight: bold;">📅 Date:</td>
                                <td style="padding: 12px 0; color: #7f8c8d; font-size: 16px;">
                                    {{ $data['created_at'] ?? now()->format('F j, Y \a\t g:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Message Content -->
                <div
                    style="background-color: #ffffff; border: 2px solid #e1e5e9; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <h3 style="color: #2c3e50; font-size: 18px; font-weight: bold; margin: 0 0 15px 0;">💬 Message
                        Content</h3>
                    <div
                        style="background-color: #f8f9fa; border-radius: 6px; padding: 15px; border-left: 4px solid #6c757d;">
                        <p style="color: #495057; font-size: 16px; line-height: 1.6; margin: 0; white-space: pre-wrap;">
                            {{ $data['message'] }}</p>
                    </div>
                </div>

                <!-- Action Required -->
                <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
                    <h3 style="color: #856404; font-weight: bold; margin: 0 0 10px 0;">⚡ Action Required</h3>
                    <div style="color: #664d03; line-height: 1.4;">
                        • Review the message content and customer details<br>
                        • Respond to the customer within 24 hours<br>
                        • Use the provided email or phone number for contact<br>
                        • Mark this inquiry as resolved once addressed<br>
                        • Follow up if additional information is needed
                    </div>
                </div>

                <!-- Quick Actions -->
                <div style="text-align: center; margin: 30px 0;">
                    <table style="margin: 0 auto; border-collapse: separate; border-spacing: 10px;">
                        <tr>
                            <td>
                                <a href="mailto:{{ $data['email'] }}?subject=Re: {{ $data['subject'] }}"
                                    style="background-color: #3498db; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                                    📧 Reply via Email
                                </a>
                            </td>
                            <td>
                                <a href="tel:{{ $data['phone_number'] }}"
                                    style="background-color: #27ae60; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                                    📞 Call Now
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Response Template -->
                <div style="background-color: #e8f5e8; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
                    <h3 style="color: #155724; font-weight: bold; margin: 0 0 10px 0;">📋 Suggested Response Template
                    </h3>
                    <div style="color: #155724; line-height: 1.4; font-size: 14px; font-style: italic;">
                        "Dear {{ $data['full_name'] ?? 'Customer' }},<br><br>
                        Thank you for contacting ZAKH. We have received your message regarding '{{ $data['subject'] }}'
                        and appreciate you taking the time to reach out to us.<br><br>
                        [Your personalized response here]<br><br>
                        Best regards,<br>
                        ZAKH Customer Service Team"
                    </div>
                </div>

                <!-- Summary -->
                <div style="text-align: center; margin: 30px 0;">
                    <h2 style="color: #2c3e50; font-size: 20px; margin: 0 0 10px 0;">📋 Contact Summary</h2>
                    <p style="color: #7f8c8d; margin: 0;">New contact form submission from
                        <strong>{{ $data['full_name'] }}</strong> regarding <strong>{{ $data['subject'] }}</strong>.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div style="background-color: #2c3e50; color: #ffffff; padding: 30px; text-align: center;">
                <p style="color: #ffffff; text-align: center; margin: 0 0 20px 0;"><strong>ZAKH</strong></p>
                <div style="color: #d4d4d4; line-height: 180%; text-align: center;">
                    <p style="font-size: 14px; line-height: 180%; margin: 0;">
                        <span style="font-family: Rubik, sans-serif; font-size: 14px; line-height: 25.2px;">Copyright ©
                            2025, Amma's Spices</span><br>
                        <span style="font-family: Rubik, sans-serif; font-size: 14px; line-height: 25.2px;">
                            Shop No. UFF29 Signature Global, Sector 95A Gurugram, Haryana 122505

                        </span><br>
                        <span style="font-family: Rubik, sans-serif; font-size: 14px; line-height: 25.2px;">
                            📞 880 0952 006
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    {{-- @dd($data) --}}
</body>

</html>
