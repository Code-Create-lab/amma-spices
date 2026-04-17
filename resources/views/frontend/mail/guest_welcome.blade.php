<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to Amma's Spices</title>
</head>

<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background:#f5f5f5; color:#000000;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:30px 15px;">

                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="border-collapse:collapse; background:#ffffff; border-radius:8px; padding:30px;">

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding: 30px 30px 25px 30px;">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="Amma's Spices"
                                style="max-width:140px;">
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td style="padding: 0 30px 20px 30px;">
                            <h2 style="margin:0; font-size:22px; font-weight:600;">
                                Welcome to Amma's Spices!
                            </h2>
                        </td>
                    </tr>

                    <!-- Greeting & Message -->
                    <tr>
                        <td style="font-size:14px; line-height:22px; padding: 0 30px 20px 30px;">
                            Hi <strong>{{ $name }}</strong>,<br><br>
                            Thank you for your order! We have created an account for you so you can
                            track your orders and enjoy a faster checkout experience in the future.
                        </td>
                    </tr>

                    <!-- Login Details Heading -->
                    <tr>
                        <td style="padding: 0 30px 10px 30px;">
                            <strong>Your Login Details</strong>
                        </td>
                    </tr>

                    <!-- Credentials Table -->
                    <tr>
                        <td style="padding: 0 30px 20px 30px;">
                            <table width="100%" cellpadding="8" cellspacing="0"
                                style="border-collapse:collapse; font-size:14px; border:1px solid #dddddd; border-radius:4px;">
                                <tr style="border-bottom:1px solid #eeeeee;">
                                    <td style="color:#555555; width:100px; padding:10px 12px;">Email:</td>
                                    <td style="font-weight:600; padding:10px 12px;">{{ $email }}</td>
                                </tr>
                                <tr>
                                    <td style="color:#555555; width:100px; padding:10px 12px;">Password:</td>
                                    <td style="font-weight:600; padding:10px 12px;">
                                        <code
                                            style="background:#f0f0f0; padding:3px 8px; border-radius:4px; font-family:monospace; font-size:14px; letter-spacing:1px;">{{ $password }}</code>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Password Notice -->
                    <tr>
                        <td style="font-size:13px; line-height:20px; color:#777777; padding: 0 30px 20px 30px;">
                            We recommend changing your password after your first login for security.
                        </td>
                    </tr>

                    <!-- CTA Button -->
                    <tr>
                        <td align="center" style="padding: 0 30px 25px 30px;">
                            <a href="{{ route('index') }}"
                                style="display:inline-block; background:#e7c840; color:#000000; text-decoration:none; padding:12px 36px; border-radius:4px; font-weight:600; font-size:14px;">
                                Shop Now
                            </a>
                        </td>
                    </tr>

                    <!-- Contact Info -->
                    <tr>
                        <td align="center" style="padding: 0 30px 0 30px; font-size:14px; line-height:22px;">
                            If you have any questions, feel free to reach out to us.
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="padding: 25px 30px 30px 30px; font-size:13px; line-height:20px; border-top:1px solid #dddddd; margin-top:30px;">
                            Contact us at <strong>info@ammasspices.com</strong><br><br>
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