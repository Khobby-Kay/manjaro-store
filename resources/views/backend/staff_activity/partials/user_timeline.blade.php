<div class="user-timeline">
    <div class="timeline-header mb-4">
        <h6 class="text-muted">{{translate('Activity Timeline')}}</h6>
        <p class="mb-0">{{translate('Showing the last 50 activities for this user')}}</p>
    </div>
    
    @if($activities->count() > 0)
        <div class="timeline-container">
            @foreach($activities as $activity)
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <div class="marker-dot marker-{{ $activity->action_type }}"></div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h6 class="mb-1">{{ $activity->action }}</h6>
                            <small class="text-muted">{{ $activity->timeAgo }}</small>
                        </div>
                        <div class="timeline-body">
                            @if($activity->description)
                                <p class="mb-2">{{ $activity->description }}</p>
                            @endif
                            <div class="timeline-meta">
                                <span class="badge badge-{{ $activity->action_type == 'critical' ? 'danger' : ($activity->action_type == 'warning' ? 'warning' : ($activity->action_type == 'success' ? 'success' : 'info')) }}">
                                    {{ ucfirst($activity->action_type) }}
                                </span>
                                @if($activity->ip_address)
                                    <small class="text-muted ms-2">
                                        <i class="las la-map-marker"></i> {{ $activity->ip_address }}
                                    </small>
                                @endif
                                @if($activity->url)
                                    <small class="text-muted ms-2">
                                        <i class="las la-link"></i> {{ Str::limit($activity->url, 50) }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-4">
            <i class="las la-inbox la-3x text-muted mb-3"></i>
            <p class="text-muted">{{translate('No activity records found for this user')}}</p>
        </div>
    @endif
</div>

<style>
.timeline-container {
    position: relative;
    padding-left: 30px;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
}

.marker-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.marker-info { background-color: #17a2b8; }
.marker-success { background-color: #28a745; }
.marker-warning { background-color: #ffc107; }
.marker-critical { background-color: #dc3545; }

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #e9ecef;
}

.timeline-content:hover {
    background: #e9ecef;
    transition: background-color 0.2s ease;
}

.timeline-header h6 {
    color: #495057;
    font-weight: 600;
}

.timeline-meta {
    margin-top: 10px;
}

.timeline-meta .badge {
    font-size: 0.75rem;
}
</style>
