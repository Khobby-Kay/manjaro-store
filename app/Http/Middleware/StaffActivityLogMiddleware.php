<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\StaffActivityLogService;
use Illuminate\Support\Facades\Auth;

class StaffActivityLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $executionTime = microtime(true) - $startTime;
        
        // Only log for authenticated admin and staff users
        if (Auth::check() && (Auth::user()->user_type === 'admin' || Auth::user()->user_type === 'staff')) {
            $this->logActivity($request, $response, $executionTime);
        }
        
        return $response;
    }
    
    /**
     * Log the activity
     */
    private function logActivity($request, $response, $executionTime)
    {
        $user = Auth::user();
        $path = $request->path();
        $method = $request->method();
        
        // Determine action and module from the request
        $action = $this->determineAction($request);
        $module = $this->determineModule($path);
        $description = $this->generateDescription($request, $action, $module);
        
        // Determine response status
        $status = $response->getStatusCode() >= 400 ? 'error' : 'success';
        
        // Log the activity
        StaffActivityLogService::log(
            $action,
            $module,
            $description,
            null,
            $status,
            $executionTime
        );
    }
    
    /**
     * Determine the action based on the request
     */
    private function determineAction($request)
    {
        $method = $request->method();
        $path = $request->path();
        
        // Login/Logout actions
        if (str_contains($path, 'login')) {
            return 'login';
        }
        
        if (str_contains($path, 'logout')) {
            return 'logout';
        }
        
        // CRUD actions based on HTTP method
        switch ($method) {
            case 'GET':
                return 'view';
            case 'POST':
                return 'create';
            case 'PUT':
            case 'PATCH':
                return 'update';
            case 'DELETE':
                return 'delete';
            default:
                return 'view';
        }
    }
    
    /**
     * Determine the module based on the URL path
     */
    private function determineModule($path)
    {
        $segments = explode('/', $path);
        
        // Remove 'admin' prefix if present
        if (isset($segments[0]) && $segments[0] === 'admin') {
            array_shift($segments);
        }
        
        // Get the first segment as module
        $module = $segments[0] ?? 'dashboard';
        
        // Map common modules
        $moduleMap = [
            'products' => 'products',
            'orders' => 'orders',
            'customers' => 'customers',
            'staffs' => 'staff',
            'categories' => 'categories',
            'brands' => 'brands',
            'coupons' => 'coupons',
            'banners' => 'banners',
            'blog' => 'blog',
            'pages' => 'pages',
            'faq' => 'faq',
            'contact' => 'contact',
            'notifications' => 'notifications',
            'settings' => 'settings',
            'reports' => 'reports',
            'alibaba' => 'alibaba',
            'staff-activity-logs' => 'staff_activity_logs',
            'dashboard' => 'dashboard',
            'profile' => 'profile'
        ];
        
        return $moduleMap[$module] ?? $module;
    }
    
    /**
     * Generate a description for the activity
     */
    private function generateDescription($request, $action, $module)
    {
        $user = Auth::user();
        $path = $request->path();
        
        // Special cases
        if ($action === 'login') {
            return "User {$user->name} logged in";
        }
        
        if ($action === 'logout') {
            return "User {$user->name} logged out";
        }
        
        // Generate description based on action and module
        $actionText = ucfirst($action);
        $moduleText = ucfirst(str_replace('_', ' ', $module));
        
        // Add specific details for certain actions
        $details = '';
        
        if ($action === 'create' && $request->has('name')) {
            $details = ": {$request->name}";
        } elseif ($action === 'update' && $request->has('id')) {
            $details = " (ID: {$request->id})";
        } elseif ($action === 'delete' && $request->has('id')) {
            $details = " (ID: {$request->id})";
        }
        
        return "{$actionText} {$moduleText}{$details}";
    }
} 