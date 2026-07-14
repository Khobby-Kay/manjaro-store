<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="mobile_money">
    <div class="form-group row">
        <input type="hidden" name="types[]" value="MOBILE_MONEY_API_URL">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('API URL') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="MOBILE_MONEY_API_URL"
                value="{{ env('MOBILE_MONEY_API_URL') }}"
                placeholder="{{ translate('Mobile Money API URL') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="MOBILE_MONEY_CLIENT_ID">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Client ID') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="MOBILE_MONEY_CLIENT_ID"
                value="{{ env('MOBILE_MONEY_CLIENT_ID') }}"
                placeholder="{{ translate('Mobile Money Client ID') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="MOBILE_MONEY_USERNAME">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Username') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="MOBILE_MONEY_USERNAME"
                value="{{ env('MOBILE_MONEY_USERNAME') }}"
                placeholder="{{ translate('Basic Auth Username') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="MOBILE_MONEY_PASSWORD">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Password') }}</label>
        </div>
        <div class="col-md-8">
            <input type="password" class="form-control" name="MOBILE_MONEY_PASSWORD"
                value="{{ env('MOBILE_MONEY_PASSWORD') }}"
                placeholder="{{ translate('Basic Auth Password') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="MOBILE_MONEY_CURRENCY">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Currency') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="MOBILE_MONEY_CURRENCY"
                value="{{ env('MOBILE_MONEY_CURRENCY', 'GHS') }}"
                placeholder="{{ translate('Currency Code') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="MOBILE_MONEY_CALLBACK_URL">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Callback URL') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="MOBILE_MONEY_CALLBACK_URL"
                value="{{ env('MOBILE_MONEY_CALLBACK_URL') }}"
                placeholder="{{ translate('Callback URL') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-from-label">{{ translate('Sandbox Mode') }}</label>
        </div>
        <div class="col-md-8">
            <label class="aiz-switch aiz-switch-success mb-0">
                <input value="1" name="mobile_money_sandbox_mode" type="checkbox"
                    @if (get_setting('mobile_money_sandbox_mode') == 1) checked @endif>
                <span class="slider round"></span>
            </label>
        </div>
    </div>
    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>
