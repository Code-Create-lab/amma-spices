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
            @if (count($errors) > 0)
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            @endif
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header card-header-primary">
                    <div class="row">
                        <div class="col-md-6">
                            <h1 class="card-title"><b>{{ $title }}</b></h1>
                        </div>
                        {{-- @dd($sel_date, $to_date, $payment_method) --}}
               {{-- {{ !isset($sel_date) || !isset($to_date) || !isset($payment_method) ? 'disabled' : '' }} --}}
                        <div class="col-md-6 text-right">
                            <button type="button" id="exportExcel" class="btn btn-success mr-2"
                                    {{ !isset($sel_date) || !isset($to_date) ? 'disabled title="Please Select Date Range"' : '' }} >
                                <i class="fa fa-file-excel"></i> Export to Excel
                            </button>
                            <a href="{{ route('sales_today') }}" class="btn btn-danger p-1">{{ __('keywords.Back') }}</a>
                        </div>
                    </div>
                </div>

                <div class="card-header card-header-secondary">
                  <form class="forms-sample" action="{{ route('datewise_orders') }}" method="post"
                        enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('keywords.Payment Method') }}</label>
                                    <select name="payment_method" class="form-control">
                                        <option disabled {{ !request('payment_method') && !isset($payment_method) ? 'selected' : '' }}>{{ __('keywords.Select payment method') }}</option>
                                        <option value="all" {{ (request('payment_method') ?? $payment_method ?? '') == 'all' ? 'selected' : '' }}>{{ __('keywords.All') }}</option>
                                        <option value="COD" {{ (request('payment_method') ?? $payment_method ?? '') == 'COD' ? 'selected' : '' }}>{{ __('keywords.COD') }}</option>
                                        <option value="online" {{ (request('payment_method') ?? $payment_method ?? '') == 'online' ? 'selected' : '' }}>{{ __('keywords.Online') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('keywords.From Date') }}</label>
                                    <input type="date" name="sel_date" class="form-control" 
                                           value="{{ request('sel_date') ?? $sel_date ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('keywords.To Date') }}</label>
                                    <input type="date" name="to_date" class="form-control" 
                                           value="{{ request('to_date') ?? $to_date ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-2"><br>
                                <div class="form-group">
                                    <label></label><br>
                                    <button type="submit"
                                        class="btn btn-primary">{{ __('keywords.Show Orders') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <hr>
                <div class="container"> <br>
                    <table id="datatableDefault" class="table text-nowrap w-100 table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th>{{ __('keywords.Cart_id') }}</th>
                                <th>{{ __('keywords.Cart price') }}</th>
                                <th>{{ __('keywords.User') }}</th>
                                <th>Order Date</th>
                                <th>{{ __('keywords.Cart Products') }}</th>
                                <th>{{ __('keywords.Payment') }}</th>
                                <th class="text-right">{{ __('keywords.Order Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($orders->isNotEmpty())
                                @php $i=1; @endphp
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="text-center">{{ $i }}</td>
                                        <td>{{ $order->cart_id }}</td>
                                        <td>{{ $order->total_price }}</td>
                                        <td>{{ $order->user?->name }}<p style="font-size:14px">
                                                    ({{ $order->user?->user_phone }})
                                                </p>
                                            </td>
                                            <td>{{ $order->order_date }}</td>
                                        <td><button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                                data-target="#exampleModal1{{ $order->cart_id }}">{{ __('keywords.Details') }}</button>
                                        </td>
                                        <td>
                                            @php
                                                $paymentStatus = strtolower($order->payment_status ?? '');
                                                $paymentMethod = strtoupper($order->payment_method ?? '');
                                            @endphp
                                            @if($paymentMethod == 'COD')
                                                <span style="color:red;font-weight:bold;">{{ $order->payment_method }}</span> - <span class="text-warning">COD</span>
                                            @elseif(in_array($paymentStatus, ['paid', 'success', 'successful']))
                                                <span style="color:green;font-weight:bold;">{{ $order->payment_method }}</span> - <span class="text-success">Paid</span>
                                            @elseif($paymentStatus == 'pending')
                                                <span style="color:orange;font-weight:bold;">{{ $order->payment_method ?? 'N/A' }}</span> - <span class="text-warning">Pending</span>
                                            @elseif($paymentStatus == 'failed')
                                                <span style="color:red;font-weight:bold;">{{ $order->payment_method ?? 'N/A' }}</span> - <span class="text-danger">Failed</span>
                                            @else
                                                {{ $order->payment_method ?? 'N/A' }} - {{ ucfirst($order->payment_status ?? 'Pending') }}
                                            @endif
                                        </td>
                                        <td class="td-actions text-right">
                                            @if($order->order_status == 'Pending' || $order->order_status == 'pending')
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
                                    </tr>
                                    @php $i++; @endphp
                                @endforeach
                            @else
                                <tr>
                                    <td>{{ __('keywords.No data found') }}</td>
                                    @for ($i = 1; $i < 10; $i++)
                                        <td style="display:none"></td>
                                    @endfor
                                </tr>
                            @endif
                        </tbody>
                    </table><br />
                    <div class="pull-right mb-1" style="float: right;">
                        {{ $orders->render('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!--/////////details model//////////-->
    @foreach ($orders as $order)
        <div id="printThis">
            <div class="modal fade" id="exampleModal1{{ $order->cart_id }}" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="container">

                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">{{ __('keywords.Order Details') }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="material-datatables">
                                <form role="form" method="post" action="">
                                    <table id="datatables" class="table table-striped table-no-bordered table-hover"
                                        cellspacing="0" width="100%" style="width:100%" data-background-color="purple">


                                        <tbody>
                                            <tr>
                                                <td colspan="5">
                                                    <table class="table">
                                                        <tr>
                                                            <td valign="top" style="width:50%">
                                                                <strong> {{ __('keywords.Order_Id') }} : </strong>
                                                                {{ $order->cart_id }}
                                                                <br />
                                                                <strong>{{ __('keywords.Customer_name') }} :
                                                                </strong>{{ $order->address->receiver_name }}<br />
                                                                <strong>{{ __('keywords.Contact') }} :
                                                                </strong>{{ $order->address->receiver_phone }},
                                                                @if ($order->user_phone != $order->receiver_phone)
                                                                    {{ $order->address->receiver_phone }}
                                                                @endif <br />
                                                                <strong>Expected Delivery Date: 
                                                                </strong> <br>{{ $order->delivery_date }}
                                                                <br />
                                                                <strong> Order Date :
                                                                </strong>{{ $order->order_date }}
                                                                <br />
                                                            </td>
                                                            <td style="width:50%" align="right">
                                                                <strong> {{ __('keywords.Delivery Address') }}
                                                                </strong><br />

                                                                <b>{{ $order->address->type }} :</b>
                                                                {{ $order->address->house_no }},{{ $order->address->society }},<br>
                                                                @if ($order->address->landmark != null)
                                                                    {{ $order->address->landmark }},
                                                                    <br>
                                                                @endif
                                                                {{ $order->address->city }},{{ $order->address->state }},<br>
                                                                {{ $order->address->pincode }}
                                                            </td>

                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>{{ __('keywords.Product_Name') }}</th>
                                                <th>{{ __('keywords.Qty') }}</th>
                                                <th>{{ __('keywords.Tax') }}</th>
                                                <th>{{ __('keywords.Price') }}</th>
                                                <th>{{ __('keywords.Total_Price') }}</th>
                                            </tr>
                                            @if ($order->orderItems->isNotEmpty())
                                                @php $i=1; @endphp

                                                <tr>
                                                    @foreach ($order->orderItems as $item)
                                                        @if ($order->cart_id == $item->order_cart_id)
                                                            <td>
                                                                <p><img style="width:25px;height:25px; border-radius:50%"
                                                                        src="{{ url('storage/'.$item->varient_image) }}"
                                                                        alt="$detailss->product_name">
                                                                    {{ $item->product_name }}({{ $item->quantity }})
                                                                </p>
                                                            </td>
                                                            <td>{{ $item->qty }}</td>
                                                            <td>
                                                                @if ($item->tx_per == 0 || $item->tx_per == null)
                                                                    0
                                                                @else
                                                                    {{ $item->tx_per }}
                                                                    @endif % @if ($item->tx_per != 0 && $item->tx_name != null)
                                                                        ({{ $item->tx_name }})
                                                                    @endif
                                                            </td>
                                                            <td>
                                                                <p><span style="color:grey">
                                                                        @if ($item->price_without_tax != null)
                                                                            {{ $item->price_without_tax }}
                                                                        @else
                                                                            {{ $item->price }}
                                                                        @endif
                                                                    </span></p>
                                                            </td>
                                                            <td>
                                                                <p><span style="color:grey">{{ $item->price }}</span>
                                                                </p>
                                                            </td>
                                                        @endif
                                                </tr>
                                                @php $i++; @endphp
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5">{{ __('keywords.No data found') }}</td>
                                            </tr>
    @endif


    <tr>
        <td colspan="4" class="text-right"><strong class="pull-right">{{ __('keywords.Products_Price') }} : </strong>
        </td>
        <td class="text-right" colspan="1">
            <strong>{{ $order->price_without_delivery  +  $order->coupon_discount }}</strong>
        </td>
    </tr>
    <tr>
        <td colspan="4" class="text-right"><strong class="pull-right">{{ __('keywords.Delivery_Charge') }} :
            </strong></td>
        <td class="text-right" colspan="1">
            <strong>+{{ $order->delivery_charge }}</strong>
        </td>
    </tr>
    @if ($order->paid_by_wallet > 0)
        <tr>
            <td colspan="4" class="text-right"><strong class="pull-right">{{ __('keywords.Paid By Wallet') }} :
                </strong></td>
            <td class="text-right" colspan="1">
                <strong>-{{ $order->paid_by_wallet }}</strong>
            </td>
        </tr>
    @endif
    @if ($order->coupon_discount > 0)
        <tr>
            <td colspan="4" class="text-right"><strong class="pull-right">{{ __('keywords.Coupon Discount') }} :
                </strong></td>
            <td class="text-right" colspan="1">
                <strong class="">-{{ $order->coupon_discount }}</strong>
            </td>
        </tr>
    @endif
    <tr>
        <td colspan="4" class="text-right"><strong
                class="pull-right">{{ __('keywords.Net_Total(Payable)') }}:</strong></td>
        <td class="text-right" colspan="1">{{ $order->rem_price }}</td>
    </tr>
    </tbody>
    </table>
    </form>
    </div>
    <div class="modal-footer">
        <button class="btn btn-danger" data-dismiss="modal" aria-hidden="true">{{ __('keywords.Close') }}</button>
    </div>
    </div>

    <!-- end content-->
    </div>
    </div>
    <!--  end card  -->

    </div>
    </div>
    </div>
    </div>
    @endforeach

@endsection

@section('postload-section')
    {{-- You must include files that have no direct efect on the load of the page and can be loaded meanwhile other tasks can be performed by user --}}
    <script>
        // Check if DataTable already exists and destroy it
        if ($.fn.DataTable.isDataTable('#datatableDefault')) {
            $('#datatableDefault').DataTable().destroy();
        }

        $('#datatableDefault').DataTable({
            dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8'f>>t<'d-flex align-items-center'<'mr-auto'i><'mb-0'p>>",
            responsive: true,
            lengthMenu: false,
            autoWidth: false,
            columnDefs: [
                { orderable: false, targets: [5, 6, 7] } // Cart Products, Payment, Order Status columns
            ],
            select: true,
            scrollX: true,
            processing: true,
            ordering: true,
            paging: false,
            order: [[0, 'asc']] // Order by first column (#) by default
        });

        // Excel export with filters
        $('#exportExcel').click(function() {
            // Don't export if button is disabled
            if ($(this).prop('disabled')) {
                return false;
            }

            let url = "{{ route('datewise_orders_export') }}";
            let params = new URLSearchParams({
                sel_date: "{{ $sel_date ?? '' }}",
                to_date: "{{ $to_date ?? '' }}",
                payment_method: "{{ $payment_method ?? '' }}"
            });

            window.location.href = url + '?' + params.toString();
        });
    </script>
@endsection
