@extends('backend.layouts.app')

@section('title', translate('User Activity Report'))

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('User Activity Report')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('staff_activity_logs.dashboard') }}" class="btn btn-primary">
                <i class="las la-arrow-left"></i> {{translate('Back to Dashboard')}}
            </a>
            <a href="{{ route('staff_activity_logs.export') }}" class="btn btn-success">
                <i class="las la-download"></i> {{translate('Export Report')}}
            </a>
        </div>
    </div>
</div>

<!-- User Activity Summary -->
<div class="row">
    @forelse($userActivities ?? [] as $userActivity)
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    @if($userActivity->user && $userActivity->user->avatar_original)
                        <img src="{{ uploaded_asset($userActivity->user->avatar_original) }}" 
                             class="rounded-circle" width="48" height="48"
                             onerror="this.src='{{ static_asset('assets/img/avatar-place.png') }}'">
                    @else
                        <img src="{{ static_asset('assets/img/avatar-place.png') }}" 
                             class="rounded-circle" width="48" height="48">
                    @endif
                    <div class="ms-3">
                        <h5 class="mb-0">{{ $userActivity->user->name ?? 'Unknown User' }}</h5>
                        <small class="text-muted">{{ $userActivity->user->email ?? 'N/A' }}</small>
                    </div>
                    <div class="ms-auto">
                        @if($userActivity->user && $userActivity->user->user_type)
                            <span class="badge badge-{{ $userActivity->user->user_type == 'admin' ? 'danger' : 'primary' }}">
                                {{ ucfirst($userActivity->user->user_type) }}
                            </span>
                        @else
                            <span class="badge badge-secondary">Unknown</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-end">
                            <h4 class="text-primary mb-1">{{ $userActivity->total_activities ?? 0 }}</h4>
                            <small class="text-muted">{{translate('Total Activities')}}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <h4 class="text-success mb-1">{{ $userActivity->today_activities ?? 0 }}</h4>
                            <small class="text-muted">{{translate('Today')}}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <h4 class="text-warning mb-1">{{ $userActivity->critical_actions ?? 0 }}</h4>
                        <small class="text-muted">{{translate('Critical')}}</small>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">{{translate('Last Activity')}}</small><br>
                        @if($userActivity->last_activity)
                            <strong>{{ $userActivity->last_activity->created_at ? $userActivity->last_activity->created_at->diffForHumans() : 'Unknown' }}</strong>
                        @else
                            <strong class="text-muted">{{translate('Never')}}</strong>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">{{translate('First Seen')}}</small><br>
                        @if($userActivity->first_activity)
                            <strong>{{ $userActivity->first_activity->created_at ? $userActivity->first_activity->created_at->format('M d, Y') : 'Unknown' }}</strong>
                        @else
                            <strong class="text-muted">{{translate('Unknown')}}</strong>
                        @endif
                    </div>
                </div>
                
                <div class="mt-3">
                    @if($userActivity->user)
                        <a href="{{ route('staff_activity_logs.logs') }}?user_id={{ $userActivity->user->id }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="las la-eye"></i> {{translate('View Details')}}
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-info" 
                                onclick="viewUserTimeline({{ $userActivity->user->id }})">
                            <i class="las la-chart-line"></i> {{translate('Timeline')}}
                        </button>
                    @else
                        <span class="text-muted">{{translate('User data unavailable')}}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5">
            <i class="las la-users la-3x text-muted mb-3"></i>
            <h5 class="text-muted">{{translate('No user activity data available')}}</h5>
            <p class="text-muted">{{translate('Users will appear here once they start performing actions in the system.')}}</p>
        </div>
    </div>
    @endforelse
</div>

<!-- Activity Timeline Modal -->
<div class="modal fade" id="userTimelineModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('User Activity Timeline')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="userTimelineContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Close')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- No Data Message -->
@if(empty($userActivities))
<div class="text-center py-5">
    <i class="las la-users la-3x text-muted mb-3"></i>
    <h5 class="text-muted">{{translate('No user activity data available')}}</h5>
    <p class="text-muted">{{translate('Users will appear here once they start performing actions in the system.')}}</p>
</div>
@endif
@endsection

@section('script')
<script>
function viewUserTimeline(userId) {
    // Load user timeline via AJAX
    $.get('{{ route("staff_activity_logs.users") }}/' + userId + '/timeline', function(data) {
        $('#userTimelineContent').html(data);
        $('#userTimelineModal').modal('show');
    });
}

// Auto-refresh user report every 2 minutes
setInterval(function() {
    if (window.location.pathname.includes('users')) {
        location.reload();
    }
}, 120000);
</script>
@endsection
