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

        .buttons-html5 {
            color: white !important;
            background-color: #35d26d !important;
            border-radius: 5px;
            margin: 2px !important;
        }

        .buttons-print {
            color: white !important;
            background-color: #35d26d !important;
            border-radius: 5px;
            margin: 2px !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
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
                        <h4 class="card-title ">{{ __('keywords.Cancelled orders') }}</h4>
                    </div>
                    <div class="container"> <br>
                        <table id="datatableDefault" class="table table-striped text-nowrap w-100">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>{{ __('keywords.Cart_id') }}</th>
                                    <th>{{ __('keywords.Cart price') }}</th>
                                    <th>{{ __('keywords.User') }}</th>
                                    <th>{{ __('keywords.Order_Date') }}</th>
                                    <th>{{ __('keywords.Delivery_Date') }}</th>
                                    <th>{{ __('keywords.Status') }}</th>
                                    <th>{{ __('keywords.Payment Method') }}</th>
                                    <th>{{ __('keywords.Payment Status') }}</th>
                                    <th>{{ __('keywords.Cart Products') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($orders) > 0)
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
                                            <td>{{ $order->delivery_date }}({{ $order->time_slot }})</td>
                                            @if ($order->order_status == null || $order->order_status == '')
                                                <td>
                                                    <p><span class="dot" style="color:orange"></span><b
                                                            style="color:orange">NOT PLACED</b></p>
                                                </td>
                                            @endif
                                            @if ($order->order_status == 'Pending' || $order->order_status == 'pending')
                                                <td>
                                                    <p><span class="dot" style="color:orange"></span><b
                                                            style="color:orange">Pending</b></p>
                                                </td>
                                            @endif
                                            @if ($order->order_status == 'Confirmed' || $order->order_status == 'confirmed')
                                                <td>
                                                    <p><span class="dot" style="color:purple"></span><b
                                                            style="color:purple">Confirmed</b></p>
                                                </td>
                                            @endif
                                            @if ($order->order_status == 'out_for_delivery' || $order->order_status == 'Out_For_Delivery')
                                                <td>
                                                    <p><span class="dot" style="color:orange"></span><b
                                                            style="color:orange">Out For Delivery</b></p>
                                                </td>
                                            @endif
                                            @if ($order->order_status == 'Cancelled' || $order->order_status == 'cancelled')
                                                <td>
                                                    <p><span class="dot" style="color:red"></span><b
                                                            style="color:red">Cancelled</b></p>
                                                </td>
                                            @endif
                                            @if ($order->order_status == 'Completed' || $order->order_status == 'completed')
                                                <td>
                                                    <p><span class="dot" style="color:green"></span><b
                                                            style="color:green">Completed</b></p>
                                                </td>
                                            @endif
                                          
                                            <td>
                                                @if (
                                                    $order->payment_method == 'COD' ||
                                                        $order->payment_method == 'cod' ||
                                                        $order->payment_method == 'Cod' ||
                                                        $order->payment_method == 'COD')
                                                    <b style="color:red">
                                                    @else
                                                        <b style="color:green">
                                                @endif {{ $order->payment_method }}</b>
                                            </td>
                                              <td>{{$order->payment_status}}</td>
                                            <td><button type="button" class="btn btn-primary" data-toggle="modal"
                                                    data-target="#exampleModal1{{ $order->cart_id }}">{{ __('keywords.Details') }}</button>
                                            </td>
                                        </tr>
                                        @php $i++; @endphp
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ __('keywords.No data found') }}</td>
                                        @for ($i = 1; $i < 9; $i++)
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
    </div>
    <div>
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
                                                                @if ($order->address->user_phone != $order->address->receiver_phone)
                                                                    {{ $order->address->receiver_phone }}
                                                                    {{ $order->address->alternate_phone }}
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

                                                                <b>{{ $order->type }} :</b>
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
                                                        @if ($item->order_cart_id == $order->cart_id)
                                                            <td>
                                                                <p><img style="width:25px;height:25px; border-radius:50%"
                                                                        src="{{ asset('storage/' . $item->varient_image) }}"
                                                                        alt="$detailss->product_name">
                                                                    {{ $item->product_name }}({{ $item->quantity }})
                                                                    @if ($item->variation->variation_attributes->isNotEmpty())
                                                                        <div class="order-subtext mt-1">
                                                                            @foreach ($item->variation->variation_attributes as $attribute)
                                                                                <span class="badge bg-secondary me-1">
                                                                                    {{ $attribute->attribute->attribute->name }}:
                                                                                    {{ $attribute->attribute->name }}
                                                                                </span>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                            </div>
                            </p>
                            </td>
                            <td>{{ $item->quantity }}</td>
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
        <td colspan="4" class="text-right"><strong class="pull-right">{{ __('keywords.Delivery_Charge') }} : </strong>
        </td>
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
                </strong>({{ $order->coupon?->coupon_name }})</td>
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
    @endforeach
    </div>
@endsection

@section('postload-section')
    {{-- You must include files that have no direct efect on the load of the page and can be loaded meanwhile other tasks can be performed by user --}}
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });
        });

        $('#datatableDefault').DataTable({
            dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8 text-right'<'d-flex justify-content-end'B>>>t<'d-flex align-items-center'<'mr-auto'i><'mb-0'p>>",
            responsive: true,
            lengthMenu: false,
            autoWidth: true,
            select: true,
            scrollX: true,
            processing: true,
            ordering: true,
            paging: false,
            buttons: [{
                extend: 'csv',
                className: 'btn btn-default'
            }]
        });
    </script>
@endsection
