<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Currency;
use App\Models\Country;

class GeoLocationService
{
    /**
     * Get user's location based on IP address
     */
    public static function getUserLocation($ip = null)
    {
        if (!$ip) {
            $ip = self::getClientIP();
        }

        // Cache the result for 24 hours to avoid repeated API calls
        $cacheKey = 'user_location_' . md5($ip);
        
        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            try {
                // Using ipapi.co for geolocation (free tier available)
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'success') {
                        return [
                            'country_code' => $data['countryCode'],
                            'country_name' => $data['country'],
                            'region' => $data['regionName'],
                            'city' => $data['city'],
                            'timezone' => $data['timezone'],
                            'currency_code' => $data['currency'] ?? 'USD',
                            'ip' => $ip
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Geolocation API error: ' . $e->getMessage());
            }

            // Fallback to default location
            return [
                'country_code' => 'US',
                'country_name' => 'United States',
                'region' => '',
                'city' => '',
                'timezone' => 'UTC',
                'currency_code' => 'USD',
                'ip' => $ip
            ];
        });
    }

    /**
     * Get client IP address
     */
    public static function getClientIP()
    {
        $ipaddress = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
            
        return $ipaddress;
    }

    /**
     * Get currency for user's location
     */
    public static function getUserCurrency($ip = null)
    {
        $location = self::getUserLocation($ip);
        $currencyCode = $location['currency_code'];

        // Check if currency exists in our system
        $currency = Currency::where('code', $currencyCode)->where('status', 1)->first();
        
        if (!$currency) {
            // Fallback to default currency
            $currency = Currency::where('id', get_setting('system_default_currency'))->first();
        }

        return $currency;
    }

    /**
     * Set user's currency based on location
     */
    public static function setUserCurrency($ip = null)
    {
        $currency = self::getUserCurrency($ip);
        
        if ($currency) {
            session([
                'currency_code' => $currency->code,
                'currency_symbol' => $currency->symbol,
                'currency_exchange_rate' => $currency->exchange_rate
            ]);
        }

        return $currency;
    }

    /**
     * Get currency mapping for countries
     */
    public static function getCountryCurrencyMapping()
    {
        return [
            'US' => 'USD',
            'GB' => 'GBP',
            'EU' => 'EUR',
            'CA' => 'CAD',
            'AU' => 'AUD',
            'JP' => 'JPY',
            'IN' => 'INR',
            'CN' => 'CNY',
            'BR' => 'BRL',
            'MX' => 'MXN',
            'KR' => 'KRW',
            'SG' => 'SGD',
            'HK' => 'HKD',
            'AE' => 'AED',
            'SA' => 'SAR',
            'ZA' => 'ZAR',
            'NG' => 'NGN',
            'KE' => 'KES',
            'GH' => 'GHS',
            'EG' => 'EGP',
            'MA' => 'MAD',
            'TN' => 'TND',
            'DZ' => 'DZD',
            'LY' => 'LYD',
            'SD' => 'SDG',
            'ET' => 'ETB',
            'TZ' => 'TZS',
            'UG' => 'UGX',
            'RW' => 'RWF',
            'BI' => 'BIF',
            'MW' => 'MWK',
            'ZM' => 'ZMW',
            'ZW' => 'ZWL',
            'BW' => 'BWP',
            'NA' => 'NAD',
            'SZ' => 'SZL',
            'LS' => 'LSL',
            'MG' => 'MGA',
            'MU' => 'MUR',
            'SC' => 'SCR',
            'KM' => 'KMF',
            'DJ' => 'DJF',
            'SO' => 'SOS',
            'ER' => 'ERN',
            'SS' => 'SSP',
            'CF' => 'XAF',
            'CM' => 'XAF',
            'TD' => 'XAF',
            'GQ' => 'XAF',
            'GA' => 'XAF',
            'CG' => 'XAF',
            'CD' => 'CDF',
            'AO' => 'AOA',
            'GW' => 'XOF',
            'GN' => 'GNF',
            'SL' => 'SLL',
            'LR' => 'LRD',
            'CI' => 'XOF',
            'BF' => 'XOF',
            'ML' => 'XOF',
            'NE' => 'XOF',
            'SN' => 'XOF',
            'TG' => 'XOF',
            'BJ' => 'XOF',
            'MR' => 'MRO',
            'GM' => 'GMD',
            'CV' => 'CVE',
            'ST' => 'STD',
            'GW' => 'XOF',
            'GN' => 'GNF',
            'SL' => 'SLL',
            'LR' => 'LRD',
            'CI' => 'XOF',
            'BF' => 'XOF',
            'ML' => 'XOF',
            'NE' => 'XOF',
            'SN' => 'XOF',
            'TG' => 'XOF',
            'BJ' => 'XOF',
            'MR' => 'MRO',
            'GM' => 'GMD',
            'CV' => 'CVE',
            'ST' => 'STD',
        ];
    }
} 