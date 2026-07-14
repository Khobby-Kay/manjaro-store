@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Mobile Money Callback Monitor') }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Callback Status</h6>
                        <div class="alert alert-info">
                            <strong>Callback URL:</strong> {{ url('/mobile_money/callback') }}<br>
                            <strong>Admin Test URL:</strong> {{ url('/mobile_money/admin/callback/test') }}<br>
                            <strong>Monitor URL:</strong> {{ url('/mobile_money/admin/callback/monitor') }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Quick Actions</h6>
                        <a href="{{ route('mobile_money.admin.callback.test') }}" class="btn btn-primary btn-sm">
                            <i class="las la-play"></i> Test Callback
                        </a>
                        <a href="{{ route('mobile_money.admin.callback.monitor') }}" class="btn btn-info btn-sm">
                            <i class="las la-refresh"></i> Refresh Monitor
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_callbacks as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    @if($log->user)
                                        {{ $log->user->name }} (ID: {{ $log->user_id }})
                                    @else
                                        System (ID: {{ $log->user_id }})
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $log->action_type == 'success' ? 'success' : ($log->action_type == 'error' ? 'danger' : 'info') }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td>{{ $log->description }}</td>
                                <td>{{ $log->ip_address }}</td>
                                <td>{{ Str::limit($log->user_agent, 50) }}</td>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No callback logs found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <h6>Callback Testing</h6>
                    <p>Use the test button above to simulate a callback and verify the system is working correctly.</p>
                    
                    <div class="alert alert-warning">
                        <strong>Note:</strong> Callbacks from external payment providers will be logged with User ID 1 (Admin) when no authentication is present.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
