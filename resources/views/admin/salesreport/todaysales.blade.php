@extends('admin.layout.app')

@section('preload-section')
    <style>
        .collo {
            overflow-y: hidden;
            overflow-x: scroll;
            -webkit-overflow-scrolling: touch;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-lg-12">
            @if (session()->has('success'))
                <div class="alert alert-success">
                    @if (is_array(session()->get('success')))
                        <ul>
                            @foreach (session()->get('success') as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    @else
                        {{ session()->get('success') }}
                    @endif
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header card-header-primary">
                    <div class="row">
                        <div class="col-md-6">
                            <h1 class="card-title"><b>{{ $title }}</b></h1>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" id="exportExcel" class="btn btn-success mr-2"
                                 {{ !isset($sel_date) || !isset($to_date) ? "disabled title=Select-Date-Range-To-Export-Data" : '' }}>
                                <i class="fa fa-file-excel"></i> Export to Excel
                            </button>
                            <a href="{{ route('sales_today') }}" class="btn btn-danger p-1">{{ __('keywords.Back') }}</a>
                        </div>
                    </div>
                </div>

                <div class="card-header card-header-secondary">
                    <form action="{{ route('datewise_orders') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('keywords.Payment Method') }}</label>
                                    <select name="payment_method" class="form-control">
                                        <option disabled selected>{{ __('keywords.Select payment method') }}</option>
                                        <option value="all">{{ __('keywords.All') }}</option>
                                        {{-- <option value="COD">{{ __('keywords.COD') }}</option> --}}
                                        <option value="online">{{ __('keywords.Online') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('keywords.From Date') }}</label>
                                    <input type="date" name="sel_date" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('keywords.To Date') }}</label>
                                    <input type="date" name="to_date" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit"
                                    class="btn btn-primary btn-block">{{ __('keywords.Show Orders') }}</button>
                            </div>
                        </div>
                    </form>
                </div>

                <hr>

                <div class="container mt-3">
                    <table id="todaysalesdatatable" class="table text-nowrap w-100 table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th>{{ __('keywords.Order_Id') }}</th>
                                <th>{{ __('keywords.total_price') }}</th>
                                <th>{{ __('keywords.User') }}</th>
                                <th>{{ __('keywords.Order Status') }}</th>
                                <th>{{ __('keywords.Cart Products') }}</th>
                                <th>{{ __('keywords.Payment Status') }}</th>
                                <th>Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $index => $order)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $order->cart_id }}</td>
                                    <td>{{ $order->total_price }}</td>
                                    <td>{{ $order->address->receiver_name ?? $order->user?->name  }}<p style="font-size:14px">
                                                    ({{  $order->address->receiver_phone ?? $order->user?->user_phone }} | {{ $order->address->receiver_email ?? $order->user?->email }})
                                                </p>
                                    </td>
                                    <td>
                                        @if ($order->order_status == 'Pending' || $order->order_status == 'pending')
                                            <span style="color:orange;font-weight:bold;">Pending</span>
                                        @elseif($order->order_status == 'Confirmed' || $order->order_status == 'confirmed')
                                            <span style="color:purple;font-weight:bold;">Confirmed</span>
                                        @elseif($order->order_status == 'Completed' || $order->order_status == 'completed')
                                            <span style="color:green;font-weight:bold;">Completed</span>
                                        @elseif($order->order_status == 'Cancelled' || $order->order_status == 'cancelled')
                                            <span style="color:red;font-weight:bold;">Cancelled</span>
                                        @else
                                            <span>{{ ucfirst($order->order_status ?? 'NOT PLACED') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#exampleModal1{{ $order->cart_id }}">
                                            {{ __('keywords.Details') }}
                                        </button>
                                    </td>
                                    <td>
                                        @php
                                            $paymentStatus = strtolower($order->payment_status ?? '');
                                            $paymentMethod = strtoupper($order->payment_method ?? '');
                                        @endphp
                                        @if ($paymentMethod == 'COD')
                                            <span class="text-warning">COD</span>
                                        @elseif(in_array($paymentStatus, ['paid', 'success', 'successful']))
                                            <span class="text-success">Paid</span>
                                        @elseif($paymentStatus == 'pending')
                                            <span class="text-warning">Pending</span>
                                        @elseif($paymentStatus == 'failed')
                                            <span class="text-danger">Failed</span>
                                        @else
                                            <span>{{ ucfirst($order->payment_status ?? 'Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (in_array(strtoupper($order->payment_method ?? ''), ['COD']))
                                            <b style="color:red;">{{ $order->payment_method }}</b>
                                        @else
                                            <b style="color:green;">{{ $order->payment_method ?? 'N/A' }}</b>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td>{{ __('keywords.No data found') }}</td>
                                    @for ($i = 1; $i < 10; $i++)
                                        <td style="display: none"></td>
                                    @endfor
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="pull-right mb-1" style="float: right;">
                        {{ $orders->render('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modals -->
    @foreach ($orders as $order)
        <div class="modal fade" id="exampleModal1{{ $order->cart_id }}" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="container">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('keywords.Order Details') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <table class="table">
                                <tr>
                                    <td width="50%">
                                        <strong>{{ __('keywords.Order_Id') }}:</strong> {{ $order->cart_id }}<br>
                                         <strong>{{ __('keywords.Customer_name') }}:</strong>
                                            {{ $order->address->receiver_name ?? $order->user?->name }}</br>
                                        <strong>{{ __('keywords.Customer_email') }}:</strong>
                                            {{ $order->address->receiver_email ?? $order->user?->email }}</br>
                                        <strong>{{ __('keywords.Contact') }}:</strong>
                                        {{ $order->address->receiver_phone }}<br>
                                        <strong>{{ __('keywords.Alternate_Contact') }}:</strong>
                                        {{ $order->address->alternate_phone ?? ''  }}<br>
                                        <strong>Expected Delivery Date:</strong>
                                        {{ $order->delivery_date }}<br>
                                        <strong>Order Date:</strong> {{ $order->order_date }}
                                    </td>
                                    <td width="50%" class="text-right">
                                        <strong>{{ __('keywords.Delivery Address') }}</strong><br>
                                        <b>{{ $order->address->type }}:</b>
                                        {{ $order->address->house_no }}, {{ $order->address->society }}<br>
                                        @if ($order->address->landmark)
                                            {{ $order->address->landmark }}<br>
                                        @endif
                                        {{ $order->address->city }}, {{ $order->address->state }}<br>
                                        {{ $order->address->pincode }}
                                    </td>
                                </tr>
                            </table>

                            <table class="table table-striped">
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
                                    @forelse ($order->orderItems as $item)
                                        <tr>
                                            <td>
                                                <img src="{{ asset('storage/' . $item->varient_image) }}"
                                                    style="width:25px; height:25px; border-radius:50%" alt="">
                                                {{ $item->product_name }} ({{ $item->quantity }})
                                            </td>
                                            <td>{{ $item->qty }}</td>
                                            <td>
                                                {{ $item->tx_per ?? 0 }}%
                                                @if ($item->tx_per && $item->tx_name)
                                                    ({{ $item->tx_name }})
                                                @endif
                                            </td>
                                            <td>{{ $item->price_without_tax ?? $item->price }}</td>
                                            <td>{{ $item->price }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">{{ __('keywords.No data found') }}</td>
                                        </tr>
                                    @endforelse

                                    <tr>
                                        <td colspan="4" class="text-right">
                                            <strong>{{ __('keywords.Products_Price') }}:</strong>
                                        </td>
                                        <td>{{ $order->price_without_delivery + $order->coupon_discount }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right">
                                            <strong>{{ __('keywords.Delivery_Charge') }}:</strong>
                                        </td>
                                        <td>+{{ $order->delivery_charge }}</td>
                                    </tr>
                                    @if ($order->paid_by_wallet > 0)
                                        <tr>
                                            <td colspan="4" class="text-right">
                                                <strong>{{ __('keywords.Paid By Wallet') }}:</strong>
                                            </td>
                                            <td>-{{ $order->paid_by_wallet }}</td>
                                        </tr>
                                    @endif
                                    @if ($order->coupon_discount > 0)
                                        <tr>
                                            <td colspan="4" class="text-right">
                                                <strong>{{ __('keywords.Coupon Discount') }}:</strong>
                                            </td>
                                            <td>-{{ $order->coupon_discount }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4" class="text-right">
                                            <strong>{{ __('keywords.Net_Total(Payable)') }}:</strong>
                                        </td>
                                        <td>{{ $order->total_price }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-danger" data-dismiss="modal">{{ __('keywords.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('postload-section')
    <script>
        $('#todaysalesdatatable').DataTable({
            dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8'f>>t<'d-flex align-items-center'<'mr-auto'i><'mb-0'p>>",
            responsive: true,
            autoWidth: true,
            select: true,
            scrollX: true,
            processing: true,
            ordering: true,
            paging: false
        });

        // Server-side Excel export
        $('#exportExcel').click(function() {
            let search = $('.dataTables_filter input').val();
            let url = "{{ route('orders.export') }}";

            if (search) {
                url += '?search=' + search;
            }

            window.location.href = url;
        });
    </script>
@endsection
