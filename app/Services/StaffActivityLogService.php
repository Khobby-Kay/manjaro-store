<?php

namespace App\Services;

use App\Models\StaffActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StaffActivityLogService
{
    /**
     * Log staff activity
     */
    public static function log($action, $module, $description = null, $requestData = null, $responseStatus = 'success', $executionTime = null)
    {
        try {
            $user = Auth::user();
            $request = request();

            $logData = [
                'user_id' => $user ? $user->id : null,
                'action' => $action,
                'action_type' => self::determineActionType($action),
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'request_data' => $requestData ?: $request->except(['password', 'password_confirmation', '_token']),
                'status' => $responseStatus,
                'duration' => $executionTime,
                'created_at' => now()
            ];

            StaffActivityLog::create($logData);

        } catch (\Exception $e) {
            Log::error('Failed to log staff activity: ' . $e->getMessage());
        }
    }

    /**
     * Determine action type based on action
     */
    private static function determineActionType($action)
    {
        $criticalActions = ['delete', 'permission_change', 'system_maintenance', 'bulk_delete'];
        $warningActions = ['update', 'bulk_action', 'settings_change'];
        $successActions = ['create', 'login', 'approve', 'import', 'export'];
        $infoActions = ['view', 'search', 'filter', 'download', 'upload'];

        if (in_array($action, $criticalActions)) {
            return 'critical';
        } elseif (in_array($action, $warningActions)) {
            return 'warning';
        } elseif (in_array($action, $successActions)) {
            return 'success';
        } else {
            return 'info';
        }
    }

    /**
     * Log login activity
     */
    public static function logLogin($user, $request)
    {
        self::log(
            'login',
            'security',
            "User {$user->name} logged in successfully",
            ['email' => $user->email],
            'success'
        );
    }

    /**
     * Log logout activity
     */
    public static function logLogout($user, $request)
    {
        self::log(
            'logout',
            'security',
            "User {$user->name} logged out",
            ['email' => $user->email],
            'success'
        );
    }

    /**
     * Log CRUD operations
     */
    public static function logCreate($module, $description, $data = null)
    {
        self::log('create', $module, $description, $data);
    }

    public static function logUpdate($module, $description, $data = null)
    {
        self::log('update', $module, $description, $data);
    }

    public static function logDelete($module, $description, $data = null)
    {
        self::log('delete', $module, $description, $data);
    }

    public static function logView($module, $description, $data = null)
    {
        self::log('view', $module, $description, $data);
    }

    /**
     * Log import/export operations
     */
    public static function logImport($module, $description, $data = null)
    {
        self::log('import', $module, $description, $data);
    }

    public static function logExport($module, $description, $data = null)
    {
        self::log('export', $module, $description, $data);
    }

    /**
     * Log approval/rejection operations
     */
    public static function logApprove($module, $description, $data = null)
    {
        self::log('approve', $module, $description, $data);
    }

    public static function logReject($module, $description, $data = null)
    {
        self::log('reject', $module, $description, $data);
    }

    /**
     * Log settings changes
     */
    public static function logSettingsChange($description, $oldData = null, $newData = null)
    {
        $data = [];
        if ($oldData) $data['old'] = $oldData;
        if ($newData) $data['new'] = $newData;

        self::log('settings_change', 'settings', $description, $data);
    }

    /**
     * Log permission changes
     */
    public static function logPermissionChange($description, $data = null)
    {
        self::log('permission_change', 'staff', $description, $data);
    }

    /**
     * Log system maintenance
     */
    public static function logSystemMaintenance($description, $data = null)
    {
        self::log('system_maintenance', 'system', $description, $data);
    }

    /**
     * Log bulk actions
     */
    public static function logBulkAction($module, $action, $description, $data = null)
    {
        self::log('bulk_action', $module, "Bulk {$action}: {$description}", $data);
    }

    /**
     * Log search operations
     */
    public static function logSearch($module, $searchTerm, $resultsCount = null)
    {
        $description = "Searched for: {$searchTerm}";
        if ($resultsCount !== null) {
            $description .= " (Found {$resultsCount} results)";
        }

        self::log('search', $module, $description, ['search_term' => $searchTerm, 'results_count' => $resultsCount]);
    }

    /**
     * Log filter operations
     */
    public static function logFilter($module, $filters, $resultsCount = null)
    {
        $description = "Applied filters: " . implode(', ', array_keys($filters));
        if ($resultsCount !== null) {
            $description .= " (Found {$resultsCount} results)";
        }

        self::log('filter', $module, $description, ['filters' => $filters, 'results_count' => $resultsCount]);
    }

    /**
     * Log download operations
     */
    public static function logDownload($module, $description, $data = null)
    {
        self::log('download', $module, $description, $data);
    }

    /**
     * Log upload operations
     */
    public static function logUpload($module, $description, $data = null)
    {
        self::log('upload', $module, $description, $data);
    }

    /**
     * Log error activities
     */
    public static function logError($module, $description, $error = null)
    {
        $data = $error ? ['error' => $error] : null;
        self::log('error', $module, $description, $data, 'error');
    }

    /**
     * Get activity statistics
     */
    public static function getActivityStats($days = 30)
    {
        $stats = StaffActivityLog::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_actions,
                COUNT(DISTINCT user_id) as unique_staff,
                COUNT(CASE WHEN action = "login" THEN 1 END) as logins,
                COUNT(CASE WHEN action = "create" THEN 1 END) as creates,
                COUNT(CASE WHEN action = "update" THEN 1 END) as updates,
                COUNT(CASE WHEN action = "delete" THEN 1 END) as deletes
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $stats;
    }

    /**
     * Get staff activity summary
     */
    public static function getStaffActivitySummary($staffId = null, $days = 30)
    {
        $query = StaffActivityLog::where('created_at', '>=', now()->subDays($days));
        
        if ($staffId) {
            $query->where('user_id', $staffId);
        }

        return $query->selectRaw('
                user_id,
                COUNT(*) as total_actions,
                COUNT(CASE WHEN action = "login" THEN 1 END) as logins,
                COUNT(CASE WHEN action = "create" THEN 1 END) as creates,
                COUNT(CASE WHEN action = "update" THEN 1 END) as updates,
                COUNT(CASE WHEN action = "delete" THEN 1 END) as deletes,
                MAX(created_at) as last_activity
            ')
            ->groupBy('user_id')
            ->orderBy('total_actions', 'desc')
            ->get();
    }

    /**
     * Get module activity summary
     */
    public static function getModuleActivitySummary($days = 30)
    {
        return StaffActivityLog::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('
                action as module,
                COUNT(*) as total_actions,
                COUNT(DISTINCT user_id) as unique_staff,
                COUNT(CASE WHEN action = "create" THEN 1 END) as creates,
                COUNT(CASE WHEN action = "update" THEN 1 END) as updates,
                COUNT(CASE WHEN action = "delete" THEN 1 END) as deletes
            ')
            ->groupBy('action')
            ->orderBy('total_actions', 'desc')
            ->get();
    }
} 