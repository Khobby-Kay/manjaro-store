@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Geo-Currency Settings')}}</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Geo-Currency Detection Settings')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('geo_currency_settings.update') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Enable Geo-Currency Detection')}}</label>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="geo_currency_detection" value="1" @if(get_setting('geo_currency_detection') == 1) checked @endif>
                                <span class="slider round"></span>
                            </label>
                            <small class="form-text text-muted">{{translate('Automatically detect user location and set appropriate currency')}}</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Default Fallback Currency')}}</label>
                        <div class="col-md-8">
                            <select class="form-control aiz-selectpicker" name="geo_currency_fallback" data-live-search="true">
                                @foreach($active_currencies as $currency)
                                    <option value="{{ $currency->id }}" @if(get_setting('geo_currency_fallback') == $currency->id) selected @endif>
                                        {{ $currency->name }} ({{ $currency->code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{translate('Currency to use when user location cannot be determined')}}</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Cache Duration (hours)')}}</label>
                        <div class="col-md-8">
                            <input type="number" class="form-control" name="geo_currency_cache_duration" value="{{ get_setting('geo_currency_cache_duration', 24) }}" min="1" max="168">
                            <small class="form-text text-muted">{{translate('How long to cache user location data (1-168 hours)')}}</small>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save Settings')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Quick Actions')}}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('currency.index') }}" class="btn btn-outline-primary">
                        <i class="las la-money-bill mr-2"></i>
                        {{translate('Manage Currencies')}}
                    </a>
                    <a href="{{ route('geo_currency_settings.test') }}" class="btn btn-outline-info" target="_blank">
                        <i class="las la-vial mr-2"></i>
                        {{translate('Test Detection')}}
                    </a>
                    <a href="{{ route('geo_currency_settings.statistics') }}" class="btn btn-outline-success">
                        <i class="las la-chart-bar mr-2"></i>
                        {{translate('View Statistics')}}
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="las la-info-circle mr-2"></i>{{translate('How it works:')}}</h6>
                    <ul class="mb-0">
                        <li>{{translate('System detects user IP address')}}</li>
                        <li>{{translate('Determines country and currency')}}</li>
                        <li>{{translate('Automatically sets appropriate currency')}}</li>
                        <li>{{translate('Shows prices in local currency')}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 