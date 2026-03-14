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
    <link rel="manifest" href="{{ asset('assets/images/icons/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ asset('assets/images/icons/safari-pinned-tab.svg') }}" color="#666666">
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
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/owl-carousel/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/magnific-popup/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/jquery.countdown.css') }}">
    <!-- Main CSS File -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demos/demo-9.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skins/skin-demo-9.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skins/_skin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/nouislider/nouislider.css') }}">
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
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .cancelled-container {
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

        .cancelled-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff9800, #f57c00);
        }

        .cancel-mark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #ff9800;
            margin: 0 auto 30px;
            position: relative;
            animation: slideIn 0.6s ease-out;
        }

        .cancel-mark::after {
            content: '⊘';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 45px;
            font-weight: bold;
        }

        @keyframes slideIn {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 0;
            }

            50% {
                transform: scale(1.1) rotate(-5deg);
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

        .cancelled-details {
            background: #fff8f0;
            border-radius: 15px;
            padding: 30px;
            margin: 40px 0;
            border-left: 4px solid #ff9800;
        }

        .cancelled-message {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 15px;
        }

        .cancelled-message strong {
            color: #ff9800;
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
            background: #ff9800;
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
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 152, 0, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #666;
            border: 2px solid #ddd;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #ff9800;
            color: #ff9800;
        }

        .btn-outline {
            background: transparent;
            color: #ff9800;
            border: 2px solid #ff9800;
        }

        .btn-outline:hover {
            background: #ff9800;
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
            color: #ff9800;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        .reasons-section {
            background: #f0f7ff;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
            border-left: 4px solid #2196F3;
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
            color: #2196F3;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        @media (max-width: 768px) {
            .cancelled-container {
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
    <div class="cancelled-container">
        <div class="cancel-mark"></div>

        <h1>Payment Cancelled</h1>
        <p class="subtitle">You have cancelled the payment process. Your items are still saved in your cart and waiting
            for you.
        </p>

        <div class="cancelled-details">
            <div class="cancelled-message">
                <strong>Transaction Status:</strong> Cancelled by Customer
            </div>
            <div class="cancelled-message">
                <strong>Order Status:</strong> Not Placed
            </div>
        </div>

        <div class="reasons-section">
            <h4>Why customers cancel payments:</h4>
            <div class="reason-item">Want to review the order details again</div>
            <div class="reason-item">Prefer to use a different payment method</div>
            <div class="reason-item">Need to check account balance first</div>
            <div class="reason-item">Want to add more items to the cart</div>
            <div class="reason-item">Changed mind about the purchase</div>
        </div>

        <div class="next-steps">
            <h3>What you can do next:</h3>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">Review your cart items and proceed to checkout when ready</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">Continue shopping and add more items to your collection</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">Browse our latest collections and trending products</div>
            </div>
        </div>

        <div class="buttons">
            <a href="{{ route('checkout') }}" class="btn btn-primary">Complete Checkout</a>
            <a href="{{ route('getCartItems') }}" class="btn btn-outline">View Cart</a>
            <a href="{{ route('index') }}" class="btn btn-secondary">Continue Shopping</a>
        </div>

        <div class="contact-info">
            <p>Have questions or need assistance? Contact our customer support at <a
                    href="mailto:contact@zakh.in">contact@zakh.in</a> or call <a href="tel:+919910336595">+91
                    9910336595</a></p>
        </div>
    </div>
</body>

</html>
