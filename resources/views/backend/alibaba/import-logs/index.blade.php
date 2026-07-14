@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Import Logs')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <button class="btn btn-danger" onclick="clearOldLogs()">
                <i class="las la-trash"></i>
                <span>{{translate('Clear Old Logs')}}</span>
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-md-0 h6">{{translate('All Import Logs')}}</h5>
        </div>
        <div class="col-md-3">
            <form class="" id="sort_logs" action="" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ translate('Type log ID & Enter') }}">
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <form class="" id="sort_logs" action="" method="GET">
            <div class="row gutters-5 mb-3">
                <div class="col-md-2">
                    <select class="form-control aiz-selectpicker" data-live-search="true" name="type" id="type">
                        <option value="">{{translate('All Types')}}</option>
                        <option value="single" @isset($type) @if($type == 'single') selected @endif @endisset>{{translate('Single Import')}}</option>
                        <option value="bulk_csv" @isset($type) @if($type == 'bulk_csv') selected @endif @endisset>{{translate('Bulk CSV')}}</option>
                        <option value="bulk_supplier" @isset($type) @if($type == 'bulk_supplier') selected @endif @endisset>{{translate('Bulk Supplier')}}</option>
                        <option value="trending" @isset($type) @if($type == 'trending') selected @endif @endisset>{{translate('Trending')}}</option>
                        <option value="discovery" @isset($type) @if($type == 'discovery') selected @endif @endisset>{{translate('Discovery')}}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control aiz-selectpicker" name="status" id="status">
                        <option value="">{{translate('All Status')}}</option>
                        <option value="success" @isset($status) @if($status == 'success') selected @endif @endisset>{{translate('Success')}}</option>
                        <option value="error" @isset($status) @if($status == 'error') selected @endif @endisset>{{translate('Error')}}</option>
                        <option value="partial" @isset($status) @if($status == 'partial') selected @endif @endisset>{{translate('Partial')}}</option>
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
                    <th>#</th>
                    <th>{{translate('Log ID')}}</th>
                    <th>{{translate('Type')}}</th>
                    <th>{{translate('Status')}}</th>
                    <th>{{translate('Total Items')}}</th>
                    <th>{{translate('Success')}}</th>
                    <th>{{translate('Failed')}}</th>
                    <th>{{translate('Duration')}}</th>
                    <th>{{translate('Started At')}}</th>
                    <th>{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @if($logs->count() > 0)
                    @foreach($logs as $key => $log)
                    <tr>
                        <td>{{ ($key+1) + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                        <td>
                            <a href="{{route('alibaba.import-logs.show', $log->id)}}">
                                #{{ $log->id }}
                            </a>
                        </td>
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
                        <td>{{ $log->total_items ?? 0 }}</td>
                        <td>
                            <span class="text-success">{{ $log->success_count ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="text-danger">{{ $log->error_count ?? 0 }}</span>
                        </td>
                        <td>
                            @if($log->duration)
                                {{ $log->duration }}s
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('alibaba.import-logs.show', $log->id)}}" title="{{ translate('View Details') }}">
                                <i class="las la-eye"></i>
                            </a>
                            @if($log->status == 'error' && $log->type == 'bulk_csv')
                                <button class="btn btn-soft-warning btn-icon btn-circle btn-sm" onclick="retryImport({{ $log->id }})" title="{{ translate('Retry Import') }}">
                                    <i class="las la-redo"></i>
                                </button>
                            @endif
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('alibaba.import-logs.destroy', $log->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center">
                            <p class="text-muted">{{ translate('No import logs found.') }}</p>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $logs->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
    
    <!-- Clear Old Logs Modal -->
    <div class="modal fade" id="clear-logs-modal" tabindex="-1" role="dialog" aria-labelledby="clear-logs-modal-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clear-logs-modal-label">{{translate('Clear Old Logs')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="clear-logs-form">
                        @csrf
                        <div class="form-group">
                            <label>{{translate('Delete logs older than')}} <span class="text-danger">*</span></label>
                            <select class="form-control" name="days" required>
                                <option value="7">{{translate('7 days')}}</option>
                                <option value="30" selected>{{translate('30 days')}}</option>
                                <option value="90">{{translate('90 days')}}</option>
                                <option value="180">{{translate('180 days')}}</option>
                                <option value="365">{{translate('1 year')}}</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>{{translate('Log Types to Delete')}}</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="types[]" value="success" id="type_success" checked>
                                <label class="custom-control-label" for="type_success">
                                    {{translate('Success logs')}}
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="types[]" value="error" id="type_error">
                                <label class="custom-control-label" for="type_error">
                                    {{translate('Error logs')}}
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="types[]" value="partial" id="type_partial">
                                <label class="custom-control-label" for="type_partial">
                                    {{translate('Partial logs')}}
                                </label>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="las la-exclamation-triangle"></i>
                            {{translate('This action cannot be undone. Are you sure you want to delete old logs?')}}
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="button" class="btn btn-danger" onclick="confirmClearLogs()">{{translate('Clear Logs')}}</button>
                </div>
            </div>
        </div>
    </div>
    
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
        $(document).ready(function() {
            // Simple script for search functionality
            $('#search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });

        function clearOldLogs() {
            $('#clear-logs-modal').modal('show');
        }

        function confirmClearLogs() {
            var formData = new FormData($('#clear-logs-form')[0]);
            
            $.ajax({
                url: '{{ route("alibaba.import-logs.clear-old") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success) {
                        AIZ.plugins.notify('success', response.message);
                        $('#clear-logs-modal').modal('hide');
                        location.reload();
                    } else {
                        AIZ.plugins.notify('error', response.message);
                    }
                },
                error: function() {
                    AIZ.plugins.notify('error', '{{ translate("Failed to clear logs") }}');
                }
            });
        }

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
    </script>
@endsection