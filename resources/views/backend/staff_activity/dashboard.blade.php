@extends('backend.layouts.app')

@section('title', translate('Staff Activity Dashboard'))

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Staff Activity Dashboard')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('staff_activity_logs.logs') }}" class="btn btn-primary">
                <i class="las la-list"></i> {{translate('View All Logs')}}
            </a>
            <a href="{{ route('staff_activity_logs.export') }}" class="btn btn-success">
                <i class="las la-download"></i> {{translate('Export Data')}}
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
                        <div class="avatar avatar-lg bg-primary">
                            <i class="las la-users la-2x text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">{{ $totalUsers ?? 0 }}</h4>
                        <p class="mb-0 text-muted">{{translate('Active Users')}}</p>
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
                        <div class="avatar avatar-lg bg-success">
                            <i class="las la-clock la-2x text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">{{ $todayActivities ?? 0 }}</h4>
                        <p class="mb-0 text-muted">{{translate('Today\'s Activities')}}</p>
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
                        <div class="avatar avatar-lg bg-warning">
                            <i class="las la-exclamation-triangle la-2x text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">{{ $criticalActions ?? 0 }}</h4>
                        <p class="mb-0 text-muted">{{translate('Critical Actions')}}</p>
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
                        <div class="avatar avatar-lg bg-info">
                            <i class="las la-chart-line la-2x text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-1">{{ $totalActivities ?? 0 }}</h4>
                        <p class="mb-0 text-muted">{{translate('Total Activities')}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Recent Activities')}}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{translate('User')}}</th>
                                <th>{{translate('Action')}}</th>
                                <th>{{translate('Details')}}</th>
                                <th>{{translate('Time')}}</th>
                                <th>{{translate('IP Address')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities ?? [] as $activity)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ uploaded_asset($activity->user->avatar_original ?? '') }}" 
                                             class="rounded-circle" width="32" height="32"
                                             onerror="this.src='{{ static_asset('assets/img/avatar-place.png') }}'">
                                        <span class="ms-2">{{ $activity->user->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $activity->action_type == 'critical' ? 'danger' : ($activity->action_type == 'warning' ? 'warning' : 'info') }}">
                                        {{ $activity->action ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($activity->description ?? '', 50) }}</td>
                                <td>{{ $activity->created_at ? $activity->created_at->diffForHumans() : 'Unknown' }}</td>
                                <td><code>{{ $activity->ip_address ?? 'N/A' }}</code></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    {{translate('No recent activities found')}}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(isset($recentActivities) && count($recentActivities) > 0)
                <div class="text-center mt-3">
                    <a href="{{ route('staff_activity_logs.logs') }}" class="btn btn-outline-primary">
                        {{translate('View All Activities')}}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Top Active Users -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Top Active Users')}}</h5>
            </div>
            <div class="card-body">
                @forelse($topUsers ?? [] as $user)
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ uploaded_asset($user->avatar_original ?? '') }}" 
                         class="rounded-circle" width="40" height="40"
                         onerror="this.src='{{ static_asset('assets/img/avatar-place.png') }}'">
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-0">{{ $user->name ?? 'Unknown' }}</h6>
                        <small class="text-muted">{{ $user->activity_count ?? 0 }} activities</small>
                    </div>
                    <span class="badge badge-primary">{{ $user->role ?? 'User' }}</span>
                </div>
                @empty
                <p class="text-muted text-center">{{translate('No user data available')}}</p>
                @endforelse
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Quick Actions')}}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('staff_activity_logs.users') }}" class="btn btn-outline-info">
                        <i class="las la-users"></i> {{translate('User Activity Report')}}
                    </a>
                    <button type="button" class="btn btn-outline-warning" onclick="clearOldLogs()">
                        <i class="las la-trash"></i> {{translate('Clear Old Logs')}}
                    </button>
                    <a href="{{ route('staff_activity_logs.export') }}" class="btn btn-outline-success">
                        <i class="las la-file-export"></i> {{translate('Export Data')}}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function clearOldLogs() {
    if (confirm('{{translate("Are you sure you want to clear old logs? This action cannot be undone.")}}')) {
        $.ajax({
            url: '{{ route("staff_activity_logs.clear") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    AIZ.plugins.notify('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    AIZ.plugins.notify('error', response.message);
                }
            },
            error: function() {
                AIZ.plugins.notify('error', '{{translate("An error occurred while clearing logs.")}}');
            }
        });
    }
}

// Auto-refresh dashboard every 30 seconds
setInterval(function() {
    // Refresh only the statistics and recent activities
    location.reload();
}, 30000);
</script>
@endsection
