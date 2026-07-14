<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffActivityLogController extends Controller
{
    public function __construct()
    {
        // Permission middleware
        $this->middleware(['permission:view_staff_activity'])->only(['index', 'logs', 'users']);
        $this->middleware(['permission:view_staff_activity_dashboard'])->only(['index']);
        $this->middleware(['permission:view_staff_activity_logs'])->only(['logs']);
        $this->middleware(['permission:view_staff_activity_users'])->only(['users']);
        $this->middleware(['permission:export_staff_activity'])->only(['export']);
        $this->middleware(['permission:clear_staff_activity_logs'])->only(['clearLogs']);
        $this->middleware(['permission:view_staff_activity'])->only(['getActivityDetails', 'getUserTimeline']);
    }
    /**
     * Display the staff activity dashboard
     */
    public function index()
    {
        // Get dashboard statistics
        $totalUsers = User::where('user_type', '!=', 'customer')->count();
        $todayActivities = StaffActivityLog::today()->count();
        $criticalActions = StaffActivityLog::critical()->count();
        $totalActivities = StaffActivityLog::count();

        // Get recent activities
        $recentActivities = StaffActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get top active users
        $topUsers = StaffActivityLog::select('user_id')
            ->selectRaw('COUNT(*) as activity_count')
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(5)
            ->with('user')
            ->get();

        return view('backend.staff_activity.dashboard', compact(
            'totalUsers',
            'todayActivities',
            'criticalActions',
            'totalActivities',
            'recentActivities',
            'topUsers'
        ));
    }

    /**
     * Display detailed activity logs with filtering
     */
    public function logs(Request $request)
    {
        $query = StaffActivityLog::with('user');

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action type
        if ($request->has('action_type') && $request->action_type) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get users for filter dropdown
        $users = User::where('user_type', '!=', 'customer')
            ->orderBy('name')
            ->get();

        return view('backend.staff_activity.logs', compact('activities', 'users'));
    }

    /**
     * Display user activity report
     */
    public function users()
    {
        // Get all users with activity statistics
        $userActivities = User::where('user_type', '!=', 'customer')
            ->withCount(['staffActivityLogs as total_activities' => function($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
            }])
            ->withCount(['staffActivityLogs as today_activities' => function($query) {
                $query->whereDate('created_at', Carbon::today());
            }])
            ->withCount(['staffActivityLogs as critical_actions' => function($query) {
                $query->where('action_type', 'critical');
            }])
            ->with(['staffActivityLogs' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->get()
            ->map(function($user) {
                // Create a structured object for the view
                $userActivity = new \stdClass();
                $userActivity->user = $user;
                $userActivity->total_activities = $user->total_activities ?? 0;
                $userActivity->today_activities = $user->today_activities ?? 0;
                $userActivity->critical_actions = $user->critical_actions ?? 0;
                $userActivity->last_activity = $user->staffActivityLogs->first();
                $userActivity->first_activity = $user->staffActivityLogs->last();
                
                return $userActivity;
            });

        return view('backend.staff_activity.users', compact('userActivities'));
    }

    /**
     * Export activity data
     */
    public function export(Request $request)
    {
        $query = StaffActivityLog::with('user');

        // Apply filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action_type') && $request->action_type) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'staff_activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'User', 'Action', 'Action Type', 'Description', 
                'IP Address', 'URL', 'Method', 'Status', 'Created At'
            ]);

            // CSV data
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->user->name ?? 'Unknown',
                    $activity->action,
                    $activity->action_type,
                    $activity->description,
                    $activity->ip_address,
                    $activity->url,
                    $activity->method,
                    $activity->status,
                    $activity->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear old activity logs
     */
    public function clearLogs(Request $request)
    {
        try {
            // Clear logs older than 90 days
            $deletedCount = StaffActivityLog::where('created_at', '<', Carbon::now()->subDays(90))->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully cleared {$deletedCount} old activity logs."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear old logs: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get activity details for modal
     */
    public function getActivityDetails($id)
    {
        $activity = StaffActivityLog::with('user')->findOrFail($id);
        
        return view('backend.staff_activity.partials.activity_details', compact('activity'));
    }

    /**
     * Get user timeline for modal
     */
    public function getUserTimeline($userId)
    {
        $activities = StaffActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('backend.staff_activity.partials.user_timeline', compact('activities'));
    }
}