<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to ZAKH</title>
</head>

<body
    style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">

    <!-- Wrapper Table -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="background-color: #f5f5f5;">
        <tr>
            <td style="padding: 40px 20px;">

                <!-- Main Container -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600"
                    style="margin: 0 auto; background-color: #ffffff; border-radius: 16px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08); overflow: hidden; max-width: 100%;">

                    <!-- Header Section -->
                    <tr>
                        <td
                            style="background: linear-gradient(135deg, #dc2626 0%, #1a1a1a 100%); padding: 50px 40px; text-align: center;">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="ZAKH"
                                style="max-width: 180px; height: auto; display: block; margin: 0 auto;">
                        </td>
                    </tr>

                    <!-- Content Section -->
                    <tr>
                        <td style="padding: 50px 40px;">

                            <!-- Greeting -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 30px;">
                                        <h1
                                            style="margin: 0; font-size: 28px; font-weight: 700; color: #1a1a1a; line-height: 1.3;">
                                            Welcome to ZAKH!</h1>
                                    </td>
                                </tr>
                            </table>

                            <!-- Message -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <p style="margin: 0; font-size: 16px; line-height: 1.8; color: #4a5568;">
                                            Dear <strong style="color: #1a1a1a;">{{ $name }}</strong>,
                                        </p>
                                        <p
                                            style="margin: 20px 0 0 0; font-size: 16px; line-height: 1.8; color: #4a5568;">
                                            Thank you for joining ZAKH. Your account has been successfully created, and
                                            you now have access to our complete collection of premium products and
                                            exclusive member benefits.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding: 30px 0; text-align: center;">
                                        <a href="{{route('index')}}"
                                            style="display: inline-block; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #ffffff; text-decoration: none; padding: 16px 48px; border-radius: 8px; font-weight: 600; font-size: 16px; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);">Explore
                                            Our Collection</a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Features Section -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
                                style="background-color: #f9fafb; border-radius: 12px; padding: 30px; margin: 20px 0;">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <h2
                                            style="margin: 0; font-size: 18px; font-weight: 600; color: #1a1a1a; padding-bottom: 20px;">
                                            Your Member Benefits</h2>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 18px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td width="32" style="vertical-align: top;">
                                                    <div
                                                        style="width: 28px; height: 28px; background: linear-gradient(135deg, #dc2626 0%, #1a1a1a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span
                                                            style="color: #ffffff; font-size: 14px; font-weight: 700;">✓</span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: top; padding-left: 12px;">
                                                    <p
                                                        style="margin: 0; font-size: 15px; line-height: 1.6; color: #4a5568;">
                                                        <strong style="color: #1a1a1a;">Exclusive Deals</strong> – Early
                                                        access to sales and special promotions
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 18px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td width="32" style="vertical-align: top;">
                                                    <div
                                                        style="width: 28px; height: 28px; background: linear-gradient(135deg, #dc2626 0%, #1a1a1a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span
                                                            style="color: #ffffff; font-size: 14px; font-weight: 700;">✓</span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: top; padding-left: 12px;">
                                                    <p
                                                        style="margin: 0; font-size: 15px; line-height: 1.6; color: #4a5568;">
                                                        <strong style="color: #1a1a1a;">Fast Checkout</strong> – Secure
                                                        payment processing with saved preferences
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                {{-- <tr>
                                    <td style="padding-bottom: 18px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td width="32" style="vertical-align: top;">
                                                    <div
                                                        style="width: 28px; height: 28px; background: linear-gradient(135deg, #dc2626 0%, #1a1a1a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span
                                                            style="color: #ffffff; font-size: 14px; font-weight: 700;">✓</span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: top; padding-left: 12px;">
                                                    <p
                                                        style="margin: 0; font-size: 15px; line-height: 1.6; color: #4a5568;">
                                                        <strong style="color: #1a1a1a;">Order Tracking</strong> –
                                                        Real-time updates on all your shipments
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr> --}}
                                <tr>
                                    <td>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td width="32" style="vertical-align: top;">
                                                    <div
                                                        style="width: 28px; height: 28px; background: linear-gradient(135deg, #dc2626 0%, #1a1a1a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span
                                                            style="color: #ffffff; font-size: 14px; font-weight: 700;">✓</span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: top; padding-left: 12px;">
                                                    <p
                                                        style="margin: 0; font-size: 15px; line-height: 1.6; color: #4a5568;">
                                                        <strong style="color: #1a1a1a;">Personalized
                                                            Experience</strong> – Curated recommendations based on your
                                                        preferences
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                width="100%">
                                <tr>
                                    <td style="padding: 30px 0;">
                                        <div style="height: 1px; background-color: #e5e7eb;"></div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Support Section -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                width="100%">
                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <p style="margin: 0; font-size: 16px; line-height: 1.8; color: #4a5568;">
                                            Our dedicated support team is available 24/7 to assist you. Please don't
                                            hesitate to contact us:
                                        </p>
                                        <p
                                            style="margin: 15px 0 0 0; font-size: 16px; line-height: 1.8; color: #4a5568;">
                                            Email: <a href="mailto:contact@zakh.in"
                                                style="color: #dc2626; text-decoration: none; font-weight: 600;">contact@zakh.in</a><br>
                                            Phone: <a href="tel:+919910336595"
                                                style="color: #dc2626; text-decoration: none; font-weight: 600;">+91
                                                9910 336 595</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Closing -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                width="100%">
                                <tr>
                                    <td>
                                        <p style="margin: 0; font-size: 16px; line-height: 1.8; color: #4a5568;">
                                            We look forward to serving you.
                                        </p>
                                        <p
                                            style="margin: 20px 0 0 0; font-size: 16px; line-height: 1.8; color: #1a1a1a;">
                                            <strong>Best regards,</strong><br>
                                            <strong style="color: #dc2626;">The ZAKH Team</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer Section -->
                    <tr>
                        <td
                            style="background-color: #f9fafb; padding: 40px 40px; text-align: center; border-top: 1px solid #e5e7eb;">

                            <!-- Social Links -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                width="100%">
                                <tr>
                                    <td style="padding-bottom: 25px; text-align: center;">
                                        <a href="https://www.facebook.com/profile.php?id=61580493276702"
                                            style="display: inline-block; margin: 0 12px; color: #dc2626; text-decoration: none; font-weight: 600; font-size: 14px;">Facebook</a>
                                        <a href="https://in.pinterest.com/business/hub/"
                                            style="display: inline-block; margin: 0 12px; color: #dc2626; text-decoration: none; font-weight: 600; font-size: 14px;">Pinterest</a>
                                        <a href="https://www.instagram.com/zakh_official_/"
                                            style="display: inline-block; margin: 0 12px; color: #dc2626; text-decoration: none; font-weight: 600; font-size: 14px;">Instagram</a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer Text -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                width="100%">
                                <tr>
                                    <td style="text-align: center;">
                                        <p style="margin: 0; font-size: 13px; line-height: 1.8; color: #6b7280;">
                                            You're receiving this email because you registered for an account at ZAKH.
                                        </p>
                                        <p
                                            style="margin: 15px 0 0 0; font-size: 13px; line-height: 1.8; color: #9ca3af;">
                                            © 2025 ZAKH. All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer Links -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                width="100%">
                                <tr>
                                    <td style="padding-top: 20px; text-align: center;">
                                        <a href="{{ route('frontend.privacy_policy') }}"
                                            style="display: inline-block; margin: 0 8px; color: #9ca3af; text-decoration: none; font-size: 12px;">Privacy
                                            Policy</a>
                                        <span style="color: #d1d5db;">|</span>
                                        <a href="{{ route('frontend.terms_and_conditions') }}"
                                            style="display: inline-block; margin: 0 8px; color: #9ca3af; text-decoration: none; font-size: 12px;">Terms
                                            of Service</a>
                                        <span style="color: #d1d5db;">|</span>
                                        <a href="{{ route('frontend.return_and_exchange') }}"
                                            style="display: inline-block; margin: 0 8px; color: #9ca3af; text-decoration: none; font-size: 12px;">Return
                                            & Exchange</a>
                                        <span style="color: #d1d5db;">|</span>
                                        <a href="{{ route('frontend.shipping_and_delivery') }}"
                                            style="display: inline-block; margin: 0 8px; color: #9ca3af; text-decoration: none; font-size: 12px;">Shipping
                                            & Delivery</a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
