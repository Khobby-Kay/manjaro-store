@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Add New Alibaba Product')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.products.index') }}" class="btn btn-primary">
                <i class="las la-arrow-left"></i>
                <span>{{translate('Back to Products')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('alibaba.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
            @csrf
            <input type="hidden" name="import_method" value="url">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="alibaba_url">{{translate('Alibaba Product URL')}} <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" name="alibaba_url" id="alibaba_url" placeholder="https://www.alibaba.com/product-detail/..." required>
                        <small class="form-text text-muted">{{translate('Enter the complete Alibaba product URL')}}</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="supplier_id">{{translate('Supplier')}} <span class="text-muted">(Auto-created from URL)</span></label>
                        <select class="form-control aiz-selectpicker" name="supplier_id" id="supplier_id" data-live-search="true">
                            <option value="">{{translate('Will be auto-created from product URL')}}</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">{{translate('Supplier will be automatically created and selected when you paste the Alibaba URL')}}</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="markup_percentage">{{translate('Markup Percentage')}} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="markup_percentage" id="markup_percentage" value="35" min="0" max="1000" step="0.01" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">{{translate('Percentage to add to the original price (e.g., 35 for 35%)')}}</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">{{translate('Category')}} <span class="text-muted">(Auto-selected)</span></label>
                        <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-live-search="true">
                            <option value="">{{translate('Choose Category')}}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $category->name === 'Manjaro Import' ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">{{translate('Manjaro Import category is automatically selected for all Alibaba products')}}</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="brand_id">{{translate('Brand')}}</label>
                        <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id" data-live-search="true">
                            <option value="">{{translate('Choose Brand')}}</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">{{translate('Product Title')}} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="{{translate('Enter product title')}}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">{{translate('Description')}}</label>
                        <textarea class="form-control aiz-text-editor" name="description" id="description" rows="4" placeholder="{{translate('Enter product description')}}"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_title">{{translate('Meta Title')}}</label>
                        <input type="text" class="form-control" name="meta_title" id="meta_title" placeholder="{{translate('Enter meta title for SEO')}}">
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description">{{translate('Meta Description')}}</label>
                        <textarea class="form-control" name="meta_description" id="meta_description" rows="3" placeholder="{{translate('Enter meta description for SEO')}}"></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="unit_price">{{translate('Unit Price')}} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ get_system_default_currency()->symbol }}</span>
                            </div>
                            <input type="number" class="form-control" name="unit_price" id="unit_price" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="retail_price">{{translate('Retail Price')}} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ get_system_default_currency()->symbol }}</span>
                            </div>
                            <input type="number" class="form-control" name="retail_price" id="retail_price" min="0" step="0.01" required>
                        </div>
                        <small class="form-text text-muted">{{translate('Final selling price after markup')}}</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="min_qty">{{translate('Minimum Quantity')}}</label>
                        <input type="number" class="form-control" name="min_qty" id="min_qty" value="1" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="max_qty">{{translate('Maximum Quantity')}}</label>
                        <input type="number" class="form-control" name="max_qty" id="max_qty" value="1000" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="sku">{{translate('SKU')}}</label>
                        <input type="text" class="form-control" name="sku" id="sku" placeholder="{{translate('Auto-generated if empty')}}">
                    </div>
                    
                    <div class="form-group">
                        <label for="barcode">{{translate('Barcode')}}</label>
                        <input type="text" class="form-control" name="barcode" id="barcode" placeholder="{{translate('Enter barcode')}}">
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">{{translate('Weight (kg)')}}</label>
                        <input type="number" class="form-control" name="weight" id="weight" min="0" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="dimensions">{{translate('Dimensions (L x W x H cm)')}}</label>
                        <div class="row">
                            <div class="col-4">
                                <input type="number" class="form-control" name="length" placeholder="L" min="0" step="0.01">
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control" name="width" placeholder="W" min="0" step="0.01">
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control" name="height" placeholder="H" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">{{translate('Status')}}</label>
                        <select class="form-control" name="status" id="status">
                            <option value="pending">{{translate('Pending')}}</option>
                            <option value="imported">{{translate('Active')}}</option>
                            <option value="error">{{translate('Error')}}</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="images">{{translate('Product Images')}}</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                            <input type="hidden" name="images" class="selected-files">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                        <small class="form-text text-muted">{{translate('Upload product images. First image will be used as main image.')}}</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="tags">{{translate('Tags')}}</label>
                        <input type="text" class="form-control aiz-tag-input" name="tags" id="tags" placeholder="{{translate('Enter tags separated by comma')}}">
                        <small class="form-text text-muted">{{translate('Separate tags with commas')}}</small>
                    </div>
                </div>
            </div>
            
            <div class="form-group mb-0 text-right">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">{{translate('Cancel')}}</button>
                <button type="submit" class="btn btn-primary">{{translate('Save Product')}}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Auto-fetch product details from Alibaba URL
            $('#alibaba_url').on('blur', function() {
                var url = $(this).val();
                console.log('URL blur event triggered:', url);
                if (url && isValidAlibabaUrl(url)) {
                    console.log('URL is valid, fetching details...');
                    fetchProductDetails(url);
                } else {
                    console.log('URL is invalid or empty');
                }
            });
            
            // Calculate retail price when unit price or markup changes
            $('#unit_price, #markup_percentage').on('input', function() {
                calculateRetailPrice();
            });
            
            // Auto-generate SKU
            $('#title').on('input', function() {
                generateSKU();
            });
        });
        
        function isValidAlibabaUrl(url) {
            return url.includes('alibaba.com') && (url.includes('product-detail') || url.includes('/product/'));
        }
        
        function createOrSelectSupplier(supplierName) {
            // Check if supplier already exists
            var existingOption = $('#supplier_id option').filter(function() {
                return $(this).text().trim() === supplierName;
            });
            
            if (existingOption.length > 0) {
                // Select existing supplier
                $('#supplier_id').val(existingOption.val());
                $('#supplier_id').trigger('change');
            } else {
                // Create new supplier via AJAX
                $.ajax({
                    url: '{{ route("alibaba.suppliers.store") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        name: supplierName,
                        contact_person: 'Auto-created',
                        email: 'supplier@alibaba.com',
                        phone: 'N/A',
                        address: 'Alibaba Supplier',
                        country: 'China',
                        status: 'active'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Add new option to select
                            var newOption = new Option(supplierName, response.supplier_id, true, true);
                            $('#supplier_id').append(newOption).trigger('change');
                            AIZ.plugins.notify('success', 'Supplier created and selected: ' + supplierName);
                        }
                    },
                    error: function() {
                        AIZ.plugins.notify('warning', 'Could not create supplier, please select manually');
                    }
                });
            }
        }
        
        function fetchProductDetails(url) {
            if (!url) return;
            
            console.log('fetchProductDetails called with URL:', url);
            
            // Show loading indicator
            $('#alibaba_url').addClass('loading');
            
            $.ajax({
                url: '{{ route("alibaba.products.fetch-details") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    alibaba_url: url
                },
                success: function(response) {
                    console.log('AJAX Response:', response);
                    if (response.success) {
                        console.log('Success! Data:', response.data);
                        // Populate form fields with fetched data
                        if (response.data.title) {
                            $('#title').val(response.data.title);
                        }
                        if (response.data.description) {
                            $('#description').val(response.data.description);
                        }
                        if (response.data.original_price) {
                            $('#unit_price').val(response.data.original_price);
                            calculateRetailPrice();
                        }
                        if (response.data.images && response.data.images.length > 0) {
                            // Set images for uploader
                            var imageHtml = '';
                            response.data.images.forEach(function(image) {
                                imageHtml += '<div class="file-preview-item"><img src="' + image + '" class="img-fit"></div>';
                            });
                            $('.file-preview').html(imageHtml);
                            $('.selected-files').val(JSON.stringify(response.data.images));
                        }
                        
                        // Auto-select Manjaro Import category
                        $('#category_id').val('{{ $manjaroImportCategoryId ?? "" }}');
                        $('#category_id').trigger('change');
                        
                        // Auto-create supplier if supplier_name is provided
                        if (response.data.supplier_name) {
                            createOrSelectSupplier(response.data.supplier_name);
                        }
                        
                        AIZ.plugins.notify('success', '{{ translate("Product details fetched successfully") }}');
                    } else {
                        console.log('Error in response:', response.message);
                        AIZ.plugins.notify('warning', response.message || '{{ translate("Could not fetch product details") }}');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', xhr.responseText);
                    console.log('Status:', status);
                    console.log('Error:', error);
                    AIZ.plugins.notify('error', '{{ translate("Failed to fetch product details") }}');
                },
                complete: function() {
                    $('#alibaba_url').removeClass('loading');
                }
            });
        }
        
        function calculateRetailPrice() {
            var unitPrice = parseFloat($('#unit_price').val()) || 0;
            var markup = parseFloat($('#markup_percentage').val()) || 0;
            
            if (unitPrice > 0 && markup >= 0) {
                var retailPrice = unitPrice * (1 + markup / 100);
                $('#retail_price').val(retailPrice.toFixed(2));
            }
        }
        
        function generateSKU() {
            var title = $('#title').val();
            if (title) {
                var sku = title.toLowerCase()
                    .replace(/[^a-z0-9\s]/g, '')
                    .replace(/\s+/g, '-')
                    .substring(0, 50);
                $('#sku').val(sku);
            }
        }
        
        // Form validation
        $('#product-form').on('submit', function(e) {
            var isValid = true;
            var errors = [];
            
            // Validate required fields
            if (!$('#alibaba_url').val()) {
                errors.push('{{ translate("Alibaba URL is required") }}');
                isValid = false;
            }
            
            if (!$('#supplier_id').val()) {
                errors.push('{{ translate("Supplier is required") }}');
                isValid = false;
            }
            
            if (!$('#title').val()) {
                errors.push('{{ translate("Product title is required") }}');
                isValid = false;
            }
            
            if (!$('#unit_price').val() || parseFloat($('#unit_price').val()) <= 0) {
                errors.push('{{ translate("Valid unit price is required") }}');
                isValid = false;
            }
            
            if (!$('#retail_price').val() || parseFloat($('#retail_price').val()) <= 0) {
                errors.push('{{ translate("Valid retail price is required") }}');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                AIZ.plugins.notify('error', errors.join('<br>'));
            }
        });
    </script>
    
    <style>
        .loading {
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
@endsection