<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Amma's Spices</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', 'Helvetica', sans-serif;
            background-color: #f5f5f5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .header {
            background-color: #FFF7ED;
            padding: 40px 20px;
            text-align: center;
            border-bottom: 3px solid #f4d9b8;
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #8B4513;
            letter-spacing: 2px;
            margin: 0;
        }

        .tagline {
            color: #A0522D;
            font-size: 14px;
            margin-top: 5px;
            font-style: italic;
        }

        .content {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }

        .greeting {
            font-size: 24px;
            color: #8B4513;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message {
            font-size: 16px;
            color: #555555;
            margin-bottom: 30px;
        }

        .otp-container {
            background-color: #FFF7ED;
            border: 2px dashed #f4d9b8;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }

        .otp-label {
            font-size: 14px;
            color: #A0522D;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .otp-code {
            font-size: 42px;
            font-weight: bold;
            color: #8B4513;
            letter-spacing: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }

        .expiry-text {
            font-size: 13px;
            color: #999999;
            margin-top: 15px;
        }

        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .warning-text {
            font-size: 14px;
            color: #856404;
            margin: 0;
        }

        .footer {
            background-color: #FFF7ED;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #f4d9b8;
        }

        .footer-text {
            font-size: 13px;
            color: #888888;
            line-height: 1.6;
            margin: 5px 0;
        }

        .footer-links {
            margin-top: 20px;
        }

        .footer-link {
            color: #8B4513;
            text-decoration: none;
            margin: 0 10px;
            font-size: 13px;
        }

        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }

            .otp-code {
                font-size: 36px;
                letter-spacing: 6px;
            }

            .greeting {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <div class="email-container">
                    <!-- Header -->
                    <div class="header">
                        <a href="{{ route('index') }}" class="logo"><img
                                src="{{ asset('assets/images/logo.png') }}" alt=" Logo" width="105"
                                height="25"></a>
                    </div>

                    <!-- Main Content -->
                    <div class="content">
                        <h2 class="greeting">Welcome, {{ $name }}! 🙏</h2>

                        <p class="message">
                            Thank you for choosing Amma's Spices. To complete your registration and secure your account,
                            please use the One-Time Password (OTP) below:
                        </p>

                        <!-- OTP Box -->
                        <div class="otp-container">
                            <div class="otp-label">Your Verification Code</div>
                            <div class="otp-code">{{ $otp }}</div>
                            <div class="expiry-text">⏱️ This code will expire in {{ $expiryMinutes }} minutes</div>
                        </div>

                        <p class="message">
                            Simply enter this code on the registration page to verify your email address and activate
                            your account.
                        </p>

                        <!-- Warning Box -->
                        <div class="warning-box">
                            <p class="warning-text">
                                <strong>🔒 Security Notice:</strong> Never share this code with anyone. Amma's Spices
                                will
                                never ask for your OTP via phone or email.
                            </p>
                        </div>

                        <p class="message" style="margin-top: 30px;">
                            If you didn't request this code, please ignore this email or contact our support team if you
                            have concerns about your account security.
                        </p>

                        <p class="message" style="color: #888888; font-size: 14px;">
                            Need help? Our customer support team is here for you 24/7.
                        </p>
                    </div>

                    <!-- Footer -->
                    {{-- <div class="footer">

                        <a href="{{ route('index') }}" class="logo"><img
                                src="{{ asset('assets/img/logo-bodhi.png') }}" alt=" Logo" width="105"
                                height="25"></a>


                        <div class="footer-links">
                            <a href="{{ config('app.url') }}" class="footer-link">Visit Website</a> |
                            <a href="mailto:support@bodhibliss.com" class="footer-link">Contact Support</a>
                        </div>

                        <p class="footer-text" style="margin-top: 20px;">
                            © {{ date('Y') }} Amma's Spices. All rights reserved.<br>
                            This is an automated email, please do not reply.
                        </p>
                    </div> --}}
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
