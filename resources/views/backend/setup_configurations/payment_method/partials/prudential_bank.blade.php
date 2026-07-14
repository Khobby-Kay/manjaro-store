<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="prudential_bank">
    <div class="form-group row">
        <input type="hidden" name="types[]" value="PRUDENTIAL_API_URL">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('API URL') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="PRUDENTIAL_API_URL"
                value="{{ env('PRUDENTIAL_API_URL') }}"
                placeholder="{{ translate('Prudential Bank API URL') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="PRUDENTIAL_GET_URL">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('GET URL') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="PRUDENTIAL_GET_URL"
                value="{{ env('PRUDENTIAL_GET_URL') }}"
                placeholder="{{ translate('Prudential Bank GET URL') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="PRUDENTIAL_CERT_PATH">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Certificate Path') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="PRUDENTIAL_CERT_PATH"
                value="{{ env('PRUDENTIAL_CERT_PATH') }}"
                placeholder="{{ translate('Path to certificate file') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="PRUDENTIAL_KEY_PATH">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Private Key Path') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="PRUDENTIAL_KEY_PATH"
                value="{{ env('PRUDENTIAL_KEY_PATH') }}"
                placeholder="{{ translate('Path to private key file') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="PRUDENTIAL_CA_PATH">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('CA Certificate Path') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="PRUDENTIAL_CA_PATH"
                value="{{ env('PRUDENTIAL_CA_PATH') }}"
                placeholder="{{ translate('Path to CA certificate file') }}">
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="PRUDENTIAL_ORDER_TYPE_RID">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Order Type RID') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="PRUDENTIAL_ORDER_TYPE_RID"
                value="{{ env('PRUDENTIAL_ORDER_TYPE_RID', '225') }}"
                placeholder="{{ translate('Order Type RID') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="PRUDENTIAL_CURRENCY">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Currency') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="PRUDENTIAL_CURRENCY"
                value="{{ env('PRUDENTIAL_CURRENCY', 'GHS') }}"
                placeholder="{{ translate('Currency Code') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="PRUDENTIAL_CALLBACK_URL">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Callback URL') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="PRUDENTIAL_CALLBACK_URL"
                value="{{ env('PRUDENTIAL_CALLBACK_URL') }}"
                placeholder="{{ translate('Callback URL') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Sandbox Mode') }}</label>
        </div>
        <div class="col-md-8">
            <label class="aiz-switch aiz-switch-success mb-0">
                <input value="1" name="prudential_sandbox_mode" type="checkbox"
                    @if (get_setting('prudential_sandbox_mode') == 1) checked @endif>
                <span class="slider round"></span>
            </label>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>
