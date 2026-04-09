<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-2"></i>{{ session()->get('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session()->get('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Create Shipping Order - Shiprocket</h4>
                            <small>Order #{{ $order->cart_id ?? $order->order_id }}</small>
                        </div>
                        @if ($order->shipment?->status == 'success')
                            <span class="badge badge-success">Order Created</span>
                            {{-- <span class="badge badge-success">Shipment Created</span> --}}
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if ($order->shipment?->status == 'success')
                        <div class="alert alert-info">
                            Shipment created for this order. Waybill: {{ $order->shipment->waybill ?? 'N/A' }}
                        </div>
                    @endif

                    <form class="forms-sample" id="confirmOrderForm"
                        action="{{ route('ajax.order.confirm', $order->order_id) }}" method="POST"
                        data-order-id="{{ $order->order_id }}">
                        @csrf

                        {{-- Basic order info
                            <div class="row">
                                <div class="col-md-5 form-group">
                                    <label>Order No</label>
                                    <input readonly type="text" name="order"
                                        class="form-control @error('order') is-invalid @enderror"
                                        value="{{ old('order', $order->cart_id ?? ($order->order_id ?? '')) }}" required>
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5 form-group">
                                    <label>Order Date</label>
                                    <input readonly type="text" name="order_date"
                                        class="form-control @error('order_date') is-invalid @enderror"
                                        value="{{ old('order_date', optional($order->order_date)->format('d-m-Y') ?? now()->format('d-m-Y')) }}"
                                        placeholder="DD-MM-YYYY" required>
                                    @error('order_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}

                        {{-- Shipping / Billing Details --}}
                        {{-- <h5>Shipping / Billing Details</h5>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>Name</label>
                                    <input readonly type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $shipping_address['name'] ?? '') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Phone</label>
                                    <input readonly type="text" name="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $shipping_address['phone'] ?? '') }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}

                        {{-- <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Address</label>
                                    <input readonly type="text" name="add"
                                        class="form-control @error('add') is-invalid @enderror"
                                        value="{{ old('add', trim($shipping_address['house_no'] ?? '')) }}">
                                    @error('add')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Address 2</label>
                                    <input readonly type="text" name="add2"
                                        class="form-control @error('add2') is-invalid @enderror"
                                        value="{{ old('add2', trim($shipping_address['society'] ?? '')) }}">
                                    @error('add2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}

                        {{-- <div class="row">
                                <div class="col-md-3 form-group">
                                    <label>City</label>
                                    <input readonly type="text" name="city" class="form-control"
                                        value="{{ old('city', $shipping_address['city'] ?? '') }}">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>State</label>
                                    <input readonly type="text" name="state" class="form-control"
                                        value="{{ old('state', $shipping_address['state'] ?? '') }}">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Pin</label>
                                    <input readonly type="text" name="pin" class="form-control"
                                        value="{{ old('pin', $shipping_address['pincode'] ?? '') }}">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Country</label>
                                    <input readonly type="text" name="country" class="form-control"
                                        value="{{ old('country', $shipping_address['country'] ?? 'India') }}">
                                </div>
                            </div> --}}

                        {{-- Products: dynamic rows --}}
                        {{-- <h5>Products</h5>
                            <table class="table" id="products-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>SKU</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Tax Rate</th>
                                        <th>Discount</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $rows = old('products', $products ?? []);
                                    @endphp

                                    @if (count($rows) == 0)
                                        @php $rows = [['product_name'=>'','product_sku'=>'','product_quantity'=>1,'product_price'=>'','product_tax_rate'=>0,'product_hsn_code'=>'','product_discount'=>0]]; @endphp
                                    @endif

                                    @foreach ($rows as $i => $p)
                                        <tr class="product-row">
                                            <td>
                                                <input readonly type="text"
                                                    name="products[{{ $i }}][product_name]"
                                                    class="form-control @error("products.$i.product_name") is-invalid @enderror"
                                                    value="{{ old("products.$i.product_name", $p['product_name'] ?? '') }}">
                                                @error("products.$i.product_name")
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            <td>
                                                <input readonly type="text"
                                                    name="products[{{ $i }}][product_sku]"
                                                    class="form-control @error("products.$i.product_sku") is-invalid @enderror"
                                                    value="{{ old("products.$i.product_sku", $p['product_sku'] ?? '') }}">
                                                @error("products.$i.product_sku")
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            <td>
                                                <input readonly type="number" min="1"
                                                    name="products[{{ $i }}][product_quantity]"
                                                    class="form-control @error("products.$i.product_quantity") is-invalid @enderror"
                                                    value="{{ old("products.$i.product_quantity", $p['product_quantity'] ?? 1) }}">
                                                @error("products.$i.product_quantity")
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            <td>
                                                <input readonly type="text"
                                                    name="products[{{ $i }}][product_price]"
                                                    class="form-control @error("products.$i.product_price") is-invalid @enderror"
                                                    value="{{ old("products.$i.product_price", $p['product_price'] ?? '') }}">
                                                @error("products.$i.product_price")
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            <td>
                                                <input readonly type="text"
                                                    name="products[{{ $i }}][product_tax_rate]"
                                                    class="form-control @error("products.$i.product_tax_rate") is-invalid @enderror"
                                                    value="{{ old("products.$i.product_tax_rate", $p['product_tax_rate'] ?? 0) }}">
                                                @error("products.$i.product_tax_rate")
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            <td>
                                                <input readonly type="text"
                                                    name="products[{{ $i }}][product_discount]"
                                                    class="form-control @error("products.$i.product_discount") is-invalid @enderror"
                                                    value="{{ old("products.$i.product_discount", $p['product_discount'] ?? 0) }}">
                                                @error("products.$i.product_discount")
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            @if ($errors->has('products'))
                                <div class="text-danger mb-3">
                                    @foreach ($errors->get('products') as $err)
                                        <div>{{ $err }}</div>
                                    @endforeach
                                </div>
                            @endif --}}

                        {{-- Shipment Dimensions & Weight --}}
                        <h5>Shipment Dimensions & Weight</h5>
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Length (cm) <span class="text-danger">*</span></label>
                                <input type="text" name="shipment_length" id="shipment_length"
                                    class="form-control dimension-input @error('shipment_length') is-invalid @enderror"
                                    value="{{ $order->shipment->length ?? old('shipment_length', $dimensions['length'] ?? '') }}"
                                    required>
                                @error('shipment_length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label>Width (cm) <span class="text-danger">*</span></label>
                                <input type="text" name="shipment_width" id="shipment_width"
                                    class="form-control dimension-input @error('shipment_width') is-invalid @enderror"
                                    value="{{ $order->shipment->width ?? old('shipment_width', $dimensions['width'] ?? '') }}"
                                    required>
                                @error('shipment_width')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label>Height (cm) <span class="text-danger">*</span></label>
                                <input type="text" name="shipment_height" id="shipment_height"
                                    class="form-control dimension-input @error('shipment_height') is-invalid @enderror"
                                    value="{{ $order->shipment->height ?? old('shipment_height', $dimensions['height'] ?? '') }}"
                                    required>
                                @error('shipment_height')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label>Weight (kg) <span class="text-danger">*</span></label>
                                <input type="text" name="weight" id="weight"
                                    class="form-control dimension-input @error('weight') is-invalid @enderror"
                                    value="{{ $order->shipment->weight ?? old('weight', $dimensions['weight'] ?? '') }}"
                                    required>
                                @error('weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                {{-- <button type="button" class="btn btn-info btn-sm" id="fetch-rates-btn">
                                        <i class="fas fa-sync-alt mr-1"></i> Fetch Delivery Rates
                                    </button> --}}
                                <button type="button" class="btn btn-info btn-sm" id="fetch-rates-btn"
                                    data-order-id="{{ $order->order_id }}">
                                    <i class="fas fa-sync-alt mr-1"></i> Fetch Delivery Rates
                                </button>
                                <small class="text-muted ml-2">Fill all dimensions and click to get delivery partner
                                    rates</small>
                            </div>
                        </div>

                        {{-- Delivery Agent Selection --}}
                        <div id="delivery-agents-section" class="mt-4">
                            @if (!empty($deliveryAgents) && count($deliveryAgents) > 0)
                                <h5>Select Delivery Partner <span class="badge badge-info">{{ count($deliveryAgents) }}
                                        Available</span></h5>
                                <div class="row" id="delivery-agents-container">
                                    @foreach ($deliveryAgents as $index => $agent)
                                        <div class="col-md-6 mb-3">
                                            <div
                                                class="card delivery-agent-card {{ $selectedCourierId == $agent['id'] ? 'selected' : '' }}">
                                                <div class="card-body">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="courier_{{ $agent['id'] }}"
                                                            name="courier_company_id" class="custom-control-input"
                                                            value="{{ $agent['id'] }}"
                                                            {{ $selectedCourierId == $agent['id'] || ($index == 0 && !$selectedCourierId) ? 'checked' : '' }}
                                                            data-rate="{{ $agent['total_charge'] }}" required>
                                                        <label class="custom-control-label w-100"
                                                            for="courier_{{ $agent['id'] }}">
                                                            <div class="d-flex justify-content-between">
                                                                <div>
                                                                    <h6 class="mb-1">
                                                                        <strong>{{ $agent['name'] }}</strong>
                                                                        @if ($agent['is_recommended'])
                                                                            <span
                                                                                class="badge badge-success badge-sm">Recommended</span>
                                                                        @endif
                                                                    </h6>
                                                                    <small class="text-muted d-block mb-2">
                                                                        ⭐ Rating:
                                                                        {{ number_format($agent['rating'], 1) }}/5
                                                                        | ETA: {{ $agent['estimated_days'] }}
                                                                    </small>
                                                                    <div class="small">
                                                                        <div>Freight:
                                                                            ₹{{ number_format($agent['freight_charge'], 2) }}
                                                                        </div>
                                                                        @if ($agent['cod_charge'] > 0)
                                                                            <div>COD Charge:
                                                                                ₹{{ number_format($agent['cod_charge'], 2) }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="text-right">
                                                                    <h4 class="text-primary mb-0">
                                                                        ₹{{ number_format($agent['total_charge'], 2) }}
                                                                    </h4>
                                                                    <small class="text-muted">Total</small>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @error('courier_company_id')
                                    <div class="text-danger mb-3">{{ $message }}</div>
                                @enderror
                            @else
                                <div class="alert alert-warning" id="no-agents-alert">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Please fill in all shipment dimensions and weight, then click "Fetch Delivery Rates"
                                    to view available delivery partners.
                                </div>
                                <div id="loading-agents" style="display: none;">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <p class="mt-2">Fetching delivery rates...</p>
                                    </div>
                                </div>
                                <div id="delivery-agents-container"></div>
                            @endif
                        </div>

                        {{-- Payment / misc fields --}}
                        <h5>Payment Details</h5>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Total Amount</label>
                                <input readonly type="text" name="total_amount" class="form-control"
                                    value="{{ old('total_amount', $totals['grand_total'] ?? '') }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Payment Mode</label>
                                <select readonly name="payment_mode" class="form-control">
                                    <option value="COD" {{ $totals['payment_method'] == 'COD' ? 'selected' : '' }}>
                                        COD</option>
                                    <option value="Prepaid"
                                        {{ $totals['payment_method'] == 'ONLINE' ? 'selected' : '' }}>
                                        Prepaid</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                @if ($totals['payment_method'] == 'COD')
                                    <label>COD Amount (if COD)</label>
                                    <input readonly type="text" name="cod_amount" class="form-control"
                                        value="{{ old('cod_amount', $totals['cod_amount'] ?? 0) }}">
                                @else
                                    <label>Prepaid Amount (if Prepaid)</label>
                                    <input readonly type="text" name="advance_amount" class="form-control"
                                        value="{{ old('advance_amount', $totals['advance_amount'] ?? 0) }}">
                                @endif
                            </div>
                        </div>

                        <div class="mt-3">
                            @if ($order->shipment?->status == 'success')
                                <div class="alert alert-info">Shipment created for this order. awb:
                                    {{ $order->shipment->awb ?? 'N/A' }}</div>
                            @else
                                <button type="submit" class="btn btn-primary" id="confirmOrderBtn">
                                    <span class="btn-text">Confirm & Create Shipping Order</span>
                                    <span class="btn-loading d-none">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Processing...
                                    </span>
                                </button>
                            @endif
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .delivery-agent-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid #e3e6f0;
    }

    .delivery-agent-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .delivery-agent-card.selected {
        border-color: #4e73df;
        background-color: #f8f9fc;
        box-shadow: 0 0.5rem 1rem rgba(78, 115, 223, 0.15);
    }

    .delivery-agent-card .custom-control-label {
        cursor: pointer;
    }
</style>

{{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const courierRadios = document.querySelectorAll('input[name="courier_company_id"]');

            courierRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.delivery-agent-card').forEach(card => {
                        card.classList.remove('selected');
                    });

                    const selectedCard = this.closest('.delivery-agent-card');
                    if (selectedCard) {
                        selectedCard.classList.add('selected');
                    }
                });
            });

            // Fetch delivery rates functionality
            const fetchRatesBtn = document.getElementById('fetch-rates-btn');
            const dimensionInputs = document.querySelectorAll('.dimension-input');
            const orderId = '{{ $order->order_id }}';

            // Enable/disable fetch button based on inputs
            function checkDimensions() {
                let allFilled = true;
                dimensionInputs.forEach(input => {
                    if (!input.value || input.value.trim() === '') {
                        allFilled = false;
                    }
                });
                if (fetchRatesBtn) {
                    fetchRatesBtn.disabled = !allFilled;
                }
            }

            dimensionInputs.forEach(input => {
                input.addEventListener('input', checkDimensions);
            });

            checkDimensions(); // Initial check

            if (fetchRatesBtn) {
                fetchRatesBtn.addEventListener('click', function() {
                    const length = document.getElementById('shipment_length').value;
                    const width = document.getElementById('shipment_width').value;
                    const height = document.getElementById('shipment_height').value;
                    const weight = document.getElementById('weight').value;

                    if (!length || !width || !height || !weight) {
                        alert('Please fill all dimension fields');
                        return;
                    }

                    // Show loading
                    const noAgentsAlert = document.getElementById('no-agents-alert');
                    const loadingAgents = document.getElementById('loading-agents');
                    const agentsContainer = document.getElementById('delivery-agents-container');

                    if (noAgentsAlert) noAgentsAlert.style.display = 'none';
                    if (loadingAgents) loadingAgents.style.display = 'block';
                    if (agentsContainer) agentsContainer.innerHTML = '';

                    fetchRatesBtn.disabled = true;
                    fetchRatesBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Fetching...';

                    // Fetch rates via AJAX
                    fetch(`{{ route('admin.orders.fetch-delivery-rates', ['order' => $order->order_id]) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                length: length,
                                width: width,
                                height: height,
                                weight: weight
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (loadingAgents) loadingAgents.style.display = 'none';
                            fetchRatesBtn.disabled = false;
                            fetchRatesBtn.innerHTML =
                                '<i class="fas fa-sync-alt mr-1"></i> Fetch Delivery Rates';

                            if (data.success && data.agents && data.agents.length > 0) {
                                displayDeliveryAgents(data.agents);
                            } else {
                                if (agentsContainer) {
                                    agentsContainer.innerHTML = `
                                    <div class="alert alert-warning col-12">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        ${data.message || 'No delivery agents available for this location and weight.'}
                                    </div>
                                `;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            if (loadingAgents) loadingAgents.style.display = 'none';
                            fetchRatesBtn.disabled = false;
                            fetchRatesBtn.innerHTML =
                                '<i class="fas fa-sync-alt mr-1"></i> Fetch Delivery Rates';

                            let errMsg = error?.message || 'Failed to fetch delivery rates. Please try again.';
                            if (agentsContainer) {
                                agentsContainer.innerHTML = `
                                <div class="alert alert-danger col-12">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    ${errMsg}
                                </div>
                            `;
                            }
                        });
                });
            }

            function displayDeliveryAgents(agents) {
                const section = document.getElementById('delivery-agents-section');
                const container = document.getElementById('delivery-agents-container');

                if (!container) return;

                let html = '<div class="row">';

                agents.forEach((agent, index) => {
                    const isRecommended = agent.is_recommended ? '<span class="badge badge-success badge-sm">Recommended</span>' : '';
                    const codCharge = agent.cod_charge > 0 ?
                        `<div>COD Charge: ₹${Number(agent.cod_charge).toFixed(2)}</div>` : '';

                    html += `
                    <div class="col-md-6 mb-3">
                        <div class="card delivery-agent-card ${index === 0 ? 'selected' : ''}">
                            <div class="card-body">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="courier_${agent.id}" 
                                        name="courier_company_id" 
                                        class="custom-control-input courier-radio" 
                                        value="${agent.id}" 
                                        ${index === 0 ? 'checked' : ''}
                                        data-rate="${agent.total_charge}" 
                                        required>
                                    <label class="custom-control-label w-100" for="courier_${agent.id}">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1">
                                                    <strong>${agent.name}</strong>
                                                    ${isRecommended}
                                                </h6>
                                                <small class="text-muted d-block mb-2">
                                                    ⭐ Rating: ${Number(agent.rating).toFixed(1)}/5 | 
                                                    ETA: ${agent.estimated_days}
                                                </small>
                                                <div class="small">
                                                    <div>Freight: ₹${Number(agent.freight_charge).toFixed(2)}</div>
                                                    ${codCharge}
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <h4 class="text-primary mb-0">₹${Number(agent.total_charge).toFixed(2)}</h4>
                                                <small class="text-muted">Total</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                });

                html += '</div>';

                container.innerHTML = html;

                // Update section title
                const titleHtml = `
                <h5 class="mt-4">Select Delivery Partner 
                    <span class="badge badge-info">${agents.length} Available</span>
                </h5>
            `;

                // Insert title before container
                const existingTitle = section.querySelector('h5');
                if (existingTitle) {
                    existingTitle.remove();
                }
                container.insertAdjacentHTML('beforebegin', titleHtml);

                // Re-attach event listeners to new radio buttons
                const newRadios = document.querySelectorAll('.courier-radio');
                newRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        document.querySelectorAll('.delivery-agent-card').forEach(card => {
                            card.classList.remove('selected');
                        });
                        const selectedCard = this.closest('.delivery-agent-card');
                        if (selectedCard) {
                            selectedCard.classList.add('selected');
                        }
                    });
                });
            }
        });
    </script> --}}

