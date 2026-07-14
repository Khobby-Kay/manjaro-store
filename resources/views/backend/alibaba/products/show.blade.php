@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Product Details')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.products.index') }}" class="btn btn-primary">
                <i class="las la-arrow-left"></i>
                <span>{{translate('Back to Products')}}</span>
            </a>
            <a href="{{ route('alibaba.products.edit', $product->id) }}" class="btn btn-success">
                <i class="las la-edit"></i>
                <span>{{translate('Edit Product')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{translate('Product Title:')}}</strong></td>
                                <td>{{ $product->title }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Alibaba ID:')}}</strong></td>
                                <td>{{ $product->alibaba_product_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Supplier:')}}</strong></td>
                                <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('SKU:')}}</strong></td>
                                <td>{{ $product->sku ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Barcode:')}}</strong></td>
                                <td>{{ $product->barcode ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Status:')}}</strong></td>
                                <td>
                                    @if($product->status == 'imported')
                                        <span class="badge badge-inline badge-success">{{translate('Active')}}</span>
                                    @elseif($product->status == 'pending')
                                        <span class="badge badge-inline badge-warning">{{translate('Pending')}}</span>
                                    @else
                                        <span class="badge badge-inline badge-danger">{{translate('Error')}}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{translate('Unit Price:')}}</strong></td>
                                <td>{{ single_price($product->unit_price) }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Retail Price:')}}</strong></td>
                                <td>{{ single_price($product->retail_price) }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Markup:')}}</strong></td>
                                <td>{{ $product->markup_percentage }}%</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Min Quantity:')}}</strong></td>
                                <td>{{ $product->min_qty ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Max Quantity:')}}</strong></td>
                                <td>{{ $product->max_qty ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Weight:')}}</strong></td>
                                <td>{{ $product->weight ? $product->weight . ' kg' : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($product->description)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>{{translate('Description')}}</h6>
                        <div class="border p-3 rounded">
                            {!! $product->description !!}
                        </div>
                    </div>
                </div>
                @endif
                
                @if($product->tags)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>{{translate('Tags')}}</h6>
                        <div class="d-flex flex-wrap">
                            @php
                                $tags = explode(',', $product->tags);
                            @endphp
                            @foreach($tags as $tag)
                                <span class="badge badge-primary mr-1 mb-1">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                @if($product->dimensions)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>{{translate('Dimensions')}}</h6>
                        <p class="mb-0">
                            {{ $product->length ? $product->length . ' cm' : 'N/A' }} × 
                            {{ $product->width ? $product->width . ' cm' : 'N/A' }} × 
                            {{ $product->height ? $product->height . ' cm' : 'N/A' }}
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        @if($product->meta_title || $product->meta_description)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('SEO Information')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table table-borderless">
                            @if($product->meta_title)
                            <tr>
                                <td><strong>{{translate('Meta Title:')}}</strong></td>
                                <td>{{ $product->meta_title }}</td>
                            </tr>
                            @endif
                            @if($product->meta_description)
                            <tr>
                                <td><strong>{{translate('Meta Description:')}}</strong></td>
                                <td>{{ $product->meta_description }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Product Images')}}</h5>
            </div>
            <div class="card-body">
                @if($product->images)
                    @php
                        $images = json_decode($product->images, true);
                    @endphp
                    @if(is_array($images) && count($images) > 0)
                        <div class="row">
                            @foreach($images as $index => $image)
                                <div class="col-6 mb-3">
                                    <div class="position-relative">
                                        @if(filter_var($image, FILTER_VALIDATE_URL))
                                            <img src="{{ $image }}" class="img-fluid rounded" alt="{{ $product->title }}">
                                        @else
                                            <img src="{{ asset($image) }}" class="img-fluid rounded" alt="{{ $product->title }}">
                                        @endif
                                        @if($index == 0)
                                            <span class="badge badge-success position-absolute" style="top: 5px; right: 5px;">{{translate('Main')}}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">{{translate('No images available')}}</p>
                    @endif
                @else
                    <p class="text-muted text-center">{{translate('No images available')}}</p>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Quick Actions')}}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ $product->alibaba_url }}" target="_blank" class="btn btn-info">
                        <i class="las la-external-link-alt"></i>
                        {{translate('View on Alibaba')}}
                    </a>
                    
                    <button class="btn btn-warning" onclick="syncProduct({{ $product->id }})">
                        <i class="las la-sync"></i>
                        {{translate('Sync with Alibaba')}}
                    </button>
                    
                    <button class="btn btn-success" onclick="updatePricing({{ $product->id }})">
                        <i class="las la-dollar-sign"></i>
                        {{translate('Update Pricing')}}
                    </button>
                    
                    <a href="{{ route('alibaba.products.edit', $product->id) }}" class="btn btn-primary">
                        <i class="las la-edit"></i>
                        {{translate('Edit Product')}}
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Product Statistics')}}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-0">{{ $product->views ?? 0 }}</h4>
                            <small class="text-muted">{{translate('Views')}}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-0">{{ $product->orders_count ?? 0 }}</h4>
                        <small class="text-muted">{{translate('Orders')}}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($product->alibaba_url)
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Alibaba Product Link')}}</h5>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ $product->alibaba_url }}" readonly>
                    <div class="input-group-append">
                        <a href="{{ $product->alibaba_url }}" target="_blank" class="btn btn-primary">
                            <i class="las la-external-link-alt"></i>
                            {{translate('Open')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('script')
    <script type="text/javascript">
        function syncProduct(productId) {
            if(confirm('{{ translate("Sync this product with Alibaba? This will update the product information.") }}')) {
                $.ajax({
                    url: '{{ route("alibaba.products.sync", ":product") }}'.replace(':product', productId),
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        product_id: productId
                    },
                    success: function(response) {
                        if(response.success) {
                            AIZ.plugins.notify('success', response.message);
                            location.reload();
                        } else {
                            AIZ.plugins.notify('error', response.message);
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('error', '{{ translate("Sync failed") }}');
                    }
                });
            }
        }
        
        function updatePricing(productId) {
            if(confirm('{{ translate("Update pricing for this product? This will recalculate the retail price based on current markup.") }}')) {
                $.ajax({
                    url: '{{ route("alibaba.products.update-pricing", ":product") }}'.replace(':product', productId),
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        product_id: productId
                    },
                    success: function(response) {
                        if(response.success) {
                            AIZ.plugins.notify('success', response.message);
                            location.reload();
                        } else {
                            AIZ.plugins.notify('error', response.message);
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('error', '{{ translate("Update failed") }}');
                    }
                });
            }
        }
    </script>
@endsection
