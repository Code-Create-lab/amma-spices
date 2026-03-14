@extends('admin.layout.app')

@section('preload-section')
    <style>
        @media screen {
            #printSection {
                display: none;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printSection,
            #printSection * {
                visibility: visible;
            }

            #printSection {
                position: absolute;
                left: 0;
                top: 0;
            }
        }

        /* Animated New Badge */
        @keyframes pulse {
            0% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.7;
                transform: scale(1.05);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animated-badge {
            animation: pulse 1.5s infinite;
            font-size: 10px;
            padding: 3px 6px;
        }

        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.15) !important;
        }

        .gap-1 {
            gap: 0.25rem;
        }

        /* Fix modal z-index issues */
        #confirmOrderModal {
            z-index: 1050 !important;
        }

        #confirmOrderModal .modal-dialog {
            z-index: 1051 !important;
        }

        #confirmOrderModal .modal-content {
            z-index: 1052 !important;
            position: relative;
            background-color: #fff;
        }

        #confirmOrderModal .modal-body {
            position: relative;
            z-index: 1053 !important;
            background-color: #fff;
        }

        #confirmOrderModal .modal-body input,
        #confirmOrderModal .modal-body select,
        #confirmOrderModal .modal-body textarea,
        #confirmOrderModal .modal-body button,
        #confirmOrderModal .modal-body .form-control {
            position: relative;
            z-index: 1054 !important;
            /* background-color: #fff; */
        }

        #confirmOrderModal .modal-body input:focus,
        #confirmOrderModal .modal-body select:focus,
        #confirmOrderModal .modal-body textarea:focus,
        #confirmOrderModal .modal-body .form-control:focus {
            z-index: 1055 !important;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        /* Ensure cards inside modal don't overlap */
        #confirmOrderModal .card {
            position: relative;
            z-index: auto;
            background-color: #fff;
        }

        #confirmOrderModal .delivery-agent-card {
            background-color: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">

        {{-- ================= ALERT MESSAGES ================= --}}
        <div id="alertContainer"></div>

        {{-- ================= CARD ================= --}}
        <div class="card">

            <div class="card-header card-header-primary">
                <h4 class="card-title">{{ __('keywords.All orders') }}</h4><span class="text-danger">Note: All pending payment orders will be updated within 20 minutes.</span>
            </div>

            <div class="card-body">

                {{-- ================= FILTERS ================= --}}
                <div class="row mb-4 align-items-end">

                    <div class="col-md-2">
                        <label>Order Status</label>
                        <select id="filter_status" class="form-control">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            {{-- <option value="out_for_delivery">Out For Delivery</option> --}}
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>

                    {{-- <div class="col-md-2">
                        <label>Payment Method</label>
                        <select id="filter_payment_method" class="form-control">
                            <option value="">All</option>
                            <option value="COD">COD</option>
                            <option value="Online">Online</option>
                        </select>
                    </div> --}}

                    {{-- <div class="col-md-2">
                        <label>Payment Status</label>
                        <select id="filter_payment_status" class="form-control">
                            <option value="">All</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div> --}}

                    <div class="col-md-2">
                        <label>From Date</label>
                        <input type="date" id="filter_from_date" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>To Date</label>
                        <input type="date" id="filter_to_date" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <button id="applyFilters" class="btn btn-primary w-100">
                            Apply
                        </button>
                    </div>

                    <div class="col-md-2">
                        <button id="resetFilters" class="btn btn-secondary w-100">
                            Reset
                        </button>
                    </div>

                    <div class="col-md-2">
                        <button id="exportExcel" class="btn btn-success w-100">
                            Export
                        </button>
                    </div>

                </div>

                {{-- ================= TABLE ================= --}}
                <table id="datatableDefault" class="table table-striped text-nowrap w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('keywords.Order_Id') }}</th>
                            <th>{{ __('keywords.total_price') }}</th>
                            <th>{{ __('keywords.User') }}</th>
                            <th>{{ __('keywords.Order_Date') }}</th>
                            <th>Order {{ __('keywords.Status') }}</th>
                            <th>Delivery Date</th>
                            <th>Payment Status</th>
                            <th>Shipment Status</th>
                            <th>{{ __('keywords.Payment Method') }}</th>
                            <th>{{ __('keywords.Actions') }}</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>
    </div>

    {{-- ================= ORDER DETAILS MODAL ================= --}}
    @foreach ($orders as $order)
        <div class="modal fade" id="orderDetailsModal{{ $order->cart_id }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('keywords.Order Details') }}</h5>
                        <button class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><b>{{ __('keywords.Order_Id') }}:</b> {{ $order->cart_id }}</p>
                                <p><b>{{ __('keywords.Customer_name') }}:</b>
                                    {{ $order->address->receiver_name ?? $order->user?->name }}</p>
                                <p><b>{{ __('keywords.Customer_email') }}:</b>
                                    {{ $order->address->receiver_email ?? $order->user?->email }}</p>
                                <p><b>{{ __('keywords.Contact') }}:</b>
                                    {{ $order->address->receiver_phone ?? $order->user?->user_phone }}</p>
                                <p><b>{{ __('keywords.Alternate_Contact') }}:</b>
                                    {{ $order->address->alternate_phone ?? '' }}</p>
                                <p><b>Order Date:</b> {{ $order->order_date }}</p>
                                {{-- <p><b>Expected Delivery:</b> {{ $order->delivery_date }}</p> --}}
                            </div>
                            <div class="col-md-6 text-right">
                                <p><b>{{ __('keywords.Delivery Address') }}</b></p>
                                @if ($order->address)
                                    <p>{{ $order->address->house_no ?? '' }}, {{ $order->address->society ?? '' }}</p>
                                    @if ($order->address->landmark)
                                        <p>{{ $order->address->landmark }}</p>
                                    @endif
                                    <p>{{ $order->address->city ?? '' }}, {{ $order->address->state ?? '' }}</p>
                                    <p>{{ $order->address->pincode ?? '' }}</p>
                                @endif
                            </div>
                        </div>

                        <table class="table table-bordered table-sm mt-3">
                            <thead>
                                <tr>
                                    <th>{{ __('keywords.Product_Name') }}</th>
                                    <th>{{ __('keywords.Qty') }}</th>
                                    <th>{{ __('keywords.Tax') }}</th>
                                    <th>{{ __('keywords.Price') }}</th>
                                    <th>{{ __('keywords.Total_Price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderItems as $item)
                                    <tr>
                                        {{-- @dd($item) --}}
                                        <td>
                                            <img style="width:25px;height:25px; border-radius:50%"
                                                src="{{ asset('storage/' . $item->varient_image) }}"
                                                alt="{{ $item->product_name }}">
                                            {{ $item->product_name }}
                                            @if ($item->variation && $item->variation->variation_attributes && $item->variation->variation_attributes->isNotEmpty())
                                                <div class="mt-1">
                                                    @foreach ($item->variation->variation_attributes as $attribute)
                                                        <span class="badge bg-secondary">
                                                            {{ $attribute->attribute->attribute->name ?? '' }}:
                                                            {{ $attribute->attribute->name ?? '' }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $item->qty ?? ($item->quantity ?? 1) }}</td>
                                        <td>{{ $item->tx_per ?? 0 }}%</td>
                                        <td>
                                            @if ($item->price_without_tax != null && $item->price_without_tax != 0)
                                                {{ $item->price_without_tax }}
                                            @elseif ($item->price != null && $item->price != 0)
                                                {{ $item->price }}
                                            @else
                                                {{ $item->total_mrp }}
                                            @endif
                                        </td>
                                        <td>{{ $item->price == 0 || $item->price == null ? $item->total_mrp : $item->price }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-3">
                            <table class="table table-sm">
                                <tr>
                                    <td class="text-right"><strong>{{ __('keywords.Products_Price') }}:</strong></td>
                                    <td class="text-right" width="120">
                                        {{ $order->price_without_delivery + ($order->coupon_discount ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-right"><strong>{{ __('keywords.Delivery_Charge') }}:</strong></td>
                                    <td class="text-right">+{{ $order->delivery_charge ?? 0 }}</td>
                                </tr>
                                @if (($order->paid_by_wallet ?? 0) > 0)
                                    <tr>
                                        <td class="text-right"><strong>{{ __('keywords.Paid By Wallet') }}:</strong></td>
                                        <td class="text-right">-{{ $order->paid_by_wallet }}</td>
                                    </tr>
                                @endif
                                @if (($order->coupon_discount ?? 0) > 0)
                                    <tr>
                                        <td class="text-right"><strong>{{ __('keywords.Coupon Discount') }}:</strong></td>
                                        <td class="text-right">-{{ $order->coupon_discount }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-right"><strong>{{ __('keywords.Net_Total(Payable)') }}:</strong></td>
                                    <td class="text-right"><strong>{{ $order->total_price }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endforeach

    {{-- ================= CONFIRM ORDER MODAL ================= --}}
    <div class="modal fade" id="confirmOrderModal" tabindex="-1" role="dialog" aria-labelledby="confirmOrderModalLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmOrderModalLabel">Create Shipping Order</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="confirmOrderModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading order details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ================= TRACK ORDER MODAL ================= --}}
    <div class="modal fade" id="trackOrderModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Track Shipment</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body p-0" id="trackOrderModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading tracking details...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('postload-section')
    <!-- Bootstrap 4 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {

            // CSRF Token for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let table = $('#datatableDefault').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,

                dom: "<'row mb-3'<'col-md-4'l><'col-md-8'f>>" +
                    "t" +
                    "<'row'<'col-md-5'i><'col-md-7'p>>",

                ajax: {
                    url: "{{ route('admin_all_orders') }}",
                    data: function(d) {
                        d.status = $('#filter_status').val();
                        d.payment_method = $('#filter_payment_method').val();
                        d.payment_status = $('#filter_payment_status').val();
                        d.from_date = $('#filter_from_date').val();
                        d.to_date = $('#filter_to_date').val();
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'cart_id_display',
                        orderable: false
                    },
                    {
                        data: 'total_price'
                    },
                    {
                        data: 'user_info',
                        orderable: false
                    },
                    {
                        data: 'order_date'
                    },
                    {
                        data: 'status_badge',
                        orderable: false
                    },
                    {
                        data: 'delivery_date'
                    },
                    {
                        data: 'payment_status'
                    },
                    {
                        data: 'shipment_status'
                    },
                    {
                        data: 'payment_method_badge',
                        orderable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],

                order: [
                    [0, 'desc']
                ]
            });

            // APPLY FILTERS
            $('#applyFilters').on('click', function() {
                table.ajax.reload();
            });

            // EXPORT TO EXCEL
            $('#exportExcel').on('click', function() {
                let params = new URLSearchParams();
                let status = $('#filter_status').val();
                let fromDate = $('#filter_from_date').val();
                let toDate = $('#filter_to_date').val();
                let search = $('input[type="search"]').val();

                if (status) params.append('status', status);
                if (fromDate) params.append('from_date', fromDate);
                if (toDate) params.append('to_date', toDate);
                if (search) params.append('search', search);

                let url = "{{ route('orders.export') }}";
                if (params.toString()) url += '?' + params.toString();
                window.location.href = url;
            });

            // RESET FILTERS
            $('#resetFilters').on('click', function() {
                $('#filter_status').val('');
                $('#filter_payment_method').val('');
                $('#filter_payment_status').val('');
                $('#filter_from_date').val('');
                $('#filter_to_date').val('');
                table.ajax.reload();
            });

            // Confirm Order - Show SweetAlert then open shipping modal
            $(document).on('click', '.open-confirm-modal', function() {
                let orderId = $(this).data('order-id');

                Swal.fire({
                    title: 'Confirm Order',
                    text: 'Do you want to confirm this order and create shipment?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Confirm',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#confirmOrderModal').modal('show');
                        $('#confirmOrderModalBody').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading order details...</p>
                    </div>
                `);
                        $('#confirmOrderModalBody').load('/admin/order/confirm/' + orderId);
                    }
                });
            });

            // Cancel Order - SweetAlert Confirmation
            $(document).on('click', '.btn-cancel-order', function() {
                let orderId = $(this).data('order-id');

                Swal.fire({
                    title: 'Cancel Order',
                    text: 'Are you sure you want to cancel this order? Refunds will be processed if applicable.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Cancel Order',
                    cancelButtonText: 'No, Keep Order',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                url: '/admin/ajax/order/cancel/' + orderId,
                                type: 'POST',
                                success: function(response) {
                                    resolve(response);
                                },
                                error: function(xhr) {
                                    Swal.showValidationMessage(
                                        xhr.responseJSON?.message ||
                                        'Failed to cancel order'
                                    );
                                    resolve(false);
                                }
                            });
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value?.success) {
                        Swal.fire({
                            title: 'Cancelled!',
                            text: result.value.message || 'Order has been cancelled.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    } else if (result.isConfirmed && result.value === false) {
                        // Error already shown via showValidationMessage
                    } else if (result.isConfirmed && !result.value?.success) {
                        Swal.fire({
                            title: 'Error!',
                            text: result.value?.message || 'Failed to cancel order.',
                            icon: 'error'
                        });
                    }
                });
            });

            // Complete Order - SweetAlert Confirmation
            $(document).on('click', '.btn-complete-order', function() {
                let orderId = $(this).data('order-id');

                Swal.fire({
                    title: 'Complete Order',
                    text: 'Are you sure you want to mark this order as completed?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Complete Order',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                url: '/admin/ajax/order/complete/' + orderId,
                                type: 'POST',
                                success: function(response) {
                                    resolve(response);
                                },
                                error: function(xhr) {
                                    Swal.showValidationMessage(
                                        xhr.responseJSON?.message ||
                                        'Failed to complete order'
                                    );
                                    resolve(false);
                                }
                            });
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value?.success) {
                        Swal.fire({
                            title: 'Completed!',
                            text: result.value.message || 'Order has been completed.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    } else if (result.isConfirmed && result.value === false) {
                        // Error already shown via showValidationMessage
                    } else if (result.isConfirmed && !result.value?.success) {
                        Swal.fire({
                            title: 'Error!',
                            text: result.value?.message || 'Failed to complete order.',
                            icon: 'error'
                        });
                    }
                });
            });

            // Delete Order - SweetAlert Confirmation
            $(document).on('click', '.btn-delete-order', function() {
                let orderId = $(this).data('order-id');

                Swal.fire({
                    title: 'Delete Order',
                    text: 'Are you sure you want to delete this order? This action can be reversed later.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No, Keep it',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                url: '/admin/ajax/order/delete/' + orderId,
                                type: 'DELETE',
                                success: function(response) {
                                    resolve(response);
                                },
                                error: function(xhr) {
                                    Swal.showValidationMessage(
                                        xhr.responseJSON?.message ||
                                        'Failed to delete order'
                                    );
                                    resolve(false);
                                }
                            });
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value?.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: result.value.message || 'Order has been deleted.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    } else if (result.isConfirmed && result.value === false) {
                        // Error already shown via showValidationMessage
                    } else if (result.isConfirmed && !result.value?.success) {
                        Swal.fire({
                            title: 'Error!',
                            text: result.value?.message || 'Failed to delete order.',
                            icon: 'error'
                        });
                    }
                });
            });

            // Track Order Modal
            $(document).on('click', '.btn-track-order', function() {

                let awb = $(this).data('awb');

                if (!awb) {
                    Swal.fire('Error', 'AWB number not found', 'error');
                    return;
                }

                $('#trackOrderModal').modal('show');

                $('#trackOrderModalBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading tracking details...</p>
        </div>
    `);

                $('#trackOrderModalBody').load('/admin/admin/shiprocket/track/' + awb);
            });


        });
    </script>
@endsection
