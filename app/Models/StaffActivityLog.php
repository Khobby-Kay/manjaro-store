<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class StaffActivityLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'action_type',
        'description',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'request_data',
        'response_data',
        'status',
        'duration',
        'location',
        'device_type',
        'browser',
        'os',
        'is_mobile',
        'is_tablet',
        'is_desktop'
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'is_mobile' => 'boolean',
        'is_tablet' => 'boolean',
        'is_desktop' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeCritical($query)
    {
        return $query->where('action_type', 'critical');
    }

    public function scopeWarning($query)
    {
        return $query->where('action_type', 'warning');
    }

    public function scopeInfo($query)
    {
        return $query->where('action_type', 'info');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('M d, Y H:i:s') : 'Unknown';
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at ? $this->created_at->diffForHumans() : 'Unknown';
    }

    public function getDeviceInfoAttribute()
    {
        $info = [];
        
        if ($this->is_mobile) $info[] = 'Mobile';
        if ($this->is_tablet) $info[] = 'Tablet';
        if ($this->is_desktop) $info[] = 'Desktop';
        
        return implode(', ', $info) ?: 'Unknown';
    }

    public function getActionBadgeClassAttribute()
    {
        switch ($this->action_type) {
            case 'critical':
                return 'danger';
            case 'warning':
                return 'warning';
            case 'success':
                return 'success';
            case 'info':
            default:
                return 'info';
        }
    }

    // Static methods for logging
    public static function logActivity($userId, $action, $description = null, $actionType = 'info', $additionalData = [])
    {
        $request = request();
        
        $data = array_merge([
            'user_id' => $userId,
            'action' => $action,
            'action_type' => $actionType,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'request_data' => $request->except(['password', 'password_confirmation', '_token']),
            'location' => self::getLocationFromIP($request->ip()),
            'device_type' => self::getDeviceType($request->userAgent()),
            'browser' => self::getBrowser($request->userAgent()),
            'os' => self::getOS($request->userAgent()),
        ], $additionalData);

        return self::create($data);
    }

    public static function logCritical($userId, $action, $description = null, $additionalData = [])
    {
        return self::logActivity($userId, $action, $description, 'critical', $additionalData);
    }

    public static function logWarning($userId, $action, $description = null, $additionalData = [])
    {
        return self::logActivity($userId, $action, $description, 'warning', $additionalData);
    }

    public static function logSuccess($userId, $action, $description = null, $additionalData = [])
    {
        return self::logActivity($userId, $action, $description, 'success', $additionalData);
    }

    public static function logInfo($userId, $action, $description = null, $additionalData = [])
    {
        return self::logActivity($userId, $action, $description, 'info', $additionalData);
    }

    // Helper methods
    private static function getLocationFromIP($ip)
    {
        // Simple IP location detection (can be enhanced with external services)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'External';
        }
        return 'Local';
    }

    private static function getDeviceType($userAgent)
    {
        $userAgent = strtolower($userAgent);
        
        if (strpos($userAgent, 'mobile') !== false) {
            return 'mobile';
        } elseif (strpos($userAgent, 'tablet') !== false) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    private static function getBrowser($userAgent)
    {
        $userAgent = strtolower($userAgent);
        
        if (strpos($userAgent, 'chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'safari') !== false) return 'Safari';
        if (strpos($userAgent, 'edge') !== false) return 'Edge';
        if (strpos($userAgent, 'opera') !== false) return 'Opera';
        
        return 'Unknown';
    }

    private static function getOS($userAgent)
    {
        $userAgent = strtolower($userAgent);
        
        if (strpos($userAgent, 'windows') !== false) return 'Windows';
        if (strpos($userAgent, 'mac') !== false) return 'macOS';
        if (strpos($userAgent, 'linux') !== false) return 'Linux';
        if (strpos($userAgent, 'android') !== false) return 'Android';
        if (strpos($userAgent, 'ios') !== false) return 'iOS';
        
        return 'Unknown';
    }

    // Statistics methods
    public static function getTodayStats()
    {
        return [
            'total' => self::today()->count(),
            'critical' => self::today()->critical()->count(),
            'warning' => self::today()->warning()->count(),
            'success' => self::today()->where('action_type', 'success')->count(),
            'info' => self::today()->info()->count(),
        ];
    }

    public static function getUserStats($userId)
    {
        return [
            'total_activities' => self::where('user_id', $userId)->count(),
            'today_activities' => self::where('user_id', $userId)->today()->count(),
            'critical_actions' => self::where('user_id', $userId)->critical()->count(),
            'last_activity' => self::where('user_id', $userId)->latest()->first(),
            'first_activity' => self::where('user_id', $userId)->oldest()->first(),
        ];
    }

    public static function getTopUsers($limit = 10)
    {
        return self::select('user_id')
            ->selectRaw('COUNT(*) as activity_count')
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit($limit)
            ->with('user')
            ->get();
    }
}