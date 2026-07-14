<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeoLocationService;
use App\Models\Currency;

class GeoCurrencyController extends Controller
{
    /**
     * Detect user's location and currency
     */
    public function detectLocation(Request $request)
    {
        try {
            $location = GeoLocationService::getUserLocation();
            $currency = GeoLocationService::getUserCurrency();
            
            return response()->json([
                'success' => true,
                'location' => $location,
                'currency' => $currency ? [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'name' => $currency->name,
                    'exchange_rate' => $currency->exchange_rate
                ] : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to detect location'
            ]);
        }
    }

    /**
     * Set currency based on location
     */
    public function setCurrencyByLocation(Request $request)
    {
        try {
            $currency = GeoLocationService::setUserCurrency();
            
            if ($currency) {
                return response()->json([
                    'success' => true,
                    'message' => 'Currency set to ' . $currency->name,
                    'currency' => [
                        'code' => $currency->code,
                        'symbol' => $currency->symbol,
                        'name' => $currency->name
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No suitable currency found for your location'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set currency'
            ]);
        }
    }

    /**
     * Get available currencies for a country
     */
    public function getCurrenciesForCountry(Request $request)
    {
        $countryCode = $request->get('country_code', 'US');
        $mapping = GeoLocationService::getCountryCurrencyMapping();
        
        $currencyCode = $mapping[$countryCode] ?? 'USD';
        $currency = Currency::where('code', $currencyCode)->where('status', 1)->first();
        
        if (!$currency) {
            $currency = Currency::where('id', get_setting('system_default_currency'))->first();
        }
        
        return response()->json([
            'success' => true,
            'currency' => $currency ? [
                'id' => $currency->id,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'name' => $currency->name,
                'exchange_rate' => $currency->exchange_rate
            ] : null
        ]);
    }

    /**
     * Test geo-currency detection
     */
    public function testDetection(Request $request)
    {
        $ip = $request->get('ip', null);
        $location = GeoLocationService::getUserLocation($ip);
        $currency = GeoLocationService::getUserCurrency($ip);
        
        return response()->json([
            'success' => true,
            'test_ip' => $ip ?? GeoLocationService::getClientIP(),
            'location' => $location,
            'currency' => $currency ? [
                'id' => $currency->id,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'name' => $currency->name,
                'exchange_rate' => $currency->exchange_rate
            ] : null
        ]);
    }
} 