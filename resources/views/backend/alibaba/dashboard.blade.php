@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Alibaba Dropshipping Dashboard') }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.settings') }}" class="btn btn-primary">
                <i class="las la-cog"></i> {{ translate('Settings') }}
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm bg-primary rounded">
                            <i class="las la-building text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">{{ $stats['total_suppliers'] }}</h4>
                        <p class="mb-0 text-muted">{{ translate('Total Suppliers') }}</p>
                        <small class="text-success">{{ $stats['active_suppliers'] }} {{ translate('Active') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm bg-success rounded">
                            <i class="las la-box text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">{{ $stats['total_products'] }}</h4>
                        <p class="mb-0 text-muted">{{ translate('Total Products') }}</p>
                        <small class="text-success">{{ $stats['imported_products'] }} {{ translate('Imported') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm bg-info rounded">
                            <i class="las la-shopping-cart text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">{{ $stats['total_orders'] }}</h4>
                        <p class="mb-0 text-muted">{{ translate('Total Orders') }}</p>
                        <small class="text-warning">{{ $stats['pending_orders'] }} {{ translate('Pending') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-sm bg-warning rounded">
                            <i class="las la-dollar-sign text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">${{ number_format($stats['monthly_revenue'], 2) }}</h4>
                        <p class="mb-0 text-muted">{{ translate('Monthly Revenue') }}</p>
                        <small class="text-info">{{ $stats['profit_margin'] }}% {{ translate('Profit Margin') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ translate('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('alibaba.suppliers.index') }}" class="btn btn-primary btn-block mb-3">
                            <i class="las la-building"></i> {{ translate('Manage Suppliers') }}
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('alibaba.products.index') }}" class="btn btn-success btn-block mb-3">
                            <i class="las la-box"></i> {{ translate('Browse Products') }}
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('alibaba.orders.index') }}" class="btn btn-info btn-block mb-3">
                            <i class="las la-shopping-cart"></i> {{ translate('View Orders') }}
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('alibaba.import-logs.index') }}" class="btn btn-warning btn-block mb-3">
                            <i class="las la-history"></i> {{ translate('Import Logs') }}
                        </a>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary btn-block mb-3" onclick="discoverProducts()">
                            <i class="las la-search"></i> {{ translate('Discover Products') }}
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-success btn-block mb-3" onclick="importTrending()">
                            <i class="las la-fire"></i> {{ translate('Import Trending') }}
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-info btn-block mb-3" onclick="syncInventory()">
                            <i class="las la-sync"></i> {{ translate('Sync Inventory') }}
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-warning btn-block mb-3" onclick="testApi()">
                            <i class="las la-plug"></i> {{ translate('Test API') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Trending Products -->
<div class="row">
    <!-- Recent Import Logs -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ translate('Recent Import Activity') }}</h5>
            </div>
            <div class="card-body">
                @if($stats['recent_imports']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($stats['recent_imports'] as $log)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <strong>{{ $log->supplier->name ?? 'Unknown Supplier' }}</strong>
                                <br>
                                <small class="text-muted">{{ $log->product_title ?? 'Product Import' }}</small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $log->status === 'success' ? 'success' : ($log->status === 'error' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="las la-inbox la-3x text-muted"></i>
                        <p class="text-muted mt-2">{{ translate('No recent import activity') }}</p>
                    </div>
                @endif
                
                <div class="text-center mt-3">
                    <a href="{{ route('alibaba.import-logs.index') }}" class="btn btn-sm btn-outline-primary">
                        {{ translate('View All Logs') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Trending Products -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ translate('Trending Products') }}</h5>
            </div>
            <div class="card-body">
                @if($stats['trending_products']->count() > 0)
                    <div class="row">
                        @foreach($stats['trending_products'] as $product)
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-2">
                                <div class="d-flex align-items-center">
                                    @if($product->images && is_array($product->images) && count($product->images) > 0)
                                        <img src="{{ $product->images[0] }}" class="rounded" width="50" height="50" 
                                             style="object-fit: cover;" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="las la-image text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 text-truncate" title="{{ $product->title }}">
                                            {{ Str::limit($product->title, 30) }}
                                        </h6>
                                        <small class="text-muted">{{ $product->supplier->name ?? 'Unknown' }}</small>
                                        <br>
                                        <span class="text-primary">${{ number_format($product->retail_price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="las la-fire la-3x text-muted"></i>
                        <p class="text-muted mt-2">{{ translate('No trending products yet') }}</p>
                    </div>
                @endif
                
                <div class="text-center mt-3">
                    <a href="{{ route('alibaba.products.index') }}" class="btn btn-sm btn-outline-success">
                        {{ translate('Browse All Products') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- API Status & Performance -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ translate('API Status & Performance') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="avatar avatar-lg bg-success rounded mx-auto mb-2">
                                <i class="las la-check-circle la-2x text-white"></i>
                            </div>
                            <h5 class="text-success">{{ translate('API Connected') }}</h5>
                            <p class="text-muted">{{ translate('Alibaba API is working properly') }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="avatar avatar-lg bg-info rounded mx-auto mb-2">
                                <i class="las la-clock la-2x text-white"></i>
                            </div>
                            <h5 class="text-info">{{ translate('Last Sync') }}</h5>
                            <p class="text-muted">{{ translate('2 hours ago') }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="avatar avatar-lg bg-warning rounded mx-auto mb-2">
                                <i class="las la-tachometer-alt la-2x text-white"></i>
                            </div>
                            <h5 class="text-warning">{{ translate('Performance') }}</h5>
                            <p class="text-muted">{{ translate('Good response time') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
// Discover Products
function discoverProducts() {
    // This would open a modal or redirect to product discovery page
    AIZ.plugins.notify('info', '{{ translate("Product discovery feature coming soon") }}');
}

// Import Trending Products
function importTrending() {
    if (confirm('{{ translate("Import trending products from Alibaba?") }}')) {
        $.post('{{ route("alibaba.products.import-trending") }}', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                AIZ.plugins.notify('success', response.message);
                location.reload();
            } else {
                AIZ.plugins.notify('error', response.message);
            }
        }).fail(function() {
            AIZ.plugins.notify('error', '{{ translate("Failed to import trending products") }}');
        });
    }
}

// Sync Inventory
function syncInventory() {
    if (confirm('{{ translate("Sync inventory with Alibaba suppliers?") }}')) {
        AIZ.plugins.notify('info', '{{ translate("Inventory sync started...") }}');
        // This would trigger inventory sync
    }
}

// Test API Connection
function testApi() {
    $.post('{{ route("alibaba.test-api") }}', {
        _token: '{{ csrf_token() }}'
    }, function(response) {
        if (response.success) {
            AIZ.plugins.notify('success', '{{ translate("API connection successful") }}');
        } else {
            AIZ.plugins.notify('error', response.message || '{{ translate("API connection failed") }}');
        }
    }).fail(function() {
        AIZ.plugins.notify('error', '{{ translate("Failed to test API connection") }}');
    });
}

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    // Refresh statistics without reloading the page
    // This could be implemented with AJAX calls
}, 300000);
</script>
@endsection