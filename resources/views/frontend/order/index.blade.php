@extends('frontend.layouts.app', ['title' => ''])

@section('content')
    <main class="main">
        <div class="page-header">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h2 class="title  text-center">My Orders</h2>
                        <span class="seprater-img"><img src="{{ asset('assets/img/seprater.png') }}"></span>
                    </div>
                </div>
            </div><!-- End .container -->
        </div><!-- End .page-header -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Orders</li>
                </ol>
            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->

        <div class="page-content">
            <div class="dashboard">
                <div class="container">
                    <div class="row">

                        @if (Route::currentRouteName() != 'customer.track-orders.index')

                            @include('frontend.layouts.sidebar')
                        @endif

                        <div class="col-md-8 mx-auto">
                            <h2 class="checkout-">Billing Details</h2>
                            @if ($orders->isNotEmpty())
                                <div class="row g-4">

                                    @foreach ($orders as $order)
                                        <div class="col-12">
                                            <div class="card ">
                                                <div class="order-r-card card-header">
                                                    <div>
                                                        <div class="od-f-d">
                                                            <p class="Order">Order</p>
                                                            <div class="order-login-d">
                                                                #{{ $order->cart_id }}
                                                            </div>
                                                        </div>
                                                        <div class="order-row-op">
                                                            <span id="order-status"
                                                                class="badge-n 
                                                            @if ($order->order_status === 'Cancelled') bg-danger text-white
                                                            @elseif($order->order_status === 'Completed') bg-success text-white
                                                            {{-- @elseif($order->payment_status === 'paid') bg-success text-white
                                                            @elseif($order->payment_status === 'failed') bg-danger text-white --}}
                                                            @else @endif">
                                                                {{ ucfirst($order->order_status) }}
                                                            </span>

                                                            {{-- @dd($order->shipment) --}}

                                                            @if (in_array($order->shipment?->status, [
                                                                    'READY TO SHIP',
                                                                    'PICKUP SCHEDULED',
                                                                    'PICKED UP',
                                                                    'IN TRANSIT',
                                                                    'OUT FOR DELIVERY',
                                                                    'DELIVERED',
                                                                ]))

                                                                <a href="{{ route('tracking', ['o' => $order->cart_id, 's' => $order->shipment?->awb]) }}"
                                                                    class="track-order-link">

                                                                    <span class="badge-n  bg-warning text-dark">
                                                                        Track
                                                                    </span>

                                                                </a>
                                                                @endif

                                                                <span class="text-plac ">📅 Order date:
                                                                    {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</span>
                                                        </div>
                                                    </div>

                                                    @if ($order->order_status === 'Completed')
                                                        <a class="invoice-dow" target="_blank"
                                                            href="{{ route('order.invoice', $order->cart_id) }}"
                                                            class="btn btn-sm btn-outline-primary card-body-right">
                                                            <i class="la la-arrow-down"></i> Invoice
                                                        </a>
                                                    @endif
                                                </div>

                                                <div class="card-body">
                                                    {{-- Shipping Address --}}
                                                    <div class="mb-2">
                                                        <h6 class="Payment_head Shipping-ad">Shipping Details:</h6>
                                                        <div class="customer-details">
                                                            @if ($order->address)
                                                                <div class="cu_row">
                                                                    <label class="customer-label">
                                                                        Name:</label>
                                                                    <span
                                                                        class="cus-details">{{ $order->address->receiver_name }}</span>
                                                                </div>
                                                                <div class="cu_row">
                                                                    <label class="customer-label"> Email id:</label>
                                                                    <span class="cus-details">
                                                                        {{ $order->address->receiver_email }}</span>

                                                                </div>
                                                                <div class="cu_row">
                                                                    <label class="customer-label">Mobile No.:</label>
                                                                    <span
                                                                        class="cus-details">{{ $order->address->receiver_phone }}</span>
                                                                </div>
                                                                <div class="cu_row">
                                                                    <label class="customer-label">Alternate No.:</label>
                                                                    <span
                                                                        class="cus-details">{{ $order->address->alternate_phone }}</span>
                                                                </div>
                                                                <div class="cu_row addres-row">
                                                                    <label class="customer-label">Address:</label>
                                                                    <span
                                                                        class="cus-details">{{ $order->address->landmark }}
                                                                        <span
                                                                            class="cus-details">{{ $order->address->house_no }}</span>
                                                                        <span class="cus-details">
                                                                            {{ $order->address->society }}</span>
                                                                        <span class="cus-details">
                                                                            {{ $order->address->city }}</span>
                                                                        <span class="cus-details">
                                                                            {{ $order->address->state }}</span>
                                                                    </span>
                                                                </div>


                                                                <div class="cu_row">
                                                                    <label class="customer-label">Pin code:</label>
                                                                    <span
                                                                        class="cus-details">{{ $order->address->pincode }}</span>
                                                                </div>
                                                            @else
                                                                <em>No shipping address found</em>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Items --}}
                                                    <div class="table-responsive">
                                                        <table class="table align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Item</th>
                                                                    <th>Qty</th>
                                                                    <th>Price</th>
                                                                    <th>Total</th>
                                                                    @if ($order->order_status == 'Completed')
                                                                        <th>Review</th>
                                                                    @endif
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php $subTotal = 0; @endphp
                                                                @foreach ($order->orderItems as $item)
                                                                    @php
                                                                        $lineTotal =
                                                                            ($item->variation->price == 0
                                                                                ? $item->variation->mrp
                                                                                : $item->variation->price) *
                                                                            $item->quantity;
                                                                        $subTotal += $lineTotal;
                                                                    @endphp
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <a
                                                                                    href="{{ route('single.product.view', $item->variation->product->slug) }}">
                                                                                    <img src="{{ asset('storage/' . $item->varient_image) }}"
                                                                                        class="rounded me-2"
                                                                                        alt="{{ $item->product_name }}"
                                                                                        style="width:50px;height:50px;object-fit:cover;">
                                                                                </a>
                                                                                <div>
                                                                                    <div class="fw-bold ml-2">
                                                                                        {{-- @dd() --}}
                                                                                        <a
                                                                                            href="{{ route('single.product.view', $item->variation->product->slug) }}">
                                                                                            {{ $item->product_name }}
                                                                                        </a>
                                                                                    </div>
                                                                                    @if ($item->variation->variation_attributes->isNotEmpty())
                                                                                        <small class="text-muted ml-2">
                                                                                            {{ $item->variation->variation_attributes->map(function ($attribute) {
                                                                                                    return $attribute->attribute->attribute->name . ': ' . $attribute->attribute->name;
                                                                                                })->implode(', ') }}
                                                                                        </small>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>{{ $item->quantity }}</td>
                                                                        <td>₹{{ $item->variation->price == 0 ? $item->variation->mrp : $item->variation->price }}
                                                                        </td>
                                                                        <td>₹{{ $lineTotal }}</td>
                                                                        <td>
                                                                            <div class="review-pop">
                                                                                @if ($item->review)
                                                                                    <div
                                                                                        class="p-3 border rounded bg-light">
                                                                                        <strong>Your Review:</strong> <br>
                                                                                        <div class=""
                                                                                            style="color: #ffc107; font-size: 1.2rem;">
                                                                                            @for ($i = 1; $i <= 5; $i++)
                                                                                                @if ($i <= $item->review->rating)
                                                                                                    ★
                                                                                                @else
                                                                                                    ☆
                                                                                                @endif
                                                                                            @endfor
                                                                                            <p class="mb-0">
                                                                                                {{ $item->review->review }}
                                                                                            </p>
                                                                                            <p class="mb-0">
                                                                                                {{ $item->review->comment }}
                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                @else
                                                                                    @if ($order->order_status === 'Completed')
                                                                                        <button
                                                                                            class="btn btn-sm btn-outline-primary reviewModal"
                                                                                            data-toggle="modal"
                                                                                            data-target="#reviewModal-{{ $item->store_order_id }}">
                                                                                            Add Review
                                                                                        </button>
                                                                                    @endif
                                                                                @endif
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    {{-- Totals --}}
                                                    <div class="price-summary">
                                                        <div class="price-row">
                                                            <span class="label">Sub Total:</span>
                                                            <span class="value"> ₹{{ $subTotal }}</span>
                                                        </div>
                                                        <div class="price-row discount">
                                                            <span class="label">
                                                                Coupon:</span>
                                                            <span class="value">-₹{{ $order->coupon_discount }}
                                                                @if ($order->coupon)
                                                                    ({{ $order->coupon->coupon_name ?? '' }})
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="price-row">
                                                            <span class="label"> Shipping Fee: </span>
                                                            <span class="value">₹{{ $order->delivery_charge }}</span>
                                                        </div>
                                                        @if ($order->gift_wrap_value > 0)
                                                            <div class="price-row">
                                                                <span class="label"> Gift Wrap Fee:</span>
                                                                <span class="value">
                                                                    ₹{{ $order->gift_wrap_value }}</span>
                                                            </div>
                                                        @endif
                                                        @if ($order->cod_charge > 0)
                                                            <div class="price-row">
                                                                <span class="label">COD Charge:</span>
                                                                <span class="value"> ₹{{ $order->cod_charge }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <div class="price-row GrandTotal">
                                                            <span class="label">Grand Total:
                                                            </span>
                                                            <span class="value">₹{{ $order->total_price }}</span>
                                                        </div>
                                                    </div>

                                                    {{-- Payment Details --}}
                                                    <div class="Payment-de">
                                                        <h6 class="Payment_head">Payment Details</h6>
                                                        <div class="payment-box">
                                                            @if ($order->payment)
                                                                <div class="payment-method">
                                                                    <span class="py-label">Payment ID:</span>
                                                                    <span class="py-value">
                                                                        {{ $order->payment->payment_id ?? 'N/A' }}
                                                                    </span>
                                                                </div>
                                                                <div class="payment-method">
                                                                    Transaction #:
                                                                    {{ $order->payment->transaction_number ?? 'N/A' }}
                                                                </div>

                                                                <div>
                                                                    <strong>Status:</strong>
                                                                    <span id="payment-status"
                                                                        class="badge 
                                                                    @if ($order->payment->payment_status === 'SUCCESS') bg-success
                                                                    @elseif($order->payment->payment_status === 'FAILED') bg-danger
                                                                    @else bg-warning text-dark @endif">
                                                                        {{ ucfirst($order->payment->payment_status ?? 'N/A') }}
                                                                    </span>
                                                                </div>
                                                                <div><strong>Payment Date:</strong>
                                                                    {{ $order->payment->created_at ?? $order->created_at }}
                                                                </div>
                                                            @endif
                                                            <div><strong>Method:</strong>
                                                                {{ ucfirst($order->payment->method ?? $order->payment_method) }}
                                                            </div>
                                                            <div><strong>Amount:</strong>
                                                                ₹{{ $order->payment->amount ?? $order->total_price }}</div>



                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Pagination --}}
                                <div class="mt-4">
                                    {{ $orders->links('pagination::bootstrap-5') }}
                                </div>




                                <!-- Modal -->
                                @foreach ($orders as $order)
                                    @foreach ($order->orderItems as $item)
                                        <div class="modal fade" id="reviewModal-{{ $item->store_order_id }}"
                                            tabindex="-1" aria-labelledby="reviewModalLabel-{{ $item->store_order_id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Add Review</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form
                                                            action="{{ route('frontend.order.review.store', $item->store_order_id) }}"
                                                            method="POST">
                                                            @csrf
                                                            <div class="modal-header bg-light">
                                                                <h5 class="modal-title"
                                                                    id="reviewModalLabel-{{ $item->store_order_id }}">
                                                                    Review for <strong>{{ $item->product_name }}</strong>
                                                                </h5>
                                                                {{-- <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button> --}}
                                                            </div>
                                                            <div class="modal-body px-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Rating</label> <span
                                                                        class="text-danger"> *</span>
                                                                    <div class="star-rating d-flex align-items-center"
                                                                        data-item-id="{{ $item->store_order_id }}">
                                                                        @for ($i = 1; $i <= 5; $i++)
                                                                            <i class="la la-star star me-1"
                                                                                data-value="{{ $i }}"
                                                                                style="font-size: 30px; cursor: pointer; color: #ccc;"></i>
                                                                        @endfor
                                                                    </div>
                                                                    <input type="hidden" name="rating"
                                                                        id="rating-input-{{ $item->store_order_id }}"
                                                                        required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="review-{{ $item->store_order_id }}"
                                                                        class="form-label">Your Review</label> <span
                                                                        class="text-danger"> *</span>
                                                                    <textarea class="form-control" id="review-{{ $item->store_order_id }}" name="review" rows="4"
                                                                        placeholder="Write your review here..." required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer justify-content-between">
                                                                {{-- <button type="button" class="btn btn-outline-secondary"
                                                                    data-bs-dismiss="modal">Close</button> --}}
                                                                <button type="submit" class="btn btn-primary">Submit
                                                                    Review</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    {{-- <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Close</button>
                                                        <button type="button" class="btn btn-primary">Save
                                                            changes</button>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                                {{-- Review Modals - placed OUTSIDE the table --}}
                                {{-- @foreach ($orders as $order)
                                    @foreach ($order->orderItems as $item)
                                        @if ($order->order_status === 'Completed')
                                            <div class="modal fade" id="reviewModal-{{ $item->store_order_id }}"
                                                tabindex="-1"
                                                aria-labelledby="reviewModalLabel-{{ $item->store_order_id }}"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content mx-2">
                                                        <form
                                                            action="{{ route('frontend.order.review.store', $item->store_order_id) }}"
                                                            method="POST">
                                                            @csrf
                                                            <div class="modal-header bg-light">
                                                                <h5 class="modal-title"
                                                                    id="reviewModalLabel-{{ $item->store_order_id }}">
                                                                    Review for <strong>{{ $item->product_name }}</strong>
                                                                </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body px-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Rating</label>
                                                                    <div class="star-rating d-flex align-items-center"
                                                                        data-item-id="{{ $item->store_order_id }}">
                                                                        @for ($i = 1; $i <= 5; $i++)
                                                                            <i class="la la-star star me-1"
                                                                                data-value="{{ $i }}"
                                                                                style="font-size: 30px; cursor: pointer; color: #ccc;"></i>
                                                                        @endfor
                                                                    </div>
                                                                    <input type="hidden" name="rating"
                                                                        id="rating-input-{{ $item->store_order_id }}"
                                                                        required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="review-{{ $item->store_order_id }}"
                                                                        class="form-label">Your Review</label>
                                                                    <textarea class="form-control" id="review-{{ $item->store_order_id }}" name="review" rows="4"
                                                                        placeholder="Write your review here..." required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer justify-content-between">
                                                                <button type="button" class="btn btn-outline-secondary"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Submit
                                                                    Review</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endforeach --}}
                            @else
                                <div class="no-orders text-center py-5">
                                    <h3>No Orders Found</h3>
                                    <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div><!-- End .row -->
            </div><!-- End .container -->
        </div><!-- End .dashboard -->
        </div><!-- End .page-content -->
    </main><!-- End .main -->

    @push('scripts')
        <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
        {!! JsValidator::formRequest('App\Http\Requests\Frontend\Profile\ProfileUpdateRequest', '#profile-update-form') !!}
    @endpush

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.star-rating').forEach(function(container) {
                const stars = container.querySelectorAll('.star');
                const itemId = container.dataset.itemId;
                const input = document.getElementById('rating-input-' + itemId);

                stars.forEach(star => {
                    star.addEventListener('mouseover', function() {
                        const val = parseInt(this.dataset.value);
                        stars.forEach((s, i) => {
                            s.style.color = (i < val) ? '#ffc107' : '#ccc';
                        });
                    });

                    star.addEventListener('mouseout', function() {
                        const val = parseInt(input.value) || 0;
                        stars.forEach((s, i) => {
                            s.style.color = (i < val) ? '#ffc107' : '#ccc';
                        });
                    });

                    star.addEventListener('click', function() {
                        const val = parseInt(this.dataset.value);
                        input.value = val;
                        stars.forEach((s, i) => {
                            s.style.color = (i < val) ? '#ffc107' : '#ccc';
                        });
                    });

                    // Mobile touch support
                    star.addEventListener('touchstart', function(e) {
                        e.preventDefault();
                        const val = parseInt(this.dataset.value);
                        input.value = val;
                        stars.forEach((s, i) => {
                            s.style.color = (i < val) ? '#ffc107' : '#ccc';
                        });
                    });
                });
            });
        });
    </script>


@endsection