<script>
    // Enable/disable fetch button based on inputs (works inside modal too)
    $(document).on('input', '.dimension-input', function() {
        let allFilled = true;
        $('.dimension-input').each(function() {
            if (!$(this).val().trim()) {
                allFilled = false;
            }
        });
        $('#fetch-rates-btn').prop('disabled', !allFilled);
    });

    // Fetch delivery rates click handler
    $(document).on('click', '#fetch-rates-btn', function() {
        const length = $('#shipment_length').val();
        const width = $('#shipment_width').val();
        const height = $('#shipment_height').val();
        const weight = $('#weight').val();
        const orderId = $(this).data('order-id') || window.currentOrderId;

        if (!length || !width || !height || !weight) {
            alert('Please fill all dimension fields');
            return;
        }

        const noAgentsAlert = $('#no-agents-alert');
        const loadingAgents = $('#loading-agents');
        const agentsContainer = $('#delivery-agents-container');

        noAgentsAlert.hide();
        loadingAgents.show();
        agentsContainer.html('');

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Fetching...');

        $.ajax({
            url: "{{ route('admin.orders.fetch-delivery-rates', ['order' => '__ORDER__']) }}".replace(
                '__ORDER__', orderId),
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                length: length,
                width: width,
                height: height,
                weight: weight
            }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                loadingAgents.hide();
                $('#fetch-rates-btn').prop('disabled', false)
                    .html('<i class="fas fa-sync-alt mr-1"></i> Fetch Delivery Rates');

                if (data.success && data.agents && data.agents.length > 0) {
                    displayDeliveryAgents(data.agents);
                } else {
                    agentsContainer.html(`
                        <div class="alert alert-warning col-12">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            ${data.message || 'No delivery agents available.'}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                loadingAgents.hide();
                $('#fetch-rates-btn').prop('disabled', false)
                    .html('<i class="fas fa-sync-alt mr-1"></i> Fetch Delivery Rates');

                let msg = 'Failed to fetch delivery rates.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                agentsContainer.html(`
                    <div class="alert alert-danger col-12">
                        <i class="fas fa-times-circle mr-2"></i> ${msg}
                    </div>
                `);
            }
        });
    });

    // Delivery agent UI render function
    function displayDeliveryAgents(agents) {
        const section = $('#delivery-agents-section');
        const container = $('#delivery-agents-container');

        let html = '<div class="row">';

        agents.forEach((agent, index) => {
            const isRecommended = agent.is_recommended ?
                '<span class="badge badge-success badge-sm">Recommended</span>' : '';
            const codCharge = agent.cod_charge > 0 ?
                `<div>COD Charge: ₹${Number(agent.cod_charge).toFixed(2)}</div>` : '';

            html += `
                <div class="col-md-6 mb-3">
                    <div class="card delivery-agent-card ${index === 0 ? 'selected' : ''}">
                        <div class="card-body">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="courier_${agent.id}" 
                                    name="courier_company_id" 
                                    class="custom-control-input courier-radio" 
                                    value="${agent.id}" 
                                    ${index === 0 ? 'checked' : ''}
                                    data-rate="${agent.total_charge}" 
                                    required>
                                <label class="custom-control-label w-100" for="courier_${agent.id}">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1">
                                                <strong>${agent.name}</strong>
                                                ${isRecommended}
                                            </h6>
                                            <small class="text-muted d-block mb-2">
                                                ⭐ Rating: ${Number(agent.rating).toFixed(1)}/5 | 
                                                ETA: ${agent.estimated_days}
                                            </small>
                                            <div class="small">
                                                <div>Freight: ₹${Number(agent.freight_charge).toFixed(2)}</div>
                                                ${codCharge}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <h4 class="text-primary mb-0">₹${Number(agent.total_charge).toFixed(2)}</h4>
                                            <small class="text-muted">Total</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.html(html);

        // Update section title
        const titleHtml = `
            <h5 class="mt-4">Select Delivery Partner 
                <span class="badge badge-info">${agents.length} Available</span>
            </h5>
        `;

        section.find('h5').remove();
        container.before(titleHtml);

        // Radio card selection effect
        $(document).on('change', '.courier-radio', function() {
            $('.delivery-agent-card').removeClass('selected');
            $(this).closest('.delivery-agent-card').addClass('selected');
        });
    }

    // AJAX Form Submission for Confirm Order
    $(document).on('submit', '#confirmOrderForm', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#confirmOrderBtn');
        const orderId = form.data('order-id');

        // Show loading state
        submitBtn.prop('disabled', true);
        submitBtn.find('.btn-text').addClass('d-none');
        submitBtn.find('.btn-loading').removeClass('d-none');


        function extractShiprocketError(message) {

            // If already a string
            if (typeof message === 'string') {
                return message;
            }

            // Common Shiprocket error structures
            if (message?.response?.data?.awb_assign_error) {
                return message.response.data.awb_assign_error;
            }

            if (message?.response?.data?.message) {
                return message.response.data.message;
            }

            if (message?.response?.message) {
                return message.response.message;
            }

            // Shiprocket top-level message
            if (message?.message) {
                return message.message;
            }

            // Shiprocket errors array
            if (message?.errors) {
                if (typeof message.errors === 'string') return message.errors;
                if (Array.isArray(message.errors)) return message.errors.join(', ');
                // Laravel-style validation errors {field: [errors]}
                if (typeof message.errors === 'object') {
                    return Object.values(message.errors).flat().join(', ');
                }
            }

            // Razorpay error structure
            if (message?.error?.description) {
                return message.error.description;
            }

            // status_code + message pattern (Shiprocket pickup errors etc.)
            if (message?.status_code && message?.message) {
                return message.message;
            }

            // Fallback: stringify safely
            try {
                return JSON.stringify(message, null, 2);
            } catch (e) {
                return 'Unknown error';
            }
        }


        function formatShiprocketMessage(data) {

            if (typeof data === 'string') {
                return `<p class="mb-0">${data}</p>`;
            }

            if (Array.isArray(data)) {
                return `
            <ul class="mb-0 pl-3">
                ${data.map(item => `<li>${item}</li>`).join('')}
            </ul>
        `;
            }

            if (typeof data === 'object' && data !== null) {
                let html = '<ul class="mb-0 pl-3">';
                Object.entries(data).forEach(([key, value]) => {
                    if (Array.isArray(value)) {
                        value.forEach(v => {
                            html += `<li><strong>${key}:</strong> ${v}</li>`;
                        });
                    } else {
                        html += `<li><strong>${key}:</strong> ${value}</li>`;
                    }
                });
                html += '</ul>';
                return html;
            }

            return `<p class="mb-0">Something went wrong</p>`;
        }


        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Close modal
                    $('#confirmOrderModal').modal('hide');

                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Order Confirmed',
                            // title: 'Shipment Created',
                            html: formatShiprocketMessage(response.message),
                            icon: 'success',
                            width: 600,
                            showConfirmButton: true
                        });
                    }

                    // Reload datatable if exists
                    if (typeof table !== 'undefined' && table.ajax) {
                        table.ajax.reload(null, false);
                    } else if ($('#datatableDefault').length) {
                        $('#datatableDefault').DataTable().ajax.reload(null, false);
                    }
                } else {
                    console.log('Error response:', response);
                    // Show error in modal
                    const errorMsg = extractShiprocketError(response.message || 'Failed to confirm order');
                    form.find('.alert').remove();
                    form.prepend(`
                        <div class="alert alert-danger alert-dismissible fade show">
                            <strong><i class="fas fa-exclamation-circle mr-2"></i>Order Confirmation Failed</strong><br>
                            ${formatShiprocketMessage(errorMsg)}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    `);
                }
            },
            error: function(xhr) {

                let rawMessage = 'Order Confirmation failed.';

                if (xhr.responseJSON?.message) {
                    rawMessage = extractShiprocketError(xhr.responseJSON.message);
                }

                form.find('.alert').remove();
                form.prepend(`
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Order Confirmation Failed</strong><br>
            ${formatShiprocketMessage(rawMessage)}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    `);
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false);
                submitBtn.find('.btn-text').removeClass('d-none');
                submitBtn.find('.btn-loading').addClass('d-none');
            }
        });
    });
</script>
