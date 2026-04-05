@extends('frontend.layouts.app', ['title' => ''])



@section('content')
    <style>
        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .thank-you-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
            padding: 40px 40px;
            text-align: center;
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .thank-you-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #4B3B2E 0%, #1a1a1a 100%);
        }

        .logo-section {
            margin-bottom: 25px;
        }

        .logo-section img {
            max-width: 120px;
            height: auto;
        }

        .success-icon-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4B3B2E 0%, #4B3B2E 100%);
            margin: 0 auto;
            position: relative;
            animation: scaleIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 40px rgba(220, 38, 38, 0.3);
        }

        .checkmark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 40px;
            font-weight: bold;
        }

        .pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            border: 3px solid #4B3B2E;
            border-radius: 50%;
            animation: pulse 2s ease-out infinite;
            opacity: 0;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0) rotate(-180deg);
                opacity: 0;
            }

            50% {
                transform: scale(1.15) rotate(10deg);
            }

            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.5;
            }

            100% {
                transform: translate(-50%, -50%) scale(1.5);
                opacity: 0;
            }
        }

        h1 {
            color: #1a1a1a;
            font-size: 2.2em;
            margin-bottom: 12px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 1.05em;
            margin-bottom: 30px;
            line-height: 1.6;
            font-weight: 400;
        }

        .order-details {
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border-radius: 16px;
            padding: 25px;
            margin: 30px 0;
            border-left: 5px solid #4B3B2E;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .order-number {
            font-size: 1em;
            color: #374151;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .order-number strong {
            color: #4B3B2E;
            font-weight: 700;
        }

        .order-icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #4B3B2E 0%, #4B3B2E 100%);
            border-radius: 50%;
            color: white;
            line-height: 20px;
            font-size: 11px;
        }

        .next-steps {
            text-align: left;
            margin: 30px 0;
        }

        .next-steps h3 {
            color: #1a1a1a;
            margin-bottom: 20px;
            font-size: 1.2em;
            font-weight: 700;
            text-align: center;
        }

        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            padding: 15px;
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .step:hover {
            transform: translateX(8px);
            border-color: #4B3B2E;
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.1);
        }

        .step-number {
            background: linear-gradient(135deg, #4B3B2E 0%, #4B3B2E 100%);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
            flex-shrink: 0;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .step-text {
            color: #4b5563;
            line-height: 1.5;
            font-size: 14px;
            padding-top: 4px;
        }

        .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 35px;
            border: none;
            border-radius: 12px;
            font-size: 0.95em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 160px;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4B3B2E 0%, #4B3B2E 100%);
            color: white;
            box-shadow: 0 6px 20px rgb(0 0 0 / 53%);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(220, 38, 38, 0.45);
        }

        .btn-secondary {
            background: white;
            color: #374151;
            border: 2px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #4B3B2E;
            color: #4B3B2E;
            transform: translateY(-2px);
        }

        .contact-info {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #f3f4f6;
            color: #6b7280;
            font-size: 0.9em;
            line-height: 1.6;
        }

        .contact-info a {
            color: #4B3B2E;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .contact-info a:hover {
            color: #4B3B2E;
            text-decoration: underline;
        }

        .divider {
            display: inline-block;
            margin: 0 8px;
            color: #d1d5db;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px 10px;
            }

            .thank-you-container {
                padding: 35px 25px;
                margin: 0 auto;
                border-radius: 20px;
            }

            h1 {
                font-size: 1.8em;
            }

            .subtitle {
                font-size: 1em;
            }

            .checkmark {
                width: 70px;
                height: 70px;
            }

            .checkmark::after {
                font-size: 35px;
            }

            .buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }

            .order-number {
                flex-direction: column;
                gap: 8px;
            }

            .step {
                padding: 12px;
            }

            .next-steps h3 {
                font-size: 1.1em;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .thank-you-container {
                padding: 30px 18px;
            }

            h1 {
                font-size: 1.6em;
            }

            .order-details {
                padding: 20px 15px;
            }
        }
    </style>
    <div class="thank-you-container mt-5 mb-5">
        <div class="logo-section">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Bodhi Bliss">
        </div>

        <div class="success-icon-wrapper">
            <div class="checkmark"></div>
            <div class="pulse-ring"></div>
        </div>

        <h1>Order Confirmed!</h1>
        <p class="subtitle">Thank you for shopping with Bodhi Bliss. Your order has been successfully placed and is being
            prepared for shipment.</p>

        <div class="order-details">
            <div class="order-number">
                <span class="order-icon">📦</span>
                <span><strong>Order ID:</strong> #{{ $order->cart_id }}</span>
            </div>
            <div class="order-number">
                <span class="order-icon">🚚</span>
                <span><strong>Estimated Delivery:</strong> 4-7 Business Days</span>
            </div>
        </div>

        <div class="next-steps">
            <h3>What Happens Next?</h3>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">You'll receive an order confirmation email with all the details within the next few
                    minutes</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">Your order will be carefully packaged and dispatched from our warehouse</div>
            </div>
            
        </div>

        <div class="buttons">
            <a href="{{ route('customer.orders.index') }}" class="btn btn-primary">
                <span>Check Your Order</span>
                <span>→</span>
            </a>
        </div>

      
    </div>
@endsection
