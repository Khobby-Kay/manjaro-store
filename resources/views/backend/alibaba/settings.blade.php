@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Alibaba Settings')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.dashboard') }}" class="btn btn-primary">
                <i class="las la-arrow-left"></i>
                <span>{{translate('Back to Dashboard')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('API Configuration')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('alibaba.update-settings') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="alibaba_api_key">{{translate('API Key')}}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="alibaba_api_key" value="{{ $settings['alibaba_api_key'] ?? '' }}" placeholder="{{translate('Enter Alibaba API Key')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="alibaba_api_secret">{{translate('API Secret')}}</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="alibaba_api_secret" value="{{ $settings['alibaba_api_secret'] ?? '' }}" placeholder="{{translate('Enter Alibaba API Secret')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="alibaba_app_key">{{translate('App Key')}}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="alibaba_app_key" value="{{ $settings['alibaba_app_key'] ?? '' }}" placeholder="{{translate('Enter Alibaba App Key')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="alibaba_app_secret">{{translate('App Secret')}}</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="alibaba_app_secret" value="{{ $settings['alibaba_app_secret'] ?? '' }}" placeholder="{{translate('Enter Alibaba App Secret')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="alibaba_access_token">{{translate('Access Token')}}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="alibaba_access_token" value="{{ $settings['alibaba_access_token'] ?? '' }}" placeholder="{{translate('Enter Access Token')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="alibaba_refresh_token">{{translate('Refresh Token')}}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="alibaba_refresh_token" value="{{ $settings['alibaba_refresh_token'] ?? '' }}" placeholder="{{translate('Enter Refresh Token')}}">
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save Settings')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Pricing Configuration')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('alibaba.update-settings') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="markup_percentage">{{translate('Markup Percentage')}}</label>
                        <input type="number" class="form-control" name="markup_percentage" value="{{ $settings['markup_percentage'] ?? 20 }}" min="0" max="100" step="0.01">
                        <small class="form-text text-muted">{{translate('Percentage to add to supplier price')}}</small>
                    </div>
                    <div class="form-group">
                        <label for="shipping_cost">{{translate('Default Shipping Cost')}}</label>
                        <input type="number" class="form-control" name="shipping_cost" value="{{ $settings['shipping_cost'] ?? 0 }}" min="0" step="0.01">
                        <small class="form-text text-muted">{{translate('Default shipping cost for products')}}</small>
                    </div>
                    <div class="form-group">
                        <label for="currency">{{translate('Currency')}}</label>
                        <select class="form-control aiz-selectpicker" name="currency">
                            <option value="USD" {{ ($settings['currency'] ?? 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="EUR" {{ ($settings['currency'] ?? 'USD') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            <option value="GBP" {{ ($settings['currency'] ?? 'USD') == 'GBP' ? 'selected' : '' }}>GBP</option>
                            <option value="CNY" {{ ($settings['currency'] ?? 'USD') == 'CNY' ? 'selected' : '' }}>CNY</option>
                            <option value="GHS" {{ ($settings['currency'] ?? 'USD') == 'GHS' ? 'selected' : '' }}>GHS (Ghana Cedis)</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">{{translate('Save Pricing')}}</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Import Settings')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('alibaba.update-settings') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="auto_import">{{translate('Auto Import')}}</label>
                        <select class="form-control aiz-selectpicker" name="auto_import">
                            <option value="0" {{ ($settings['auto_import'] ?? 0) == 0 ? 'selected' : '' }}>{{translate('Disabled')}}</option>
                            <option value="1" {{ ($settings['auto_import'] ?? 0) == 1 ? 'selected' : '' }}>{{translate('Enabled')}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="import_frequency">{{translate('Import Frequency (hours)')}}</label>
                        <input type="number" class="form-control" name="import_frequency" value="{{ $settings['import_frequency'] ?? 24 }}" min="1" max="168">
                    </div>
                    <div class="form-group">
                        <label for="max_products_per_import">{{translate('Max Products per Import')}}</label>
                        <input type="number" class="form-control" name="max_products_per_import" value="{{ $settings['max_products_per_import'] ?? 100 }}" min="1" max="1000">
                    </div>
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">{{translate('Save Import Settings')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('API Test')}}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <button class="btn btn-info" onclick="test_api_connection()">
                    <i class="las la-wifi"></i>
                    {{translate('Test API Connection')}}
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-success" onclick="sync_products()">
                    <i class="las la-sync"></i>
                    {{translate('Sync Products')}}
                </button>
            </div>
        </div>
        <div id="api_test_result" class="mt-3"></div>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.aiz-selectpicker').selectpicker();
        });

        function test_api_connection() {
            $('#api_test_result').html('<div class="alert alert-info">Testing connection...</div>');
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method:"POST",
                url:'{{route('alibaba.test-api')}}',
                success: function(data, textStatus, jqXHR){
                    if(data.success){
                        $('#api_test_result').html('<div class="alert alert-success">' + data.message + '</div>');
                    }else{
                        $('#api_test_result').html('<div class="alert alert-danger">' + data.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#api_test_result').html('<div class="alert alert-danger">Connection failed: ' + error + '</div>');
                }
            });
        }

        function sync_products() {
            $('#api_test_result').html('<div class="alert alert-info">Syncing products...</div>');
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method:"POST",
                url:'{{route('alibaba.sync-products')}}',
                success: function(data, textStatus, jqXHR){
                    if(data.success){
                        $('#api_test_result').html('<div class="alert alert-success">' + data.message + '</div>');
                    }else{
                        $('#api_test_result').html('<div class="alert alert-danger">' + data.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#api_test_result').html('<div class="alert alert-danger">Sync failed: ' + error + '</div>');
                }
            });
        }
    </script>
@endsection