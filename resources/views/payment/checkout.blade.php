<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var options = {
        "key": @json($razorpayKey),
        "amount": @json($order->total_price * 100), // in paise
        "currency": "INR",
        "name": "Your Store",
        "description": "Order {{ $order->cart_id }}",
        "order_id": @json($razorpayOrderId),
        "prefill": {
            "name": @json($order->user->name),
            "email": @json($order->user->email),
            "contact": @json($order->user->phone)
        },
        "handler": function(response){
            // POST to callback
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('payment.callback') }}";

            // CSRF
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfInput);

            // Add Razorpay fields
            ['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature'].forEach(function(key){
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = response[key];
                form.appendChild(input);
            });

            // Add our cart_id for mapping
            var cartInput = document.createElement('input');
            cartInput.type = 'hidden';
            cartInput.name = 'cart_id';
            cartInput.value = "{{ $order->cart_id }}";
            form.appendChild(cartInput);

            document.body.appendChild(form);
            form.submit();
        },
        "modal": {
            "ondismiss": function(){
                fetch("{{ route('customer.payment.cancel') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        cart_id: "{{ $order->cart_id }}"
                    })
                }).then(() => {
                    window.location.href = "{{ route('payment.error') }}";
                });
                // Redirect back to checkout with toast
                // window.location.href = "{{ route('payment.error', ['cart_id' => $order->cart_id]) }}";
            }
        }
    };

    var rzp1 = new Razorpay(options);
    rzp1.open();
});
</script>
</body>
</html>
