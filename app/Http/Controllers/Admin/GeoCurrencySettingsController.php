<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GeoLocationService;
use App\Models\Currency;

class GeoCurrencySettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:currency_setup'])->only('index', 'update');
    }

    /**
     * Display geo-currency settings page
     */
    public function index()
    {
        $currencies = Currency::where('status', 1)->get();
        $active_currencies = Currency::where('status', 1)->get();
        
        return view('backend.setup_configurations.geo_currency_settings.index', compact('currencies', 'active_currencies'));
    }

    /**
     * Update geo-currency settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'geo_currency_detection' => 'nullable|boolean',
            'geo_currency_fallback' => 'required|exists:currencies,id',
            'geo_currency_cache_duration' => 'required|integer|min:1|max:168'
        ]);

        // Update settings
        set_setting('geo_currency_detection', $request->geo_currency_detection ? 1 : 0);
        set_setting('geo_currency_fallback', $request->geo_currency_fallback);
        set_setting('geo_currency_cache_duration', $request->geo_currency_cache_duration);

        flash(translate('Geo-currency settings updated successfully'))->success();
        return redirect()->route('geo_currency_settings.index');
    }

    /**
     * Test geo-currency detection
     */
    public function test(Request $request)
    {
        $ip = $request->get('ip', null);
        $location = GeoLocationService::getUserLocation($ip);
        $currency = GeoLocationService::getUserCurrency($ip);
        
        return view('backend.setup_configurations.geo_currency_settings.test', compact('location', 'currency', 'ip'));
    }

    /**
     * Get detection statistics
     */
    public function statistics()
    {
        // Get cached location data
        $cacheKeys = \Cache::get('user_location_*');
        $detectionStats = [
            'total_detections' => 0,
            'successful_detections' => 0,
            'failed_detections' => 0,
            'top_countries' => [],
            'top_currencies' => []
        ];

        return view('backend.setup_configurations.geo_currency_settings.statistics', compact('detectionStats'));
    }
} 