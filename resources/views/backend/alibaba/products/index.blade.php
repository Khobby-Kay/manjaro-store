@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Alibaba Products')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.products.create') }}" class="btn btn-primary">
                <i class="las la-plus"></i>
                <span>{{translate('Add New Product')}}</span>
            </a>
            <button class="btn btn-success" onclick="open_bulk_import_modal()">
                <i class="las la-upload"></i>
                <span>{{translate('Bulk Import')}}</span>
            </button>
            <button class="btn btn-info" onclick="import_trending_products()">
                <i class="las la-fire"></i>
                <span>{{translate('Import Trending')}}</span>
            </button>
            <button class="btn btn-warning" onclick="open_product_discovery_modal()">
                <i class="las la-search"></i>
                <span>{{translate('Product Discovery')}}</span>
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">{{translate('All Products')}}</h5>
        </div>
        <div class="col-md-3">
            <form class="" id="sort_products" action="" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <form class="" id="sort_products" action="" method="GET">
            <div class="row gutters-5 mb-3">
                <div class="col-md-2">
                    <select class="form-control aiz-selectpicker" data-live-search="true" name="supplier_id" id="supplier_id">
                        <option value="">{{translate('All Suppliers')}}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @isset($supplier_id) @if($supplier_id == $supplier->id) selected @endif @endisset>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control aiz-selectpicker" name="status" id="status">
                        <option value="">{{translate('All Status')}}</option>
                        <option value="1" @isset($status) @if($status == 1) selected @endif @endisset>{{translate('Active')}}</option>
                        <option value="0" @isset($status) @if($status == 0) selected @endif @endisset>{{translate('Inactive')}}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" type="submit">{{translate('Filter')}}</button>
                </div>
            </div>
        </form>
        
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>
                        <div class="form-group">
                            <div class="aiz-checkbox-inline">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-all">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </div>
                    </th>
                    <th>#</th>
                    <th>{{translate('Image')}}</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Supplier')}}</th>
                    <th>{{translate('Alibaba ID')}}</th>
                    <th>{{translate('Price')}}</th>
                    <th>{{translate('Status')}}</th>
                    <th>{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @if($products->count() > 0)
                    @foreach($products as $key => $product)
                    <tr>
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check" value="{{ $product->id }}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>{{ ($key+1) + ($products->currentPage() - 1) * $products->perPage() }}</td>
                        <td>
                            @if($product->images)
                                @php
                                    $images = json_decode($product->images, true);
                                    $firstImage = is_array($images) && !empty($images) ? $images[0] : 'assets/img/placeholder.jpg';
                                @endphp
                                @if(filter_var($firstImage, FILTER_VALIDATE_URL))
                                    <img src="{{ $firstImage }}" alt="{{ $product->title }}" class="img-60-60">
                                @else
                                    <img src="{{ asset($firstImage) }}" alt="{{ $product->title }}" class="img-60-60">
                                @endif
                            @else
                                <img src="{{ asset('assets/img/placeholder.jpg') }}" alt="Placeholder" class="img-60-60">
                            @endif
                        </td>
                        <td>{{ $product->title }}</td>
                        <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                        <td>{{ $product->alibaba_product_id }}</td>
                        <td>{{ single_price($product->retail_price) }}</td>
                        <td>
                            @if($product->status == 'imported')
                                <span class="badge badge-inline badge-success">{{translate('Active')}}</span>
                            @elseif($product->status == 'pending')
                                <span class="badge badge-inline badge-warning">{{translate('Pending')}}</span>
                            @else
                                <span class="badge badge-inline badge-danger">{{translate('Error')}}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('alibaba.products.edit', $product->id)}}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{route('alibaba.products.show', $product->id)}}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('alibaba.products.destroy', $product->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center">
                            <p class="text-muted">{{ translate('No products found.') }}</p>
                            <a href="{{ route('alibaba.products.create') }}" class="btn btn-primary btn-sm">
                                {{ translate('Add Your First Product') }}
                            </a>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $products->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
    
    <!-- Bulk Import Modal -->
    <div class="modal fade" id="bulk-import-modal" tabindex="-1" role="dialog" aria-labelledby="bulk-import-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulk-import-modal-label">{{translate('Bulk Import Products')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{translate('Method 1: CSV Upload')}}</h6>
                            <form id="csv-upload-form" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label>{{translate('Upload CSV File')}}</label>
                                    <input type="file" class="form-control" name="csv_file" accept=".csv">
                                    <small class="form-text text-muted">{{translate('Download sample CSV format below')}}</small>
                                </div>
                                <div class="form-group">
                                    <label>{{translate('Default Markup (%)')}}</label>
                                    <input type="number" class="form-control" name="default_markup" value="35" min="0" max="1000">
                                </div>
                                <button type="submit" class="btn btn-primary">{{translate('Upload & Import')}}</button>
                                <a href="#" class="btn btn-secondary" onclick="download_csv_template()">{{translate('Download Template')}}</a>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h6>{{translate('Method 2: Supplier Catalog')}}</h6>
                            <form id="supplier-catalog-form">
                                @csrf
                                <div class="form-group">
                                    <label>{{translate('Select Supplier')}}</label>
                                    <select class="form-control aiz-selectpicker" name="supplier_id" data-live-search="true">
                                        <option value="">{{translate('Choose Supplier')}}</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>{{translate('Import Limit')}}</label>
                                    <input type="number" class="form-control" name="import_limit" value="100" min="1" max="1000">
                                </div>
                                <div class="form-group">
                                    <label>{{translate('Markup (%)')}}</label>
                                    <input type="number" class="form-control" name="markup_percentage" value="35" min="0" max="1000">
                                </div>
                                <button type="submit" class="btn btn-success">{{translate('Import Supplier Catalog')}}</button>
                            </form>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>{{translate('Method 3: Trending Products')}}</h6>
                            <p class="text-muted">{{translate('Automatically import trending products from Alibaba')}}</p>
                            <button class="btn btn-info" onclick="import_trending_products()">
                                <i class="las la-fire"></i>
                                {{translate('Import Trending Products')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Discovery Modal -->
    <div class="modal fade" id="product-discovery-modal" tabindex="-1" role="dialog" aria-labelledby="product-discovery-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="product-discovery-modal-label">{{translate('Product Discovery')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>{{translate('Search Criteria')}}</h6>
                            <form id="product-discovery-form">
                                @csrf
                                <div class="form-group">
                                    <label>{{translate('Category')}}</label>
                                    <select class="form-control" name="category" id="discovery-category">
                                        <option value="">{{translate('All Categories')}}</option>
                                        <option value="electronics">Electronics</option>
                                        <option value="fashion">Fashion & Apparel</option>
                                        <option value="home">Home & Garden</option>
                                        <option value="beauty">Beauty & Health</option>
                                        <option value="sports">Sports & Outdoor</option>
                                        <option value="automotive">Automotive</option>
                                        <option value="toys">Toys & Hobbies</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>{{translate('Price Range')}}</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="number" class="form-control" name="min_price" placeholder="Min Price" min="0">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" class="form-control" name="max_price" placeholder="Max Price" min="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{translate('Sort By')}}</label>
                                    <select class="form-control" name="sort_by">
                                        <option value="relevance">{{translate('Relevance')}}</option>
                                        <option value="price_low">{{translate('Price: Low to High')}}</option>
                                        <option value="price_high">{{translate('Price: High to Low')}}</option>
                                        <option value="orders">{{translate('Most Orders')}}</option>
                                        <option value="rating">{{translate('Highest Rating')}}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>{{translate('Results Limit')}}</label>
                                    <input type="number" class="form-control" name="limit" value="20" min="1" max="100">
                                </div>
                                <div class="form-group">
                                    <label>{{translate('Default Markup (%)')}}</label>
                                    <input type="number" class="form-control" name="markup_percentage" value="35" min="0" max="1000">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">{{translate('Search Products')}}</button>
                            </form>
                        </div>
                        <div class="col-md-8">
                            <h6>{{translate('Search Results')}}</h6>
                            <div id="discovery-results" class="row">
                                <div class="col-12 text-center">
                                    <p class="text-muted">{{translate('Search for products to see results here')}}</p>
                                </div>
                            </div>
                            <div id="discovery-loading" class="text-center" style="display: none;">
                                <i class="las la-spinner la-spin"></i> {{translate('Searching products...')}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        // Simple script for search functionality
        $(document).ready(function() {
            $('#search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // CSV Upload Form
            $('#csv-upload-form').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                $.ajax({
                    url: '{{ route("alibaba.products.bulk-import-csv") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if(response.success) {
                            AIZ.plugins.notify('success', response.message);
                            $('#bulk-import-modal').modal('hide');
                            location.reload();
                        } else {
                            AIZ.plugins.notify('error', response.message);
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('error', '{{ translate("Upload failed") }}');
                    }
                });
            });

            // Supplier Catalog Form
            $('#supplier-catalog-form').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                $.ajax({
                    url: '{{ route("alibaba.products.bulk-import-supplier") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if(response.success) {
                            AIZ.plugins.notify('success', response.message);
                            $('#bulk-import-modal').modal('hide');
                            location.reload();
                        } else {
                            AIZ.plugins.notify('error', response.message);
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('error', '{{ translate("Import failed") }}');
                    }
                });
            });
        });

        function open_bulk_import_modal() {
            $('#bulk-import-modal').modal('show');
        }

        function import_trending_products() {
            if(confirm('{{ translate("Import trending products from Alibaba? This may take a few minutes.") }}')) {
                $.ajax({
                    url: '{{ route("alibaba.products.import-trending") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                        AIZ.plugins.notify('error', '{{ translate("Import failed") }}');
                    }
                });
            }
        }

        function download_csv_template() {
            // Create and download CSV template
            var csvContent = "alibaba_url,supplier_id,markup_percentage\n";
            csvContent += "https://alibaba.com/product1,1,35\n";
            csvContent += "https://alibaba.com/product2,1,40\n";
            csvContent += "https://alibaba.com/product3,2,30\n";
            csvContent += "https://alibaba.com/product4,1,45\n";
            csvContent += "https://alibaba.com/product5,3,30\n";
            
            var blob = new Blob([csvContent], { type: 'text/csv' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'alibaba_products_template.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        function open_product_discovery_modal() {
            $('#product-discovery-modal').modal('show');
        }

        // Product Discovery Form
        $('#product-discovery-form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            $('#discovery-loading').show();
            $('#discovery-results').hide();
            
            $.ajax({
                url: '{{ route("alibaba.products.discover") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#discovery-loading').hide();
                    if(response.success) {
                        displayDiscoveryResults(response.products);
                    } else {
                        AIZ.plugins.notify('error', response.message);
                    }
                },
                error: function() {
                    $('#discovery-loading').hide();
                    AIZ.plugins.notify('error', '{{ translate("Search failed") }}');
                }
            });
        });

        function displayDiscoveryResults(products) {
            var resultsHtml = '';
            
            if (products.length === 0) {
                resultsHtml = '<div class="col-12 text-center"><p class="text-muted">{{ translate("No products found") }}</p></div>';
            } else {
                products.forEach(function(product) {
                    resultsHtml += `
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            <img src="${product.image || '{{ asset("assets/img/placeholder.jpg") }}'}" class="img-fluid rounded" alt="${product.title}">
                                        </div>
                                        <div class="col-8">
                                            <h6 class="card-title">${product.title}</h6>
                                            <p class="card-text text-muted small">${product.description}</p>
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>{{ translate("Price:") }}</strong> $${product.price}
                                                </div>
                                                <div class="col-6">
                                                    <strong>{{ translate("Orders:") }}</strong> ${product.orders}
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-primary" onclick="importSingleProduct('${product.url}', ${product.markup})">
                                                    <i class="las la-plus"></i> {{ translate("Import") }}
                                                </button>
                                                <a href="${product.url}" target="_blank" class="btn btn-sm btn-secondary">
                                                    <i class="las la-external-link-alt"></i> {{ translate("View") }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            $('#discovery-results').html(resultsHtml).show();
        }

        function importSingleProduct(url, markup) {
            if(confirm('{{ translate("Import this product?") }}')) {
                $.ajax({
                    url: '{{ route("alibaba.products.import-single") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        alibaba_url: url,
                        markup_percentage: markup,
                        supplier_id: 1 // Default supplier
                    },
                    success: function(response) {
                        if(response.success) {
                            AIZ.plugins.notify('success', response.message);
                            $('#product-discovery-modal').modal('hide');
                            location.reload();
                        } else {
                            AIZ.plugins.notify('error', response.message);
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('error', '{{ translate("Import failed") }}');
                    }
                });
            }
        }
    </script>
@endsection