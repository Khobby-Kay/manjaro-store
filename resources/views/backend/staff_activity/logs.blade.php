@extends('backend.layouts.app')

@section('title', translate('Staff Activity Logs'))

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Staff Activity Logs')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('staff_activity_logs.dashboard') }}" class="btn btn-primary">
                <i class="las la-arrow-left"></i> {{translate('Back to Dashboard')}}
            </a>
            <a href="{{ route('staff_activity_logs.export') }}" class="btn btn-success">
                <i class="las la-download"></i> {{translate('Export Data')}}
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Filter Activities')}}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('staff_activity_logs.logs') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <label>{{translate('User')}}</label>
                    <select name="user_id" class="form-control aiz-selectpicker" data-live-search="true">
                        <option value="">{{translate('All Users')}}</option>
                        @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label>{{translate('Action Type')}}</label>
                    <select name="action_type" class="form-control aiz-selectpicker">
                        <option value="">{{translate('All Actions')}}</option>
                        <option value="login" {{ request('action_type') == 'login' ? 'selected' : '' }}>{{translate('Login')}}</option>
                        <option value="logout" {{ request('action_type') == 'logout' ? 'selected' : '' }}>{{translate('Logout')}}</option>
                        <option value="create" {{ request('action_type') == 'create' ? 'selected' : '' }}>{{translate('Create')}}</option>
                        <option value="update" {{ request('action_type') == 'update' ? 'selected' : '' }}>{{translate('Update')}}</option>
                        <option value="delete" {{ request('action_type') == 'delete' ? 'selected' : '' }}>{{translate('Delete')}}</option>
                        <option value="critical" {{ request('action_type') == 'critical' ? 'selected' : '' }}>{{translate('Critical')}}</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label>{{translate('Date From')}}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-3">
                    <label>{{translate('Date To')}}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-search"></i> {{translate('Filter')}}
                    </button>
                    <a href="{{ route('staff_activity_logs.logs') }}" class="btn btn-secondary">
                        <i class="las la-times"></i> {{translate('Clear Filters')}}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Activity Logs Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Activity Logs')}}</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{translate('User')}}</th>
                        <th>{{translate('Action')}}</th>
                        <th>{{translate('Description')}}</th>
                        <th>{{translate('IP Address')}}</th>
                        <th>{{translate('User Agent')}}</th>
                        <th>{{translate('Timestamp')}}</th>
                        <th>{{translate('Actions')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities ?? [] as $key => $activity)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ uploaded_asset($activity->user->avatar_original ?? '') }}" 
                                     class="rounded-circle" width="32" height="32"
                                     onerror="this.src='{{ static_asset('assets/img/avatar-place.png') }}'">
                                <div class="ms-2">
                                    <strong>{{ $activity->user->name ?? 'Unknown' }}</strong><br>
                                    <small class="text-muted">{{ $activity->user->email ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $activity->action_type == 'critical' ? 'danger' : ($activity->action_type == 'warning' ? 'warning' : 'info') }}">
                                {{ $activity->action ?? 'Unknown' }}
                            </span>
                        </td>
                        <td>
                            <div class="text-wrap" style="max-width: 300px;">
                                {{ $activity->description ?? 'No description' }}
                            </div>
                        </td>
                        <td><code>{{ $activity->ip_address ?? 'N/A' }}</code></td>
                        <td>
                            <small class="text-muted" title="{{ $activity->user_agent ?? 'N/A' }}">
                                {{ Str::limit($activity->user_agent ?? 'N/A', 50) }}
                            </small>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $activity->created_at ? $activity->created_at->format('M d, Y') : 'Unknown' }}</strong><br>
                                <small class="text-muted">{{ $activity->created_at ? $activity->created_at->format('H:i:s') : 'Unknown' }}</small>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="viewActivityDetails({{ $activity->id }})">
                                <i class="las la-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="las la-inbox la-3x mb-3"></i>
                            <p>{{translate('No activity logs found')}}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($activities) && $activities->hasPages())
        <div class="aiz-pagination">
            {{ $activities->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Activity Details Modal -->
<div class="modal fade" id="activityDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('Activity Details')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="activityDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Close')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function viewActivityDetails(activityId) {
    // Load activity details via AJAX
    $.get('{{ route("staff_activity_logs.logs") }}/' + activityId + '/details', function(data) {
        $('#activityDetailsContent').html(data);
        $('#activityDetailsModal').modal('show');
    });
}

// Auto-refresh logs every 60 seconds
setInterval(function() {
    if (window.location.pathname.includes('logs')) {
        location.reload();
    }
}, 60000);
</script>
@endsection
