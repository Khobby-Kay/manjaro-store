<div class="activity-details">
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-muted">{{translate('User Information')}}</h6>
            <div class="d-flex align-items-center mb-3">
                <img src="{{ uploaded_asset($activity->user->avatar_original ?? '') }}" 
                     class="rounded-circle" width="48" height="48"
                     onerror="this.src='{{ static_asset('assets/img/avatar-place.png') }}'">
                <div class="ms-3">
                    <h6 class="mb-0">{{ $activity->user->name ?? 'Unknown User' }}</h6>
                    <small class="text-muted">{{ $activity->user->email ?? 'N/A' }}</small><br>
                    <span class="badge badge-{{ $activity->user->user_type == 'admin' ? 'danger' : 'primary' }}">
                        {{ $activity->user->user_type ?? 'User' }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-muted">{{translate('Action Details')}}</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>{{translate('Action')}}:</strong></td>
                    <td>
                        <span class="badge badge-{{ $activity->actionBadgeClass }}">
                            {{ $activity->action }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>{{translate('Type')}}:</strong></td>
                    <td>
                        <span class="badge badge-{{ $activity->action_type == 'critical' ? 'danger' : ($activity->action_type == 'warning' ? 'warning' : ($activity->action_type == 'success' ? 'success' : 'info')) }}">
                            {{ ucfirst($activity->action_type) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>{{translate('Status')}}:</strong></td>
                    <td>
                        <span class="badge badge-{{ $activity->status >= 400 ? 'danger' : 'success' }}">
                            {{ $activity->status ?? 'N/A' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>{{translate('Duration')}:</strong></td>
                    <td>{{ $activity->duration ?? 'N/A' }} ms</td>
                </tr>
            </table>
        </div>
    </div>
    
    <hr>
    
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-muted">{{translate('Request Information')}}</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>{{translate('Method')}}:</strong></td>
                    <td><code>{{ $activity->method ?? 'N/A' }}</code></td>
                </tr>
                <tr>
                    <td><strong>{{translate('URL')}}:</strong></td>
                    <td><small class="text-break">{{ $activity->url ?? 'N/A' }}</small></td>
                </tr>
                <tr>
                    <td><strong>{{translate('IP Address')}}:</strong></td>
                    <td><code>{{ $activity->ip_address ?? 'N/A' }}</code></td>
                </tr>
                <tr>
                    <td><strong>{{translate('Location')}}:</strong></td>
                    <td>{{ $activity->location ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-muted">{{translate('Device Information')}}</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>{{translate('Device Type')}}:</strong></td>
                    <td>{{ $activity->deviceInfo }}</td>
                </tr>
                <tr>
                    <td><strong>{{translate('Browser')}}:</strong></td>
                    <td>{{ $activity->browser ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>{{translate('Operating System')}}:</strong></td>
                    <td>{{ $activity->os ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>{{translate('User Agent')}}:</strong></td>
                    <td><small class="text-break">{{ Str::limit($activity->user_agent ?? 'N/A', 100) }}</small></td>
                </tr>
            </table>
        </div>
    </div>
    
    @if($activity->description)
    <hr>
    <div class="row">
        <div class="col-12">
            <h6 class="text-muted">{{translate('Description')}}</h6>
            <p class="mb-0">{{ $activity->description }}</p>
        </div>
    </div>
    @endif
    
    @if($activity->request_data)
    <hr>
    <div class="row">
        <div class="col-12">
            <h6 class="text-muted">{{translate('Request Data')}}</h6>
            <pre class="bg-light p-2 rounded"><code>{{ json_encode($activity->request_data, JSON_PRETTY_PRINT) }}</code></pre>
        </div>
    </div>
    @endif
    
    <hr>
    <div class="row">
        <div class="col-12">
            <h6 class="text-muted">{{translate('Timestamp')}}</h6>
            <p class="mb-0">
                <strong>{{translate('Created')}}:</strong> {{ $activity->formattedCreatedAt }}<br>
                <strong>{{translate('Time Ago')}}:</strong> {{ $activity->timeAgo }}
            </p>
        </div>
    </div>
</div>
