@extends('backend.layouts.app')

@section('title', translate('Staff Activity Dashboard'))

@section('content')
<!-- Header Section with Gradient -->
<div class="dashboard-box overflow-hidden mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="d-flex justify-content-between align-items-center p-4">
        <div class="d-flex align-items-center">
            <div class="mr-4">
                <div class="bg-white rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="las la-user-clock fs-28 text-primary"></i>
                </div>
            </div>
            <div>
                <h3 class="fs-20 fw-600 mb-1 text-white">{{ translate('Staff Activity Dashboard') }}</h3>
                <p class="fs-14 text-white mb-0" style="opacity: 0.8;">{{ translate('Comprehensive monitoring and analytics for staff activities') }}</p>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <div class="mr-3 text-white text-right">
                <div class="fs-12 text-white" style="opacity: 0.8;">{{ translate('Real-time analytics') }}</div>
                <div class="fs-14 fw-600">{{ translate('Live monitoring') }}</div>
            </div>
            <a href="{{ route('staff_activity_logs.index') }}" class="btn btn-white btn-lg px-4 py-2 fw-600 shadow-sm">
                <i class="las la-list mr-2"></i>
                {{ translate('View All Logs') }}
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards with Gradient Theme -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-box overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-white rounded-circle p-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="las la-users fs-20 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 text-white">{{ translate('Active Staff') }}</h6>
                        <h4 class="mb-0 text-white fw-600">{{ $staffActivity->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-box overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-white rounded-circle p-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="las la-chart-line fs-20 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 text-white">{{ translate('Total Actions') }}</h6>
                        <h4 class="mb-0 text-white fw-600">{{ $activityStats->sum('total_actions') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-box overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-white rounded-circle p-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="las la-cube fs-20 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 text-white">{{ translate('Active Modules') }}</h6>
                        <h4 class="mb-0 text-white fw-600">{{ $moduleActivity->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="dashboard-box overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-white rounded-circle p-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="las la-clock fs-20 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 text-white">{{ translate('Today Actions') }}</h6>
                        <h4 class="mb-0 text-white fw-600">{{ $activityStats->where('date', date('Y-m-d'))->first()->total_actions ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8 mb-3">
        <div class="dashboard-box bg-white overflow-hidden">
            <div class="p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="mb-0 h6 text-white">{{ translate('Activity Trend (Last 30 Days)') }}</h5>
            </div>
            <div class="p-4">
                <canvas id="activityChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-3">
        <div class="dashboard-box bg-white overflow-hidden">
            <div class="p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="mb-0 h6 text-white">{{ translate('Top Active Staff') }}</h5>
            </div>
            <div class="p-4">
                @foreach($topStaff as $staff)
                <div class="d-flex align-items-center mb-3 p-3 rounded" style="background-color: rgba(102, 126, 234, 0.1);">
                    <div class="flex-shrink-0">
                        <div class="bg-primary rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="las la-user text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 fw-600">{{ $staff->staff_name }}</h6>
                        <small class="text-muted">{{ $staff->activity_count }} actions</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Module Activity and Recent Activities -->
<div class="row mb-4">
    <div class="col-lg-6 mb-3">
        <div class="dashboard-box bg-white overflow-hidden">
            <div class="p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="mb-0 h6 text-white">{{ translate('Most Active Modules') }}</h5>
            </div>
            <div class="p-4">
                <canvas id="moduleChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-3">
        <div class="dashboard-box bg-white overflow-hidden">
            <div class="p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="mb-0 h6 text-white">{{ translate('Recent Activities') }}</h5>
            </div>
            <div class="p-4">
                <div class="timeline">
                    @foreach($recentActivities as $activity)
                    <div class="timeline-item">
                        <div class="timeline-marker" style="background-color: {{ $activity->status_color == 'success' ? '#28a745' : ($activity->status_color == 'error' ? '#dc3545' : '#007bff') }};"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1 fw-600">{{ $activity->staff_name }}</h6>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $activity->description }}</p>
                            <small class="text-muted">
                                <span class="badge badge-{{ $activity->status_color }}">{{ $activity->formatted_action }}</span>
                                in <span class="badge badge-info">{{ $activity->formatted_module }}</span>
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Staff Activity Summary -->
<div class="dashboard-box bg-white overflow-hidden">
    <div class="p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <h5 class="mb-0 h6 text-white">{{ translate('Staff Activity Summary') }}</h5>
    </div>
    <div class="p-4">
        <div class="table-responsive">
            <table class="table table-bordered aiz-table">
                <thead>
                    <tr style="background-color: rgba(102, 126, 234, 0.1);">
                        <th>{{ translate('Staff') }}</th>
                        <th>{{ translate('Total Actions') }}</th>
                        <th>{{ translate('Logins') }}</th>
                        <th>{{ translate('Creates') }}</th>
                        <th>{{ translate('Updates') }}</th>
                        <th>{{ translate('Deletes') }}</th>
                        <th>{{ translate('Last Activity') }}</th>
                        <th>{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffActivity as $staff)
                    <tr>
                        <td class="fw-600">{{ $staff->staff_name }}</td>
                        <td><span class="badge badge-primary">{{ $staff->total_actions }}</span></td>
                        <td><span class="badge badge-success">{{ $staff->logins }}</span></td>
                        <td><span class="badge badge-info">{{ $staff->creates }}</span></td>
                        <td><span class="badge badge-warning">{{ $staff->updates }}</span></td>
                        <td><span class="badge badge-danger">{{ $staff->deletes }}</span></td>
                        <td>{{ $staff->last_activity ? $staff->last_activity->diffForHumans() : 'Never' }}</td>
                        <td>
                            <a href="{{ route('staff_activity_logs.staff', $staff->staff_id) }}" class="btn btn-sm btn-primary">
                                <i class="las la-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Activity Trend Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityData = @json($activityStats);
    
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: activityData.map(item => item.date),
            datasets: [{
                label: 'Total Actions',
                data: activityData.map(item => item.total_actions),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.2)',
                tension: 0.4,
                borderWidth: 3
            }, {
                label: 'Logins',
                data: activityData.map(item => item.logins),
                borderColor: '#764ba2',
                backgroundColor: 'rgba(118, 75, 162, 0.2)',
                tension: 0.4,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                }
            }
        }
    });

    // Module Activity Chart
    const moduleCtx = document.getElementById('moduleChart').getContext('2d');
    const moduleData = @json($topModules);
    
    const moduleChart = new Chart(moduleCtx, {
        type: 'doughnut',
        data: {
            labels: moduleData.map(item => item.module),
            datasets: [{
                data: moduleData.map(item => item.activity_count),
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c',
                    '#4facfe',
                    '#00f2fe',
                    '#43e97b',
                    '#38f9d7',
                    '#fa709a',
                    '#fee140'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        },
                        usePointStyle: true
                    }
                }
            }
        }
    });
});
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
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #667eea;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

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
</style>
@endsection 