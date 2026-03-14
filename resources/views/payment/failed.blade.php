<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - Order Unsuccessful</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Amma's Spices ">
    <meta name="keywords" content="Amma's Spices">
    <meta property="og:title" content="Amma's Spices – ">
    <meta property="og:description" content="">
    <meta property="og:image" content="{{ asset('assets/images/demos/demo-9/SOAP.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Amma's Spices ">
    <meta name="twitter:description" content="S">
    <meta name="twitter:image" content="{{ asset('assets/images/demos/demo-9/SOAP.png') }}">
    <meta property="og:site_name" content="Amma's Spices">
    <meta name="robots" content="noindex, nofollow">
    <title> {{ config('app.name') }} |
        @isset($title)
            {{ $title }}
        @endisset
    </title>
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/icons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/icons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/icons/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/icons/favicon.ico') }}">
    <meta name="apple-mobile-web-app-title" content="Molla">
    <meta name="application-name" content="Molla">
    <meta name="msapplication-TileColor" content="#cc9966">
    <meta name="msapplication-config" content="{{ asset('assets/images/icons/browserconfig.xml') }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet"
        href="{{ asset('assets/vendor/line-awesome/line-awesome/line-awesome/css/line-awesome.min.css') }}">
    <!-- Plugins CSS File -->
    <link rel="stylesheet" href="{{ asset('assets_old/css/toastr.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- Main CSS File -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demos/demo-9.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skins/skin-demo-9.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skins/_skin.css') }}">
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/css/fontawesome.css') }}"></script>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .failed-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .failed-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #ee5a52);
        }

        .error-mark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #ff6b6b;
            margin: 0 auto 30px;
            position: relative;
            animation: shakeIn 0.6s ease-out;
        }

        .error-mark::after {
            content: '✕';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 40px;
            font-weight: bold;
        }

        @keyframes shakeIn {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 0;
            }

            50% {
                transform: scale(1.1) rotate(5deg);
            }

            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .subtitle {
            color: #666;
            font-size: 1.2em;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .error-details {
            background: #fff5f5;
            border-radius: 15px;
            padding: 30px;
            margin: 40px 0;
            border-left: 4px solid #ff6b6b;
        }

        .error-message {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 15px;
        }

        .error-message strong {
            color: #ff6b6b;
            font-weight: 600;
        }

        .next-steps {
            text-align: left;
            margin: 30px 0;
        }

        .next-steps h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .step {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: transform 0.2s ease;
        }

        .step:hover {
            transform: translateX(5px);
        }

        .step-number {
            background: #ff6b6b;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .step-text {
            color: #555;
            line-height: 1.5;
        }

        .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            min-width: 150px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #666;
            border: 2px solid #ddd;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #ff6b6b;
            color: #ff6b6b;
        }

        .btn-outline {
            background: transparent;
            color: #ff6b6b;
            border: 2px solid #ff6b6b;
        }

        .btn-outline:hover {
            background: #ff6b6b;
            color: white;
        }

        .contact-info {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 0.9em;
        }

        .contact-info a {
            color: #ff6b6b;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        .reasons-section {
            background: #fffbf5;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
            border-left: 4px solid #ffa500;
        }

        .reasons-section h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.1em;
            font-weight: 600;
        }

        .reason-item {
            color: #666;
            margin-bottom: 8px;
            font-size: 0.9em;
            position: relative;
            padding-left: 20px;
        }

        .reason-item::before {
            content: '•';
            color: #ffa500;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        @media (max-width: 768px) {
            .failed-container {
                padding: 40px 20px;
                margin: 10px;
            }

            h1 {
                font-size: 2em;
            }

            .buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>

<body>
    <div class="failed-container">
        <div class="error-mark"></div>

        <h1>Payment Failed!</h1>
        <p class="subtitle">We couldn't process your payment. Don't worry, no amount has been charged from your account.
        </p>

        <div class="error-details">
            <div class="error-message">
                <strong>Transaction Status:</strong> Payment Failed
            </div>
            <div class="error-message">
                <strong>Order Status:</strong> Not Placed
            </div>
        </div>

        <div class="reasons-section">
            <h4>Common reasons for payment failure:</h4>
            <div class="reason-item">Insufficient funds in your account</div>
            <div class="reason-item">Card expired or blocked by bank</div>
            <div class="reason-item">Incorrect card details entered</div>
            <div class="reason-item">Bank security restrictions</div>
            <div class="reason-item">Network connectivity issues</div>
        </div>

        <div class="next-steps">
            <h3>What you can do next:</h3>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">Check your card details and try the payment again</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">Try using a different payment method or card</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">Contact your bank to authorize the transaction</div>
            </div>
        </div>

        <div class="buttons">
            {{-- <a href="{{ route('checkout') }}" class="btn btn-primary">Retry Payment</a> --}}
            <a href="{{ route('getCartItems') }}" class="btn btn-outline">Review Cart</a>
            <a href="{{ route('index') }}" class="btn btn-secondary">Continue Shopping</a>
        </div>


    </div>
</body>

</html>
