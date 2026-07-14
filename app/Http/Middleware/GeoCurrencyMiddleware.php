<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\GeoLocationService;

class GeoCurrencyMiddleware
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
        // Only run for frontend routes and if geo-currency is enabled
        if (get_setting('geo_currency_detection') == 1 && !$request->is('admin/*')) {
            
            // Skip if user has already manually selected a currency
            if (!session()->has('currency_code') || session('currency_code') == '') {
                
                // Get user's location and set appropriate currency
                $currency = GeoLocationService::setUserCurrency();
                
                // Log the detection for debugging
                if ($currency) {
                    \Log::info('Geo-currency detection', [
                        'ip' => GeoLocationService::getClientIP(),
                        'currency' => $currency->code,
                        'symbol' => $currency->symbol
                    ]);
                }
            }
        }

        return $next($request);
    }
} 