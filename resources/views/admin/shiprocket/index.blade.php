@extends('admin.layout.app')

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
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Shiprocket Orders</h4>
            </div>

            <div class="card-body table-responsive">

                @if (empty($orders))
                    <div class="alert alert-info">No orders found.</div>
                @else
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>SR Order ID</th>
                                <th>Channel Order ID</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>AWB</th>
                                <th>Courier</th>
                                <th>Total</th>
                                <th>Order Date</th>
                                <th width="150">Actions</th>
                                <th width="150">Invoice</th>
                            </tr>
                        </thead>

                        <tbody>
                            {{-- @dd($orders) --}}
                            @foreach ($orders as $o)
                            {{-- @dd($o) --}}
                                @php
                                    $shipment = $o['shipments'][0] ?? null;
                                @endphp

                                <tr>
                                    {{-- SR Order ID --}}
                                    <td>{{ $o['id'] }}</td>

                                    {{-- Channel Order ID --}}
                                    <td>{{ $o['channel_order_id'] }}</td>

                                    {{-- Customer --}}
                                    <td>{{ $o['customer_name'] }}</td>

                                    {{-- Phone --}}
                                    <td>{{ $o['customer_phone'] }}</td>

                                    {{-- Payment Method --}}
                                    <td style="text-transform: uppercase;">
                                        {{ $o['payment_method'] }}
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <span
                                            class="badge 
                                    @if ($o['status'] == 'NEW') badge-info 
                                    @elseif($o['status'] == 'IN_TRANSIT') badge-warning
                                    @elseif($o['status'] == 'DELIVERED') badge-success
                                    @else badge-secondary @endif">
                                            {{ $o['status'] }}
                                        </span>
                                    </td>

                                    {{-- AWB --}}
                                    <td>{{ $shipment['awb'] ?? '—' }}</td>

                                    {{-- Courier --}}
                                    <td>{{ $shipment['courier_name'] ?? '—' }}</td>

                                    {{-- Total --}}
                                    <td>₹{{ $o['total'] }}</td>

                                    {{-- Order Date --}}
                                    <td>{{ $o['created_at'] }}</td>

                                    {{-- Actions --}}
                                    <td>


                                        @if ($o['status'] != 'CANCELED')
                                            {{-- {{ route('shiprocket.track', $o['id']) }} --}}
                                            <a href="{{ route('shiprocket.track', $o['shipments'][0]['awb']) }}"
                                                class="btn btn-sm btn-info mb-1">
                                                Track
                                            </a>
                                            @if ($o['local_order'])
                                                
                                            <a href="{{ route('changeOrderStatusCancelled', $o['local_order']->order_id) }}"
                                                class="btn btn-sm  btn-danger btn-info mb-1">
                                                Cancel
                                            </a>
                                            @endif
                                        @endif
                                    </td>

                                    <td>
                                        @if (!empty($shipment['label_url']))
                                            <a href="{{ $shipment['label_url'] }}" target="_blank"
                                                class="btn btn-sm btn-primary mb-1">
                                                Label
                                            </a>
                                        @endif

                                        @if (!empty($o['local_order']->shipment->invoice_url))
                                            <a href="{{ $o['local_order']->shipment->invoice_url }}" target="_blank"
                                                class="btn btn-sm btn-secondary mb-1">
                                                Invoice
                                            </a>
                                        @endif
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                    {{-- PAGINATION --}}
                    @if (!empty($meta))
                        <div class="d-flex justify-content-between mt-3">

                            <div>
                                Showing {{ $meta['count'] }} of {{ $meta['total'] }} orders
                                (Page {{ $meta['current_page'] }} of {{ $meta['total_pages'] }})
                            </div>

                            <div>
                                {{-- Previous Page --}}
                                @if ($meta['current_page'] > 1)
                                    <a class="btn btn-outline-primary" href="?page={{ $meta['current_page'] - 1 }}">
                                        Previous
                                    </a>
                                @endif

                                {{-- Next Page --}}
                                @if ($meta['current_page'] < $meta['total_pages'])
                                    <a class="btn btn-outline-primary" href="?page={{ $meta['current_page'] + 1 }}">
                                        Next
                                    </a>
                                @endif
                            </div>

                        </div>
                    @endif


                @endif

            </div>
        </div>

    </div>

@endsection
