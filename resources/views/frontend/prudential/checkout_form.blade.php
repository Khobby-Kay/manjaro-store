<div class="prudential-payment-container">
    <div class="text-center">
        <div class="bank-logo mb-4">
            <img src="{{ static_asset('assets/img/cards/payhere.png') }}" alt="Prudential Bank" class="img-fluid" style="max-height: 80px;">
        </div>
        <h3>{{ translate('Secure Payment with Prudential Bank') }}</h3>
        <p class="text-muted">{{ translate('You will be redirected to Prudential Bank\'s secure payment page') }}</p>
        <div class="payment-info mb-4">
            <div class="row">
                <div class="col-6">
                    <strong>{{ translate('Amount:') }}</strong><br>
                    <span class="text-primary h5">GHS {{ number_format($amount, 2) }}</span>
                </div>
                <div class="col-6">
                    <strong>{{ translate('Order:') }}</strong><br>
                    <span class="text-muted">#{{ $combined_order_id }}</span>
                </div>
            </div>
        </div>
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">{{ translate('Processing...') }}</span>
        </div>
        <p class="mt-3 text-muted">{{ translate('Please wait while we process your payment...') }}</p>
    </div>

    <form style="display: none" method="POST" action="{{ \App\Utility\PrudentialUtility::get_action_url() }}" id="prudential-checkout-form">
        <input type="hidden" name="order_data" value="{{ json_encode($order_data) }}">
    </form>

    <script type="text/javascript">
        // Cache busting: {{ time() }}
        document.addEventListener('DOMContentLoaded', function() {
            // Create order via AJAX first
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("prudential.create_order") }}', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.success && data.redirect_url) {
                                // Show success message before redirect
                                showMessage('{{ translate("Payment initialized successfully. Redirecting to Prudential Bank...") }}', 'success');
                                setTimeout(function() {
                                    window.location.href = data.redirect_url;
                                }, 1500);
                            } else {
                                // Show specific error message
                                var errorMsg = data.error || '{{ translate("Payment initialization failed. Please try again.") }}';
                                showMessage(errorMsg, 'error');
                                setTimeout(function() {
                                    window.location.href = '{{ route("home") }}';
                                }, 3000);
                            }
                        } catch (e) {
                            console.error('JSON Parse Error:', e);
                            showMessage('{{ translate("Payment initialization failed. Please try again.") }}', 'error');
                            setTimeout(function() {
                                window.location.href = '{{ route("home") }}';
                            }, 3000);
                        }
                    } else {
                        console.error('HTTP Error:', xhr.status, xhr.statusText);
                        showMessage('{{ translate("Payment service temporarily unavailable. Please try again later.") }}', 'error');
                        setTimeout(function() {
                            window.location.href = '{{ route("home") }}';
                        }, 3000);
                    }
                }
            };
            
            var requestData = {
                order_data: @json($order_data),
                order_id: '{{ $combined_order_id }}',
                amount: '{{ $amount }}',
                first_name: '{{ $first_name }}',
                last_name: '{{ $last_name }}',
                phone: '{{ $phone }}',
                email: '{{ $email }}',
                address: '{{ $address }}',
                city: '{{ $city }}'
            };
            
            xhr.send(JSON.stringify(requestData));
        });

        // Message display function
        function showMessage(message, type) {
            var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            var icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
            
            var messageHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                '<i class="' + icon + ' me-2"></i>' + message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
            
            // Remove existing messages
            document.querySelectorAll('.alert').forEach(function(alert) {
                alert.remove();
            });
            
            // Add new message
            var container = document.querySelector('.prudential-payment-container');
            container.insertAdjacentHTML('afterbegin', messageHtml);
        }
    </script>
</div>

<style>
.prudential-payment-container {
    padding: 40px 20px;
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.bank-logo {
    text-align: center;
}

.payment-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin: 20px 0;
}

.payment-info .row > div {
    padding: 10px;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    margin: 20px auto;
    color: #007bff;
}

h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 600;
}

p {
    color: #7f8c8d;
    margin-bottom: 20px;
}

.alert {
    margin-bottom: 20px;
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.2em;
    opacity: 0.7;
}

.btn-close:hover {
    opacity: 1;
}

@media (max-width: 768px) {
    .prudential-payment-container {
        padding: 20px 15px;
        margin: 10px;
    }
    
    .payment-info .row > div {
        padding: 5px;
        text-align: center;
    }
}
</style>
