<div class="mobile-money-payment-container">
    <div class="text-center">
        <div class="mobile-money-logo mb-4">
            <img src="{{ static_asset('assets/img/cards/payhere.png') }}" alt="Mobile Money" class="img-fluid" style="max-height: 80px;">
        </div>
        <h3>{{ translate('Mobile Money Payment') }}</h3>
        <p class="text-muted">{{ translate('Complete your payment using Mobile Money') }}</p>
        <div class="payment-info mb-4">
            <div class="row">
                <div class="col-6">
                    <strong>{{ translate('Amount:') }}</strong><br>
                    <span class="text-success h5">GHS {{ number_format($amount, 2) }}</span>
                </div>
                <div class="col-6">
                    <strong>{{ translate('Order:') }}</strong><br>
                    <span class="text-muted">#{{ $combined_order_id }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="payment-form-container">
        <form id="mobile-money-checkout-form" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $combined_order_id }}">
            <input type="hidden" name="amount" value="{{ $amount }}">
            <input type="hidden" name="first_name" value="{{ $first_name }}">
            <input type="hidden" name="last_name" value="{{ $last_name }}">
            <input type="hidden" name="phone" value="{{ $phone }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="address" value="{{ $address }}">
            <input type="hidden" name="city" value="{{ $city }}">

            <div class="form-group">
                <label for="wallet_type">{{ translate('Select Mobile Money Provider') }}</label>
                <select class="form-control" id="wallet_type" name="wallet_type" required>
                    <option value="">{{ translate('Choose Provider') }}</option>
                    <option value="mtn">MTN Mobile Money</option>
                    <option value="vodafone">Vodafone Cash</option>
                    <option value="airteltigo">AirtelTigo Money</option>
                </select>
            </div>

            <div class="form-group">
                <label for="wallet_number">{{ translate('Mobile Money Number') }}</label>
                <input type="tel" class="form-control" id="wallet_number" name="wallet_number" 
                       placeholder="e.g., 233244000000" required>
                <small class="form-text text-muted">{{ translate('Enter your mobile money number (e.g., 233244000000)') }}</small>
            </div>

            <div class="form-group">
                <label for="wallet_name">{{ translate('Account Name') }} <small class="text-muted">(Optional)</small></label>
                <input type="text" class="form-control" id="wallet_name" name="wallet_name" 
                       placeholder="{{ translate('Account holder name (optional)') }}">
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="check-name-btn">
                    {{ translate('Check Account Name') }}
                </button>
                <small class="form-text text-muted">{{ translate('You can leave this empty and proceed with payment') }}</small>
            </div>

            <div class="form-group">
                <label for="amount_display">{{ translate('Amount') }}</label>
                <input type="text" class="form-control" id="amount_display" value="GHS {{ number_format($amount, 2) }}" readonly>
            </div>

            <div class="form-group">
                <label for="remarks">{{ translate('Payment Remarks') }}</label>
                <input type="text" class="form-control" id="remarks" name="remarks" 
                       value="Payment for Order #{{ $combined_order_id }}" readonly>
            </div>

            <div class="payment-actions">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                    {{ translate('Cancel') }}
                </button>
                <button type="submit" class="btn btn-primary" id="pay-btn">
                    {{ translate('Pay Now') }}
                </button>
            </div>
        </form>
    </div>

    <div id="loading-spinner" class="text-center" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">{{ translate('Processing...') }}</span>
        </div>
        <p>{{ translate('Processing your payment...') }}</p>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('mobile-money-checkout-form');
    const walletTypeSelect = document.getElementById('wallet_type');
    const walletNumberInput = document.getElementById('wallet_number');
    const walletNameInput = document.getElementById('wallet_name');
    const checkNameBtn = document.getElementById('check-name-btn');
    const payBtn = document.getElementById('pay-btn');
    const loadingSpinner = document.getElementById('loading-spinner');

    // Format wallet number as user types
    walletNumberInput.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value.length > 0 && !value.startsWith('233')) {
            value = '233' + value;
        }
        this.value = value;
    });

    // Check account name
    checkNameBtn.addEventListener('click', function() {
        const walletType = walletTypeSelect.value;
        const walletNumber = walletNumberInput.value;

        if (!walletType || !walletNumber) {
            alert('{{ translate("Please select wallet type and enter wallet number") }}');
            return;
        }

        if (walletNumber.length < 12) {
            alert('{{ translate("Please enter a valid mobile money number") }}');
            return;
        }

        checkNameBtn.disabled = true;
        checkNameBtn.textContent = '{{ translate("Checking...") }}';

        fetch('{{ route("mobile_money.wallet_name_enquiry") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                wallet_type: walletType,
                wallet_number: walletNumber
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                walletNameInput.value = data.account_name;
                walletNameInput.classList.remove('is-invalid');
                walletNameInput.classList.add('is-valid');
            } else {
                walletNameInput.value = '';
                walletNameInput.classList.remove('is-valid');
                walletNameInput.classList.add('is-invalid');
                alert('{{ translate("Failed to retrieve account name: ") }}' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ translate("An error occurred while checking account name") }}');
        })
        .finally(() => {
            checkNameBtn.disabled = false;
            checkNameBtn.textContent = '{{ translate("Check Account Name") }}';
        });
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const walletType = walletTypeSelect.value;
        const walletNumber = walletNumberInput.value;
        const walletName = walletNameInput.value || ''; // Allow empty wallet name

        if (!walletType || !walletNumber) {
            alert('{{ translate("Please select wallet type and enter wallet number") }}');
            return;
        }

        // Show loading spinner
        form.style.display = 'none';
        loadingSpinner.style.display = 'block';

        // Process payment
        fetch('{{ route("mobile_money.debit_wallet") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                wallet_type: walletType,
                wallet_name: walletName,
                wallet_number: walletNumber,
                amount: {{ $amount }},
                transaction_id: 'MM{{ $combined_order_id }}{{ time() }}',
                remarks: 'Payment for Order #{{ $combined_order_id }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('{{ translate("Payment processed successfully! Redirecting...") }}', 'success');
                setTimeout(function() {
                    window.location.href = '{{ route("mobile_money.return") }}';
                }, 2000);
            } else {
                showMessage('{{ translate("Payment failed: ") }}' + data.message, 'error');
                form.style.display = 'block';
                loadingSpinner.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('{{ translate("An error occurred while processing payment") }}', 'error');
            form.style.display = 'block';
            loadingSpinner.style.display = 'none';
        });
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
        var container = document.querySelector('.mobile-money-payment-container');
        container.insertAdjacentHTML('afterbegin', messageHtml);
    }
});
</script>

<style>
.mobile-money-payment-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.mobile-money-logo {
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

.payment-form-container {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 10px;
    margin: 20px 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.form-control {
    border-radius: 5px;
    border: 1px solid #ddd;
    padding: 10px;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.payment-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
}

.btn {
    padding: 10px 20px;
    border-radius: 5px;
    font-weight: 600;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-primary {
    color: #007bff;
    border-color: #007bff;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

.is-valid {
    border-color: #28a745;
}

.is-invalid {
    border-color: #dc3545;
}

h3 {
    color: #2c3e50;
    margin-bottom: 10px;
}

p {
    color: #7f8c8d;
    margin-bottom: 20px;
}
</style>
