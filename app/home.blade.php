@extends('admin.layout.app', ['title' => 'Dashboard'])

@section('preload-section')
    {{-- You must include files that need to be preloaded: Syncronous scripts and Stylesheets mostly --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">


    <style>
        .filter-form {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .filter-form .form-group {
            margin-right: 15px;
        }

        .filter-form label {
            display: block;
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        @media (max-width: 768px) {
            .filter-form .d-flex {
                flex-direction: column;
                align-items: stretch !important;
            }

            .filter-form .form-group {
                margin-bottom: 10px;
                margin-right: 0;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <form action="{{ route('adminHome') }}" method="get" class="filter-form" id="filterForm">
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <!-- Date Range Filter -->
                    <div class="form-group mb-0">
                        <label class="small text-muted mb-1">Date Range</label>
                        <div class="d-flex align-items-center">
                            <input type="date" class="form-control form-control-sm" id="startDate" name="startDate"
                                value="{{ request('startDate') }}" style="max-width: 140px;">
                            <span class="mx-2 text-muted">to</span>
                            <input type="date" class="form-control form-control-sm" id="endDate" name="endDate"
                                value="{{ request('endDate') }}" style="max-width: 140px;">
                        </div>
                    </div>

                    <!-- Quick Date Filter -->
                    <div class="form-group mb-0">
                        <label class="small text-muted mb-1">Quick Filter</label>
                        <select class="form-control form-control-sm" id="quickDateFilter" name="quickDateFilter"
                            style="min-width: 130px;">
                            <option value="">Select Period</option>
                            <option value="today" {{ request('quickDateFilter') == 'today' ? 'selected' : '' }}>Today
                            </option>
                            <option value="yesterday" {{ request('quickDateFilter') == 'yesterday' ? 'selected' : '' }}>
                                Yesterday</option>
                            <option value="last7days" {{ request('quickDateFilter') == 'last7days' ? 'selected' : '' }}>Last
                                7 Days</option>
                            <option value="last30days" {{ request('quickDateFilter') == 'last30days' ? 'selected' : '' }}>
                                Last 30 Days</option>
                            <option value="thisMonth" {{ request('quickDateFilter') == 'thisMonth' ? 'selected' : '' }}>This
                                Month</option>
                            <option value="lastMonth" {{ request('quickDateFilter') == 'lastMonth' ? 'selected' : '' }}>Last
                                Month</option>
                            <option value="thisYear" {{ request('quickDateFilter') == 'thisYear' ? 'selected' : '' }}>This
                                Year</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    {{-- <div class="form-group mb-0">
                        <label class="small text-muted mb-1">Status</label>
                        <select class="form-control form-control-sm" id="statusFilter" name="statusFilter"
                            style="min-width: 120px;">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('statusFilter') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="completed" {{ request('statusFilter') == 'completed' ? 'selected' : '' }}>
                                Completed</option>
                            <option value="cancelled" {{ request('statusFilter') == 'cancelled' ? 'selected' : '' }}>
                                Cancelled</option>
                            <option value="new" {{ request('statusFilter') == 'new' ? 'selected' : '' }}>New</option>
                        </select>
                    </div> --}}

                    <!-- Buttons -->
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-sm" id="applyFilters">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                        <a href="{{ route('adminHome') }}">

                            <button type="button" class="btn btn-secondary btn-sm" id="resetFilters">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </a>
                    </div>

                    <!-- Language Selector (moved to same row) -->
                    {{-- <div class="form-group mb-0 ms-auto">
                        <label class="small text-muted mb-1">Language</label>
                        <select class="form-control form-control-sm changeLang" style="min-width: 100px;">
                            <option value="en" {{ session()->get('locale') == 'en' ? 'selected' : '' }}>English
                            </option>
                            <option value="bu" {{ session()->get('locale') == 'bu' ? 'selected' : '' }}>Bulgarian
                            </option>
                            <option value="in" {{ session()->get('locale') == 'in' ? 'selected' : '' }}>Hindi</option>
                            <option value="ch" {{ session()->get('locale') == 'ch' ? 'selected' : '' }}>Chinese
                            </option>
                        </select>
                    </div> --}}
                </div>
            </form>
        </div>
    </div>

    <!-- Add Active Filter Display (Optional) -->
    @if (request('startDate') || request('endDate') || request('quickDateFilter') || request('statusFilter'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-info py-2">
                    <small>
                        <strong>Active Filters:</strong>
                        @if (request('startDate') && request('endDate'))
                            Date: {{ request('startDate') }} to {{ request('endDate') }}
                        @endif
                        @if (request('quickDateFilter'))
                            | Period:
                            {{ ucfirst(str_replace(['7days', '30days'], [' 7 Days', ' 30 Days'], request('quickDateFilter'))) }}
                        @endif
                        @if (request('statusFilter'))
                            | Status: {{ ucfirst(request('statusFilter')) }}
                        @endif
                        <a href="{{ route('adminHome') }}" class="btn btn-sm btn-link text-danger float-end">Clear All</a>
                    </small>
                </div>
            </div>
        </div>
    @endif



    <!-- BEGIN row -->
    <div class="row mt-3">
        <!-- BEGIN col-6 -->


        <!-- END col-6 -->

        <!-- BEGIN col-6 -->
        <div class="col-xl-12">
            <!-- BEGIN row - Alternative Modern Design with Gradients -->
            <div class="row g-4 mb-3">
                <!-- Pending Orders Card -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="stats-card card-pink">
                        <div class="card-content">
                            <div class="card-header-section">
                                <div class="icon-wrapper">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stats-value">{{ $this_week_pen }}</div>
                            </div>
                            <a href="{{ url('admin/admin/pending_orders') }}" class="card-link">
                                <div class="card-footer-section">
                                    <span class="stats-label">{{ __('keywords.Pending Orders') }}</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Users Card -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="stats-card card-indigo">
                        <div class="card-content">
                            <div class="card-header-section">
                                <div class="icon-wrapper">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stats-value">{{ $this_week_usr }}</div>
                            </div>
                            <a href="{{ url('admin/user/list') }}" class="card-link">
                                <div class="card-footer-section">
                                    <span class="stats-label">Users</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Failure Orders Card -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="stats-card card-danger">
                        <div class="card-content">
                            <div class="card-header-section">
                                <div class="icon-wrapper">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stats-value">{{ $totalFailureOrder }}</div>
                            </div>
                            <a href="{{ url('admin/admin/payment_failed_orders') }}" class="card-link">
                                <div class="card-footer-section">
                                    <span class="stats-label">Failed Orders</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Orders Card -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="stats-card card-orange">
                        <div class="card-content">
                            <div class="card-header-section">
                                <div class="icon-wrapper">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stats-value">{{ $totalOrder }}</div>
                            </div>
                            <a href="{{ url('admin/admin/all_orders') }}" class="card-link">
                                <div class="card-footer-section">
                                    <span class="stats-label">Total Orders</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Orders Card -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="stats-card card-secondary">
                        <div class="card-content">
                            <div class="card-header-section">
                                <div class="icon-wrapper">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="stats-value">{{ $this_week_can }}</div>
                            </div>
                            <a href="{{ url('admin/admin/cancelled_orders') }}" class="card-link">
                                <div class="card-footer-section">
                                    <span class="stats-label">{{ __('keywords.Cancelled Orders') }}</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Complete Orders Card -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="stats-card card-success">
                        <div class="card-content">
                            <div class="card-header-section">
                                <div class="icon-wrapper">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stats-value">{{ $this_week_ord }}</div>
                            </div>
                            <a href="{{ url('admin/admin/completed_orders') }}" class="card-link">
                                <div class="card-footer-section">
                                    <span class="stats-label">Complete Orders</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END row -->

            <style>
                /* Modern Stats Card Styles */
                .stats-card {
                    border-radius: 16px;
                    padding: 24px;
                    position: relative;
                    overflow: hidden;
                    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                    border: none;
                    height: 100%;
                    min-height: 160px;
                }

                .stats-card:hover {
                    transform: translateY(-8px) scale(1.02);
                    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
                }

                .stats-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    right: 0;
                    width: 100px;
                    height: 100px;
                    border-radius: 50%;
                    opacity: 0.1;
                    transform: translate(30%, -30%);
                }

                /* Card Colors with Gradients */
                .card-pink {
                    background: linear-gradient(135deg, #EC407A 0%, #F48FB1 100%);
                    color: white;
                }

                .card-pink::before {
                    background: white;
                }

                .card-indigo {
                    background: linear-gradient(135deg, #5C6BC0 0%, #9FA8DA 100%);
                    color: white;
                }

                .card-indigo::before {
                    background: white;
                }

                .card-danger {
                    background: linear-gradient(135deg, #EF5350 0%, #E57373 100%);
                    color: white;
                }

                .card-danger::before {
                    background: white;
                }

                .card-orange {
                    background: linear-gradient(135deg, #FF9800 0%, #FFB74D 100%);
                    color: white;
                }

                .card-orange::before {
                    background: white;
                }

                .card-secondary {
                    background: linear-gradient(135deg, #78909C 0%, #B0BEC5 100%);
                    color: white;
                }

                .card-secondary::before {
                    background: white;
                }

                .card-success {
                    background: linear-gradient(135deg, #26A69A 0%, #80CBC4 100%);
                    color: white;
                }

                .card-success::before {
                    background: white;
                }

                /* Card Content */
                .card-content {
                    position: relative;
                    z-index: 1;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    height: 100%;
                }

                .card-header-section {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    margin-bottom: 20px;
                }

                .icon-wrapper {
                    background: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                    width: 50px;
                    height: 50px;
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                }

                .stats-value {
                    font-size: 2rem;
                    font-weight: 700;
                    line-height: 1;
                }

                .card-footer-section {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding-top: 12px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                }

                .stats-label {
                    font-size: 0.875rem;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    opacity: 0.9;
                }

                .card-link {
                    color: inherit;
                    text-decoration: none;
                    display: block;
                }

                .card-link:hover {
                    color: inherit;
                }

                .card-footer-section i {
                    transition: transform 0.3s ease;
                }

                .stats-card:hover .card-footer-section i {
                    transform: translateX(5px);
                }

                /* Responsive Design */
                @media (max-width: 768px) {
                    .stats-card {
                        padding: 20px;
                        min-height: 140px;
                    }

                    .stats-value {
                        font-size: 1.75rem;
                    }

                    .icon-wrapper {
                        width: 45px;
                        height: 45px;
                        font-size: 20px;
                    }

                    .stats-label {
                        font-size: 0.8rem;
                    }
                }
            </style>
        </div>
    </div>
    <!-- END col-6 -->
    <div class="row">
        <div class="col-xl-12">
            <!-- BEGIN card -->
            <div class="card">
                <!-- BEGIN card-body -->
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ __('keywords.Orders') }}</h5>
                            <div class="fs-13px">{{ __('keywords.Latest order history') }}</div>
                        </div>
                    </div>

                    <!-- BEGIN table-responsive -->
                    <div class="table-responsive mb-n2">
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th class="pl-0">#</th>
                                    <th>{{ __('keywords.Cart_id') }}</th>
                                    <th>User Details</th>
                                    <th class="text-center">{{ __('keywords.Status') }}</th>
                                    <th class="text-right pr-0">Amount</th>
                                    <th class="text-right pr-0">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($ongoin) > 0)
                                    @php $i=1; @endphp
                                    @foreach ($ongoin as $ongoing)
                                        <tr>
                                            <td class="pl-0">{{ $i }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">

                                                    <div class="ml-3 flex-grow-1">
                                                        <div class="font-weight-600 text-dark">{{ $ongoing->cart_id }}
                                                        </div>
                                                        <div class="fs-13px">{{ $ongoing->order_date }}</div>
                                                    </div>

                                                </div>
                                            </td>
                                            <td>
                                                <div class="ml-3 flex-grow-1">
                                                    <div class="font-weight-600 text-dark">{{ $ongoing->name }}</div>
                                                    <div class="fs-13px">{{ $ongoing->user_phone }}</div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if ($ongoing->order_status == 'Pending')
                                                    <span class="label bg-warning-transparent-2 text-warning"
                                                        style="min-width: 60px;padding: 5px;border-radius: 11px;">
                                                @endif
                                                @if ($ongoing->order_status == 'Confirmed')
                                                    <span class="label bg-success-transparent-2 text-success"
                                                        style="min-width: 60px;padding: 5px;border-radius: 11px;">
                                                @endif
                                                @if ($ongoing->order_status == 'Completed')
                                                    <span class="label bg-success-transparent-2 text-success"
                                                        style="min-width: 60px;padding: 5px;border-radius: 11px;">
                                                @endif
                                                @if ($ongoing->order_status == 'Cancelled')
                                                    <span class="label text-dark-transparent-5"
                                                        style="min-width: 60px;padding: 5px;border-radius: 11px;">
                                                @endif
                                                @if ($ongoing->order_status == 'Out_For_Delivery')
                                                    <span class="label bg-success-transparent-2 text-success"
                                                        style="min-width: 60px;    padding: 5px;border-radius: 11px;">
                                                @endif
                                                {{ $ongoing->order_status }}</span>
                                            </td>
                                            <td class="text-right pr-0"> {{ $ongoing->total_price }}</td>
                                            <td class="text-right pr-0"><button type="button" class="btn btn-primary"
                                                    data-toggle="modal"
                                                    data-target="#exampleModal1{{ $ongoing->cart_id }}">{{ __('keywords.Details') }}</button>
                                            </td>
                                        </tr>
                                        @php $i++; @endphp
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6">{{ __('keywords.No data found') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <!-- END table-responsive -->
                </div>
                <!-- END card-body -->
            </div>
            <!-- END card -->
        </div>

    </div>


    </div>
    <!-- END row -->
    <!-- BEGIN row -->

    <!-- END row -->
    <!--/////////Order details model//////////-->
    @foreach ($ongoin as $ords)
        <div id="printThis">
            <div class="modal fade" id="exampleModal1{{ $ords->cart_id }}" tabindex="-1" role="dialog"
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
                                        cellspacing="0" width="100%" style="width:100%"
                                        data-background-color="purple">


                                        <tbody>
                                            <tr>
                                                <td colspan="5">
                                                    <table class="table">
                                                        <tr>
                                                            <td valign="top" style="width:50%">
                                                                <strong> {{ __('keywords.Order_Id') }} : </strong>
                                                                {{ $ords->cart_id }}
                                                                <br />
                                                                <strong>{{ __('keywords.Customer_name') }} :
                                                                </strong>{{ $ords->user->name }}<br />
                                                                <strong>{{ __('keywords.Contact') }} :
                                                                </strong>{{ $ords->user->phone }}, @if ($ords->user_phone != $ords->receiver_phone)
                                                                    {{ $ords->receiver_phone }}
                                                                @endif <br />
                                                                {{-- <strong> Expected Delivery Date :
                                                                </strong>
                                                                <br>
                                                                {{ $ords->delivery_date }}
                                                                <br /> --}}
                                                                <strong> Order Date :
                                                                </strong>{{ $ords->order_date }}
                                                                <br />
                                                            </td>
                                                            <td style="width:50%" align="right">
                                                                <strong> {{ __('keywords.Delivery Address') }}
                                                                </strong><br />

                                                                <b>{{ $ords->type }} :</b>
                                                                {{ $ords->house_no }},{{ $ords->society }},<br>
                                                                @if ($ords->landmark != null)
                                                                    {{ $ords->landmark }},
                                                                    <br>
                                                                @endif
                                                                {{ $ords->city }},{{ $ords->state }},<br>
                                                                {{ $ords->pincode }}
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
                                            @if (count($details) > 0)
                                                @php $i=1; @endphp

                                                <tr>
                                                    @foreach ($details as $detailss)
                                                        @if ($detailss->cart_id == $ords->cart_id)
                                                            <td>
                                                                <p><img style="width:25px;height:25px; border-radius:50%"
                                                                        src="/storage/{{ $detailss->varient_image }}"
                                                                        alt="">
                                                                    {{ $detailss->product_name }}({{ $detailss->quantity }}{{ $detailss->unit }})
                                                                </p>
                                                            </td>
                                                            <td>{{ $detailss->qty }}</td>
                                                            <td>
                                                                @if ($detailss->tx_per == 0 || $detailss->tx_per == null)
                                                                    0
                                                                @else
                                                                    {{ $detailss->tx_per }}
                                                                    @endif % @if ($detailss->tx_per != 0 && $detailss->tx_name != null)
                                                                        ({{ $detailss->tx_name }})
                                                                    @endif
                                                            </td>
                                                            <td>
                                                                <p><span style="color:grey">
                                                                        @if ($detailss->price_without_tax != null)
                                                                            {{ $detailss->price_without_tax }}
                                                                        @else
                                                                            {{ $detailss->price }}
                                                                        @endif
                                                                    </span></p>
                                                            </td>
                                                            <td>
                                                                <p><span style="color:grey">{{ $detailss->price }}</span>
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
        <td colspan="4" class="text-right"><strong class="pull-right">{{ __('keywords.Products_Price') }} :
            </strong>
        </td>
        <td class="text-right" colspan="1">
            <strong>{{ $ords->price_without_delivery + $ords->coupon_discount }}</strong>
        </td>
    </tr>
    <tr>
        <td colspan="4" class="text-right"><strong class="pull-right">{{ __('keywords.Delivery_Charge') }} :
            </strong></td>
        <td class="text-right" colspan="1">
            <strong>+{{ $ords->delivery_charge }}</strong>
        </td>
    </tr>
    @if ($ords->paid_by_wallet > 0)
        <tr>
            <td colspan="4" class="text-right"><strong class="pull-right">{{ __('keywords.Paid By Wallet') }} :
                </strong></td>
            <td class="text-right" colspan="1">
                <strong>-{{ $ords->paid_by_wallet }}</strong>
            </td>
        </tr>
    @endif
    @if ($ords->coupon_discount > 0)
        <tr>
            <td colspan="4" class="text-right"><strong class="pull-right">{{ __('keywords.Coupon Discount') }}:
                </strong></td>
            <td class="text-right" colspan="1">
                <strong class="">-{{ $ords->coupon_discount }}</strong>
            </td>
        </tr>
    @endif
    <tr>
        <td colspan="4" class="text-right"><strong
                class="pull-right">{{ __('keywords.Net_Total(Payable)') }}:</strong></td>
        <td class="text-right" colspan="1">{{ $ords->total_price }}</td>
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
@endsection

@section('postload-section')
    {{-- You must include files that have no direct efect on the load of the page and can be loaded meanwhile other tasks can be performed by user --}}
    <script>
        $('#datatableDefault').DataTable({
            dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8 text-right'<'d-flex justify-content-end'fB>>>t<'d-flex align-items-center'<'mr-auto'i><'mb-0'p>>",
            responsive: true,
            lengthMenu: false,
            autoWidth: true,
            select: true,
            scrollX: true,
            processing: true,
            ordering: true,
            paging: false,
            buttons: [{
                    extend: 'print',
                    className: 'btn btn-default fa-print'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-default'
                }
            ]
        });
    </script>


    <!-- JavaScript for handling reset and quick date filter -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Reset button functionality
            document.getElementById('resetFilters').addEventListener('click', function() {
                // Clear all form inputs
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                document.getElementById('quickDateFilter').value = '';
                document.getElementById('statusFilter').value = '';

                // Redirect to base URL without parameters
                window.location.href = "{{ route('adminHome') }}";
            });

            // Quick date filter auto-fill dates
            document.getElementById('quickDateFilter').addEventListener('change', function() {
                const value = this.value;
                const today = new Date();
                let startDate = '';
                let endDate = '';

                switch (value) {
                    case 'today':
                        startDate = endDate = formatDate(today);
                        break;
                    case 'yesterday':
                        const yesterday = new Date(today);
                        yesterday.setDate(yesterday.getDate() - 1);
                        startDate = endDate = formatDate(yesterday);
                        break;
                    case 'last7days':
                        const last7 = new Date(today);
                        last7.setDate(last7.getDate() - 7);
                        startDate = formatDate(last7);
                        endDate = formatDate(today);
                        break;
                    case 'last30days':
                        const last30 = new Date(today);
                        last30.setDate(last30.getDate() - 30);
                        startDate = formatDate(last30);
                        endDate = formatDate(today);
                        break;
                    case 'thisMonth':
                        startDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                        endDate = formatDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));
                        break;
                    case 'lastMonth':
                        startDate = formatDate(new Date(today.getFullYear(), today.getMonth() - 1, 1));
                        endDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 0));
                        break;
                    case 'thisYear':
                        startDate = formatDate(new Date(today.getFullYear(), 0, 1));
                        endDate = formatDate(new Date(today.getFullYear(), 11, 31));
                        break;
                }

                // Auto-fill date inputs
                if (startDate && endDate) {
                    document.getElementById('startDate').value = startDate;
                    document.getElementById('endDate').value = endDate;
                }
            });

            // Format date to YYYY-MM-DD
            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // Disable manual date selection when quick filter is selected
            const quickFilter = document.getElementById('quickDateFilter');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');

            startDateInput.addEventListener('change', function() {
                if (this.value) {
                    quickFilter.value = '';
                }
            });

            endDateInput.addEventListener('change', function() {
                if (this.value) {
                    quickFilter.value = '';
                }
            });
        });
    </script>
@endsection
