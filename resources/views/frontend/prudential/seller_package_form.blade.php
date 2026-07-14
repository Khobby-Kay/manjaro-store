<div class="prudential-payment-container">
    <div class="text-center">
        <h3>{{ translate('Redirecting to Prudential Bank') }}</h3>
        <p>{{ translate('Please wait while we process your seller package payment...') }}</p>
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">{{ translate('Loading...') }}</span>
        </div>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Create order via AJAX first
            fetch('{{ route("prudential.create_order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    order_data: @json($order_data),
                    order_id: '{{ $order_id }}',
                    amount: '{{ $amount }}',
                    first_name: '{{ $first_name }}',
                    last_name: '{{ $last_name }}',
                    phone: '{{ $phone }}',
                    email: '{{ $email }}',
                    address: '{{ $address }}',
                    city: '{{ $city }}',
                    user_id: '{{ $user_id }}',
                    package_id: '{{ $package_id }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    // Redirect to Prudential Bank HPP
                    window.location.href = data.redirect_url;
                } else {
                    // Show error and redirect back
                    alert('{{ translate("Payment initialization failed. Please try again.") }}');
                    window.location.href = '{{ route("dashboard") }}';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ translate("Payment initialization failed. Please try again.") }}');
                window.location.href = '{{ route("dashboard") }}';
            });
        });
    </script>
</div>

<style>
.prudential-payment-container {
    padding: 40px 20px;
    text-align: center;
    max-width: 500px;
    margin: 0 auto;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    margin-top: 20px;
}

h3 {
    color: #2c3e50;
    margin-bottom: 15px;
}

p {
    color: #7f8c8d;
    margin-bottom: 20px;
}
</style>
