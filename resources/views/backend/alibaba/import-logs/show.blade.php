@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Import Log Details')}} #{{ $log->id }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('alibaba.import-logs.index') }}" class="btn btn-primary">
                <i class="las la-arrow-left"></i>
                <span>{{translate('Back to Logs')}}</span>
            </a>
            @if($log->status == 'error' && $log->type == 'bulk_csv')
                <button class="btn btn-warning" onclick="retryImport({{ $log->id }})">
                    <i class="las la-redo"></i>
                    <span>{{translate('Retry Import')}}</span>
                </button>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Import Summary')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{translate('Log ID:')}}</strong></td>
                                <td>#{{ $log->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Type:')}}</strong></td>
                                <td>
                                    @if($log->type == 'single')
                                        <span class="badge badge-inline badge-primary">{{translate('Single Import')}}</span>
                                    @elseif($log->type == 'bulk_csv')
                                        <span class="badge badge-inline badge-info">{{translate('Bulk CSV')}}</span>
                                    @elseif($log->type == 'bulk_supplier')
                                        <span class="badge badge-inline badge-warning">{{translate('Bulk Supplier')}}</span>
                                    @elseif($log->type == 'trending')
                                        <span class="badge badge-inline badge-success">{{translate('Trending')}}</span>
                                    @elseif($log->type == 'discovery')
                                        <span class="badge badge-inline badge-secondary">{{translate('Discovery')}}</span>
                                    @else
                                        <span class="badge badge-inline badge-light">{{translate('Unknown')}}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Status:')}}</strong></td>
                                <td>
                                    @if($log->status == 'success')
                                        <span class="badge badge-inline badge-success">{{translate('Success')}}</span>
                                    @elseif($log->status == 'error')
                                        <span class="badge badge-inline badge-danger">{{translate('Error')}}</span>
                                    @elseif($log->status == 'partial')
                                        <span class="badge badge-inline badge-warning">{{translate('Partial')}}</span>
                                    @else
                                        <span class="badge badge-inline badge-secondary">{{translate('Unknown')}}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Started At:')}}</strong></td>
                                <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{translate('Total Items:')}}</strong></td>
                                <td>{{ $log->total_items ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Success Count:')}}</strong></td>
                                <td><span class="text-success">{{ $log->success_count ?? 0 }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Error Count:')}}</strong></td>
                                <td><span class="text-danger">{{ $log->error_count ?? 0 }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>{{translate('Duration:')}}</strong></td>
                                <td>
                                    @if($log->duration)
                                        {{ $log->duration }}s
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($log->message)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>{{translate('Message')}}</h6>
                        <div class="border p-3 rounded">
                            {{ $log->message }}
                        </div>
                    </div>
                </div>
                @endif
                
                @if($log->metadata)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>{{translate('Additional Information')}}</h6>
                        <div class="border p-3 rounded">
                            <pre class="mb-0">{{ json_encode(json_decode($log->metadata), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        @if($log->error_count > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Error Details')}}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{translate('Item')}}</th>
                                <th>{{translate('Error')}}</th>
                                <th>{{translate('Line')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($log->errors)
                                @php
                                    $errors = json_decode($log->errors, true);
                                @endphp
                                @if(is_array($errors))
                                    @foreach($errors as $error)
                                    <tr>
                                        <td>{{ $error['item'] ?? 'N/A' }}</td>
                                        <td class="text-danger">{{ $error['message'] ?? 'N/A' }}</td>
                                        <td>{{ $error['line'] ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center">{{translate('No error details available')}}</td>
                                    </tr>
                                @endif
                            @else
                                <tr>
                                    <td colspan="3" class="text-center">{{translate('No error details available')}}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        
        @if($log->success_count > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Successfully Imported Products')}}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{translate('Product ID')}}</th>
                                <th>{{translate('Title')}}</th>
                                <th>{{translate('Price')}}</th>
                                <th>{{translate('Status')}}</th>
                                <th>{{translate('Actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($log->successful_products)
                                @php
                                    $products = json_decode($log->successful_products, true);
                                @endphp
                                @if(is_array($products))
                                    @foreach($products as $product)
                                    <tr>
                                        <td>{{ $product['id'] ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($product['title'] ?? 'N/A', 50) }}</td>
                                        <td>{{ single_price($product['price'] ?? 0) }}</td>
                                        <td>
                                            <span class="badge badge-inline badge-success">{{translate('Imported')}}</span>
                                        </td>
                                        <td>
                                            @if(isset($product['id']))
                                                <a href="{{route('alibaba.products.show', $product['id'])}}" class="btn btn-sm btn-primary">
                                                    <i class="las la-eye"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">{{translate('No product details available')}}</td>
                                    </tr>
                                @endif
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">{{translate('No product details available')}}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Import Statistics')}}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-0">{{ $log->total_items ?? 0 }}</h4>
                            <small class="text-muted">{{translate('Total Items')}}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-0">{{ $log->success_count ?? 0 }}</h4>
                        <small class="text-muted">{{translate('Success')}}</small>
                    </div>
                </div>
                <div class="row text-center mt-3">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-danger mb-0">{{ $log->error_count ?? 0 }}</h4>
                            <small class="text-muted">{{translate('Errors')}}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info mb-0">
                            @if($log->total_items > 0)
                                {{ round((($log->success_count ?? 0) / $log->total_items) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </h4>
                        <small class="text-muted">{{translate('Success Rate')}}</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Quick Actions')}}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($log->status == 'error' && $log->type == 'bulk_csv')
                        <button class="btn btn-warning" onclick="retryImport({{ $log->id }})">
                            <i class="las la-redo"></i>
                            {{translate('Retry Import')}}
                        </button>
                    @endif
                    
                    <button class="btn btn-info" onclick="downloadLog({{ $log->id }})">
                        <i class="las la-download"></i>
                        {{translate('Download Log')}}
                    </button>
                    
                    <button class="btn btn-secondary" onclick="exportProducts({{ $log->id }})">
                        <i class="las la-file-export"></i>
                        {{translate('Export Products')}}
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Import Timeline')}}</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">{{translate('Import Started')}}</h6>
                            <p class="timeline-text">{{ $log->created_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    </div>
                    
                    @if($log->completed_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">{{translate('Import Completed')}}</h6>
                            <p class="timeline-text">{{ $log->completed_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
    
    <!-- Retry Import Modal -->
    <div class="modal fade" id="retry-import-modal" tabindex="-1" role="dialog" aria-labelledby="retry-import-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="retry-import-modal-label">{{translate('Retry Import')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="retry-import-form">
                        @csrf
                        <input type="hidden" name="log_id" id="retry-log-id">
                        
                        <div class="form-group">
                            <label>{{translate('Retry Options')}}</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="retry_failed_only" id="retry_failed_only" checked>
                                <label class="custom-control-label" for="retry_failed_only">
                                    {{translate('Retry only failed items')}}
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="update_existing" id="update_existing">
                                <label class="custom-control-label" for="update_existing">
                                    {{translate('Update existing products')}}
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>{{translate('Markup Percentage')}}</label>
                            <input type="number" class="form-control" name="markup_percentage" value="35" min="0" max="1000" step="0.01">
                            <small class="form-text text-muted">{{translate('Default markup to apply to products')}}</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="button" class="btn btn-primary" onclick="confirmRetryImport()">{{translate('Retry Import')}}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function retryImport(logId) {
            $('#retry-log-id').val(logId);
            $('#retry-import-modal').modal('show');
        }

        function confirmRetryImport() {
            var formData = new FormData($('#retry-import-form')[0]);
            
            $.ajax({
                url: '{{ route("alibaba.import-logs.retry") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success) {
                        AIZ.plugins.notify('success', response.message);
                        $('#retry-import-modal').modal('hide');
                        location.reload();
                    } else {
                        AIZ.plugins.notify('error', response.message);
                    }
                },
                error: function() {
                    AIZ.plugins.notify('error', '{{ translate("Failed to retry import") }}');
                }
            });
        }

        function downloadLog(logId) {
            window.open('{{ route("alibaba.import-logs.download", ":id") }}'.replace(':id', logId), '_blank');
        }

        function exportProducts(logId) {
            window.open('{{ route("alibaba.import-logs.export-products", ":id") }}'.replace(':id', logId), '_blank');
        }
    </script>
    
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-marker {
            position: absolute;
            left: -35px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        
        .timeline-content {
            padding-left: 15px;
        }
        
        .timeline-title {
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .timeline-text {
            margin-bottom: 0;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
@endsection