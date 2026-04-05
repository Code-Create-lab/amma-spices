<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - Order #ORD-{{ $order->cart_id ?? 'N/A' }}</title>
</head>

<body style="margin: 0; padding: 20px; background-color: #f5f5f5; font-family: Arial, sans-serif; font-size: 15px;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 900px;">

                    <!-- Logo Section -->
                    <tr>
                        <td style="padding: 20px; text-align: center;">
                            @if (!empty($logoBase64))
                                <img src="data:image/png;base64,{{ $logoBase64 }}" alt="logo" width="120"
                                    style="display: block; margin: 0 auto 15px;">
                            @else
                                <div style="font-size: 18px; font-weight: bold;">Amma's Spices</div>
                            @endif
                        </td>
                    </tr>

                    <!-- Header Section -->
                    <tr>
                        <td>
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="border-bottom: 2px solid #333333; padding-bottom: 15px;">
                                <tr>
                                    <td width="48%" valign="top">
                                        <strong>Customer Address</strong><br />
                                        {{ $order->address->receiver_name ?? 'Customer Name' }}<br />
                                        {{ $order->address->house_no ?? '' }}
                                        {{ $order->address->society ? ', ' . $order->address->society : '' }}<br />
                                        @if (!empty($order->address->landmark))
                                            Near {{ $order->address->landmark }}<br />
                                        @endif
                                        {{ $order->address->city ?? '' }}, {{ $order->address->state ?? '' }},
                                        {{ $order->address->pincode ?? '' }}<br />
                                        India<br />
                                        @if (!empty($order->address->receiver_phone))
                                            <strong>Phone:</strong> {{ $order->address->receiver_phone }}<br />
                                        @endif
                                        @if (!empty($order->address->receiver_email))
                                            <strong>Email:</strong> {{ $order->address->receiver_email }}<br />
                                        @endif
                                    </td>
                                    <td width="48%" valign="top" align="right">
                                        <strong>Company Information:</strong><br />
                                        Amma's Spices<br />
                                        Shop No. UFF29 Signature Global, <br />
                                        Sector 95A Gurugram, Haryana 122505<br />
                                        {{-- <strong>GSTIN:</strong> 29GJIPP3529L1ZG<br /> --}}
                                        <strong>Email:</strong> info@ammasspices.com
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Invoice Title -->
                    <tr>
                        <td align="center" style="padding: 20px 0 10px;">
                            <h2 style="margin: 0; font-size: 20px; text-decoration: underline;">TAX INVOICE</h2>
                            <div style="font-size: 14px;">Original For Recipient</div>
                        </td>
                    </tr>

                    <!-- Invoice Details -->
                    <tr>
                        <td>
                            <table width="100%" cellpadding="10" cellspacing="0" style="margin-top: 10px;">
                                <tr>
                                    <td width="60%" valign="top">
                                        <strong>BILL TO / SHIP TO</strong><br />
                                        {{ $order->address->receiver_name ?? 'Customer Name' }}<br />
                                        {{ $order->address->house_no ?? '' }}
                                        {{ $order->address->society ? ', ' . $order->address->society : '' }}<br />
                                        @if (!empty($order->address->landmark))
                                            Near {{ $order->address->landmark }}<br />
                                        @endif
                                        {{ $order->address->city ?? '' }}, {{ $order->address->state ?? '' }},
                                        {{ $order->address->pincode ?? '' }}<br /><br />
                                        <strong>Sold by:</strong><br />
                                        Amma's Spices<br />
                                         Shop No. UFF29 Signature Global, <br />
                                        Sector 95A Gurugram, Haryana 122505<br />
                                        <strong>GSTIN:</strong> 29GJIPP3529L1ZG
                                    </td>
                                    <td width="30%" style="text-align: right;" valign="top">
                                        <strong>Order No.</strong><br />
                                        {{ $order->cart_id ?? 'A0000' }}<br /><br />
                                        <strong>Invoice No.</strong><br />
                                        BBSOAP/{{ $order->cart_id ?? 'N/A' }}<br /><br />
                                        <strong>Order Date</strong><br />
                                        {{ $order->order_date ?? now()->format('d M, D Y') }}<br /><br />
                                        <strong>Invoice Date</strong><br />
                                        {{ now()->format('d M, D Y') }}<br /><br />
                                        {{-- <strong>Shipped By</strong><br />
                                        {{ $order->shipment->logistic_name ?? 'DELHIVERY' }}<br /><br />
                                        <strong>AWB No.</strong><br />
                                        {{ $order->shipment->waybill ?? '' }}<br /><br /> --}}
                                        <strong>Payment Method</strong><br />
                                        {{ strtoupper($order->payment_method ?? 'PREPAID') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <br>

                    <!-- Product Table -->
                    <tr>
                        <td>
                            <table width="100%" cellpadding="5" cellspacing="0" border="1"
                                style="border-collapse: collapse; font-size: 12px;">
                                <thead>
                                    <tr style="background: #f0f0f0;">
                                        <th>SR. NO</th>
                                        <th>PRODUCT DESCRIPTION</th>
                                        {{-- <th>HSN</th> --}}
                                        <th>QTY</th>
                                        <th>UNIT PRICE</th>
                                        <th colspan="2" style="text-align: center">CGST</th>
                                        <th colspan="2" style="text-align: center">SGST</th>
                                        <th colspan="2" style="text-align: center">IGST</th>
                                        <th>NET AMT</th>
                                    </tr>
                                    <tr style="background: #f9f9f9;">
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th>Rate</th>
                                        <th>Amt</th>
                                        <th>Rate</th>
                                        <th>Amt</th>
                                        <th>Rate</th>
                                        <th>Amt</th>
                                        {{-- <th></th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sr = 1;
                                        $subtotal = 0;
                                        $totalTax = 0;
                                        $totalDiscount = 0;
                                    @endphp

                                    @if (isset($order->orderItems) && $order->orderItems->isNotEmpty())
                                        @foreach ($order->orderItems as $item)
                                            @php
                                                $mrp = $item->variation->mrp ?? 0;
                                                $price = ($item->variation->price == 0 ? $item->variation->mrp  : $item->variation->price ) ?? 0;
                                                $qty = $item->quantity ?? 1;
                                                $unitDiscount = max(0, $mrp - $price);
                                                $taxRate = $item->variation->product->tax->value ?? 0;
                                                $taxableValue = $price * $qty - $unitDiscount * $qty;
                                                $taxAmount = $price * $qty * ($taxRate / 100);
                                                $lineTotal = $price * $qty;

                                                $subtotal += $lineTotal;
                                                $totalTax += $taxAmount;
                                                $totalDiscount += $unitDiscount * $qty;

                                                $cgstRate = $taxRate / 2;
                                                $sgstRate = $taxRate / 2;
                                                $igstRate = 0; // Set to $taxRate if IGST applies
                                                $cgstAmount = $taxAmount / 2;
                                                $sgstAmount = $taxAmount / 2;
                                                $igstAmount = 0;
                                            @endphp
                                            <tr>
                                                <td style="text-align: center;">{{ $sr }}</td>
                                                <td>
                                                    <strong>{{ $item->product_name ?? 'Product' }}</strong><br>
                                                    <span style="font-size: 11px; color: #6b7280;">
                                                        HSN: {{ $item->variation->product->hsn_number ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                {{-- <td>{{ $item->variation->hsn ?? '' }}</td> --}}
                                                <td style="text-align: center;">{{ $qty }}</td>
                                                <td style="text-align: right;">{{ number_format($price, 2) }}</td>

                                                {{-- CGST --}}
                                                <td style="text-align: right;">{{ number_format($cgstRate, 2) }}%</td>
                                                <td style="text-align: right;">{{ number_format($cgstAmount, 2) }}</td>

                                                {{-- SGST --}}
                                                <td style="text-align: right;">{{ number_format($sgstRate, 2) }}%</td>
                                                <td style="text-align: right;">{{ number_format($sgstAmount, 2) }}</td>

                                                {{-- IGST --}}
                                                <td style="text-align: right;">
                                                    {{ $igstAmount > 0 ? number_format($igstRate, 2) . '%' : '—' }}</td>
                                                <td style="text-align: right;">
                                                    {{ $igstAmount > 0 ? '' . number_format($igstAmount, 2) : '—' }}</td>

                                                {{-- Net Amount --}}
                                                <td style="text-align: right;">{{ number_format($lineTotal, 2) }}</td>
                                            </tr>
                                            @php $sr++; @endphp
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" style="text-align:center; padding:20px; color:#6b7280">No
                                                items found in this order.</td>
                                        </tr>
                                    @endif

                                    {{-- Summary Rows --}}
                                    <tr style="border-top: 2px solid #333;">
                                        <td colspan="10" style="text-align: right; font-weight: bold; padding: 8px;">
                                            Subtotal (Before Tax)</td>
                                        <td style="font-weight: bold; text-align: right; padding: 8px;">Rs
                                            {{ number_format($subtotal - $totalTax, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="10" style="text-align: right; font-weight: bold; padding: 8px;">
                                            Total Taxes</td>
                                        <td style="font-weight: bold; text-align: right; padding: 8px;">Rs
                                            {{ number_format($totalTax, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="10" style="text-align: right; font-weight: bold; padding: 8px;">
                                            Shipping</td>
                                        <td style="font-weight: bold; text-align: right; padding: 8px;">Rs
                                            {{ number_format($order->delivery_charge ?? 0, 2) }}</td>
                                    </tr>
                                    @if (isset($order->coupon_discount) && $order->coupon_discount > 0)
                                        <tr>
                                            <td colspan="10"
                                                style="text-align: right; font-weight: bold; padding: 8px;">Extra
                                                Discount</td>
                                            <td style="font-weight: bold; text-align: right; padding: 8px;">- Rs
                                                {{ number_format($order->coupon_discount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if (isset($order->advance_amount) && $order->advance_amount > 0)
                                        <tr>
                                            <td colspan="10"
                                                style="text-align: right; font-weight: bold; padding: 8px;">Advance
                                                Amount</td>
                                            <td style="font-weight: bold; text-align: right; padding: 8px;">Rs
                                                {{ number_format($order->advance_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="total-row" style="background: #f0f0f0;">
                                        <td colspan="10"
                                            style="text-align: right; font-weight: bold; font-size: 16px; padding: 10px;">
                                            Grand Total</td>
                                        <td
                                            style="font-weight: bold; font-size: 16px; text-align: right; padding: 10px;">
                                            Rs {{ number_format($order->total_price ?? 0, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <!-- Payment Transaction Section -->
                    <tr>
                        <td style="padding-top: 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="background-color: #f0f4f8; border-radius: 8px; padding: 20px;">
                                <tr>
                                    <td colspan="2"
                                        style="font-weight: bold; font-size: 18px; padding-bottom: 10px;">Payment
                                        Information</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;">Payment Method:</td>
                                    <td style="padding: 8px 0; text-align: right;">
                                        {{ strtoupper($order->payment_method ?? 'PREPAID') }}</td>
                                </tr>
                                {{-- @if ($order->payment_status ?? false)
                <tr>
                  <td style="padding: 8px 0;">Payment Status:</td>
                  <td style="padding: 8px 0; text-align: right;">{{ ucfirst($order->payment_status) }}</td>
                </tr>
                @endif --}}
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Note -->
                    <tr>
                        <td
                            style="padding-top: 20px; font-size: 12px; line-height: 1.4; border-top: 1px solid #e5e7eb; padding-top: 15px; color: #6b7280;">
                            Tax is not payable on reverse charge basis. This is a computer generated invoice and does
                            not require signature.<br />
                            <br />
                            <strong>Amma's Spices</strong> —  Shop No. UFF29 Signature Global,
                                        Sector 95A Gurugram, Haryana 122505
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
