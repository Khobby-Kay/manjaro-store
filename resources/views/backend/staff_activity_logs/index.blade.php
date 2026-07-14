@extends('backend.layouts.app')

@section('title', translate('Staff Activity Logs'))

@section('content')
<!-- Header Section with Gradient -->
<div class="dashboard-box overflow-hidden mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="d-flex justify-content-between align-items-center p-4">
        <div class="d-flex align-items-center">
            <div class="mr-4">
                <div class="bg-white rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="las la-list fs-28 text-primary"></i>
                </div>
            </div>
            <div>
                <h3 class="fs-20 fw-600 mb-1 text-white">{{ translate('Staff Activity Logs') }}</h3>
                <p class="fs-14 text-white mb-0" style="opacity: 0.8;">{{ translate('Comprehensive activity tracking and monitoring system') }}</p>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <div class="mr-3 text-white text-right">
                <div class="fs-12 text-white" style="opacity: 0.8;">{{ translate('Advanced filtering') }}</div>
                <div class="fs-14 fw-600">{{ translate('Real-time logs') }}</div>
            </div>
            <div>
                <a href="{{ route('staff_activity_logs.dashboard') }}" class="btn btn-white btn-lg px-4 py-2 fw-600 shadow-sm mr-2">
                    <i class="las la-chart-bar mr-2"></i>
                    {{ translate('Dashboard') }}
                </a>
                <a href="{{ route('staff_activity_logs.export') }}" class="btn btn-white btn-lg px-4 py-2 fw-600 shadow-sm">
                    <i class="las la-download mr-2"></i>
                    {{ translate('Export') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-box bg-white overflow-hidden">
    <div class="p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <h5 class="mb-0 h6 text-white">{{ translate('Filter Logs') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('staff_activity_logs.index') }}" method="GET">
            <div class="row">
                <div class="col-md-2">
                    <label>{{translate('Staff')}}</label>
                    <select class="form-control aiz-selectpicker" name="staff_id" data-live-search="true">
                        <option value="">{{translate('All Staff')}}</option>
                        @foreach($staffList as $id => $name)
                            <option value="{{ $id }}" {{ request('staff_id') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>{{translate('Module')}}</label>
                    <select class="form-control aiz-selectpicker" name="module" data-live-search="true">
                        <option value="">{{translate('All Modules')}}</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                {{ ucfirst($module) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>{{translate('Action')}}</label>
                    <select class="form-control aiz-selectpicker" name="action" data-live-search="true">
                        <option value="">{{translate('All Actions')}}</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst($action) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>{{translate('Status')}}</label>
                    <select class="form-control aiz-selectpicker" name="status">
                        <option value="">{{translate('All Status')}}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>{{translate('Date From')}}</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label>{{translate('Date To')}}</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-8">
                    <label>{{translate('Search Description')}}</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="{{translate('Search in descriptions...')}}">
                </div>
                <div class="col-md-4">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-search"></i>
                            <span>{{translate('Filter')}}</span>
                        </button>
                        <a href="{{ route('staff_activity_logs.index') }}" class="btn btn-secondary">
                            <i class="las la-times"></i>
                            <span>{{translate('Clear')}}</span>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="dashboard-box bg-white overflow-hidden mt-4">
    <div class="p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <h5 class="mb-0 h6 text-white">{{ translate('Activity Logs') }}</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered aiz-table">
                <thead>
                    <tr style="background-color: rgba(102, 126, 234, 0.1);">
                        <th>{{ translate('ID') }}</th>
                        <th>{{ translate('Staff') }}</th>
                        <th>{{ translate('Action') }}</th>
                        <th>{{ translate('Module') }}</th>
                        <th>{{ translate('Description') }}</th>
                        <th>{{ translate('IP Address') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th>{{ translate('Duration') }}</th>
                        <th>{{ translate('Date') }}</th>
                        <th>{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>
                            @if($log->staff)
                                <a href="{{ route('staff_activity_logs.staff', $log->staff_id) }}">
                                    {{ $log->staff_name }}
                                </a>
                            @else
                                {{ $log->staff_name }}
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $log->action == 'error' ? 'danger' : 'info' }}">
                                {{ $log->formatted_action }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('staff_activity_logs.module', $log->module) }}">
                                {{ $log->formatted_module }}
                            </a>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;" title="{{ $log->description }}">
                                {{ $log->description }}
                            </div>
                        </td>
                        <td>{{ $log->ip_address }}</td>
                        <td>
                            <span class="badge badge-{{ $log->status_color }}">
                                {{ ucfirst($log->response_status) }}
                            </span>
                        </td>
                        <td>{{ $log->duration }}</td>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('staff_activity_logs.show', $log->id) }}" class="btn btn-sm btn-info">
                                <i class="las la-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="aiz-pagination">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Clear Old Logs Modal -->
<div class="modal fade" id="clear-logs-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('Clear Old Logs')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('staff_activity_logs.clear_old') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{translate('Clear logs older than (days)')}}</label>
                        <input type="number" class="form-control" name="days" value="90" min="1" max="365">
                        <small class="form-text text-muted">{{translate('This will permanently delete logs older than the specified number of days.')}}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-danger">{{translate('Clear Logs')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Initialize selectpicker
        $('.aiz-selectpicker').selectpicker();

        // Auto-submit form on select change
        $('select[name="staff_id"], select[name="module"], select[name="action"], select[name="status"]').change(function() {
            $(this).closest('form').submit();
        });
    });
</script>

<style>
.dashboard-box {
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.dashboard-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.15);
}

.badge {
    font-size: 11px;
    padding: 5px 8px;
    border-radius: 6px;
}

.btn-white {
    background: white;
    color: #667eea;
    border: 2px solid white;
    transition: all 0.3s ease;
}

.btn-white:hover {
    background: rgba(255,255,255,0.9);
    color: #667eea;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>
@endsection 