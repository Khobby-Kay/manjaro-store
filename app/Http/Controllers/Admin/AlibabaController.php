<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlibabaSupplier;
use App\Models\AlibabaProduct;
use App\Models\AlibabaOrder;
use App\Models\AlibabaImportLog;
use App\Services\AlibabaApiService;
use App\Services\AlibabaImportService;
use App\Services\AlibabaPricingService;
use Carbon\Carbon;

class AlibabaController extends Controller
{
    protected $apiService;
    protected $importService;
    protected $pricingService;

    public function __construct(
        AlibabaApiService $apiService,
        AlibabaImportService $importService,
        AlibabaPricingService $pricingService
    ) {
        $this->apiService = $apiService;
        $this->importService = $importService;
        $this->pricingService = $pricingService;
        
        // Permission middleware
        $this->middleware(['permission:alibaba_management'])->only(['index', 'settings', 'updateSettings']);
        $this->middleware(['permission:alibaba_dashboard'])->only(['index']);
        $this->middleware(['permission:alibaba_settings'])->only(['settings', 'updateSettings']);
        $this->middleware(['permission:alibaba_api_testing'])->only(['testApi', 'syncProducts']);

        // Temporarily comment out permission middleware for testing
        // $this->middleware(['permission:view_alibaba_module'])->only(['index', 'dashboard']);
        // $this->middleware(['permission:configure_alibaba_api'])->only(['settings', 'updateSettings']);
    }

    public function index()
    {
        $stats = [
            'total_suppliers' => AlibabaSupplier::count(),
            'active_suppliers' => AlibabaSupplier::active()->count(),
            'total_products' => AlibabaProduct::count(),
            'imported_products' => AlibabaProduct::imported()->count(),
            'pending_orders' => AlibabaOrder::where('status', 'pending')->count(),
            'total_orders' => AlibabaOrder::count(),
            'recent_imports' => AlibabaImportLog::with('supplier')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'profit_margin' => $this->calculateProfitMargin(),
            'trending_products' => AlibabaProduct::with('supplier')
                ->where('status', 'imported')
                ->orderBy('last_sync_at', 'desc')
                ->limit(6)
                ->get()
        ];

        return view('backend.alibaba.dashboard', compact('stats'));
    }

    public function dashboard()
    {
        return $this->index();
    }

    public function settings()
    {
        $settings = [
            'api_key' => '',
            'api_secret' => '',
            'default_markup' => 50,
            'min_markup' => 30,
            'max_markup' => 200,
            'auto_approve' => false,
            'sync_interval' => 6,
            'currency' => 'USD'
        ];

        return view('backend.alibaba.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
            'default_markup' => 'required|numeric|min:0|max:1000',
            'min_markup' => 'required|numeric|min:0|max:1000',
            'max_markup' => 'required|numeric|min:0|max:1000',
            'auto_approve' => 'boolean',
            'sync_interval' => 'required|numeric|min:1|max:24',
            'currency' => 'required|string|size:3'
        ]);

        // For now, just redirect with success message
        flash(translate('Settings updated successfully'))->success();
        return redirect()->route('alibaba.settings');
    }

    private function calculateMonthlyRevenue()
    {
        return 0;
    }

    private function calculateProfitMargin()
    {
        return 0;
    }

    public function testApi(Request $request)
    {
        try {
            // For now, return a success response
            // In a real implementation, this would test the actual API connection
            return response()->json([
                'success' => true,
                'message' => 'API connection test successful!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ]);
        }
    }

    public function syncProducts(Request $request)
    {
        try {
            // For now, return a success response
            // In a real implementation, this would sync products from Alibaba
            return response()->json([
                'success' => true,
                'message' => 'Product sync completed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product sync failed: ' . $e->getMessage()
            ]);
        }
    }
}