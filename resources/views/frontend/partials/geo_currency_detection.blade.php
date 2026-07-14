@if(get_setting('geo_currency_detection') == 1)
<div class="geo-currency-detection" id="geo-currency-detection" style="display: none;">
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="las la-globe mr-2"></i>
            <div class="flex-grow-1">
                <strong>{{ translate('Currency Detected') }}:</strong>
                <span id="detected-currency-text"></span>
                <small class="d-block text-muted" id="detected-location-text"></small>
            </div>
            <div class="ml-3">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="acceptDetectedCurrency()">
                    {{ translate('Use This Currency') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showCurrencySelector()">
                    {{ translate('Change') }}
                </button>
            </div>
        </div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>

<!-- Currency Selector Modal -->
<div class="modal fade" id="currency-selector-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('Select Currency') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ translate('Available Currencies') }}</label>
                    <select class="form-control aiz-selectpicker" id="currency-selector" data-live-search="true">
                        @foreach(\App\Models\Currency::where('status', 1)->get() as $currency)
                            <option value="{{ $currency->code }}" data-symbol="{{ $currency->symbol }}" data-rate="{{ $currency->exchange_rate }}">
                                {{ $currency->name }} ({{ $currency->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="changeCurrency()">{{ translate('Change Currency') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Check if user has already selected a currency
    if (!sessionStorage.getItem('currency_selected')) {
        detectUserCurrency();
    }
});

function detectUserCurrency() {
    $.ajax({
        url: '{{ route("geo.currency.detect") }}',
        type: 'GET',
        success: function(response) {
            if (response.success && response.currency) {
                showCurrencyDetection(response.currency, response.location);
            }
        },
        error: function() {
            console.log('Failed to detect currency');
        }
    });
}

function showCurrencyDetection(currency, location) {
    $('#detected-currency-text').text(currency.symbol + ' ' + currency.name + ' (' + currency.code + ')');
    $('#detected-location-text').text('Based on your location: ' + location.country_name);
    $('#geo-currency-detection').show();
}

function acceptDetectedCurrency() {
    $.ajax({
        url: '{{ route("geo.currency.set") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                sessionStorage.setItem('currency_selected', 'true');
                $('#geo-currency-detection').hide();
                location.reload(); // Reload to show prices in new currency
            }
        }
    });
}

function showCurrencySelector() {
    $('#currency-selector-modal').modal('show');
}

function changeCurrency() {
    var selectedCurrency = $('#currency-selector').val();
    var selectedOption = $('#currency-selector option:selected');
    
    $.ajax({
        url: '{{ route("currency.change") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            currency_code: selectedCurrency
        },
        success: function(response) {
            sessionStorage.setItem('currency_selected', 'true');
            $('#currency-selector-modal').modal('hide');
            $('#geo-currency-detection').hide();
            location.reload(); // Reload to show prices in new currency
        }
    });
}
</script>
@endif 