<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AlibabaSupplier;
use App\Models\AlibabaProduct;

class AlibabaApiService
{
    protected $apiKey;
    protected $apiSecret;
    protected $baseUrl = 'https://api.alibaba.com/v2/';

    public function __construct()
    {
        $this->apiKey = get_setting('alibaba_api_key');
        $this->apiSecret = get_setting('alibaba_api_secret');
    }

    public function authenticate()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . 'auth/verify');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Alibaba API authentication failed: ' . $e->getMessage());
            return false;
        }
    }

    public function searchProducts($query, $filters = [])
    {
        try {
            $params = array_merge([
                'q' => $query,
                'page' => 1,
                'pageSize' => 50
            ], $filters);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . 'products/search', $params);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Alibaba product search failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getProductByUrl($url)
    {
        try {
            $productId = $this->extractProductIdFromUrl($url);
            
            if (!$productId) {
                throw new \Exception('Invalid Alibaba product URL');
            }

            return $this->getProductDetails($productId);
        } catch (\Exception $e) {
            Log::error('Failed to get product by URL: ' . $e->getMessage());
            return null;
        }
    }

    public function getProductDetails($productId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . 'products/' . $productId);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                
                return [
                    'alibaba_product_id' => $data['id'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'original_price' => $data['price'],
                    'min_order_quantity' => $data['minOrderQuantity'],
                    'stock_quantity' => $data['stockQuantity'],
                    'images' => $data['images'] ?? [],
                    'specifications' => $data['specifications'] ?? [],
                    'shipping_info' => $data['shippingInfo'] ?? []
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get product details: ' . $e->getMessage());
            return null;
        }
    }

    public function getSupplierProducts($supplierId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . 'suppliers/' . $supplierId . '/products');

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get supplier products: ' . $e->getMessage());
            return [];
        }
    }

    public function placeOrder($orderData)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . 'orders', $orderData);

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to place order: ' . $e->getMessage());
            return null;
        }
    }

    public function trackOrder($trackingNumber)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . 'orders/track/' . $trackingNumber);

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to track order: ' . $e->getMessage());
            return null;
        }
    }

    private function getAccessToken()
    {
        $cachedToken = cache('alibaba_access_token');
        
        if ($cachedToken) {
            return $cachedToken;
        }

        $response = Http::post($this->baseUrl . 'auth/token', [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret
        ]);

        if ($response->successful()) {
            $token = $response->json()['access_token'];
            cache(['alibaba_access_token' => $token], 3600);
            return $token;
        }

        throw new \Exception('Failed to get access token');
    }

    private function extractProductIdFromUrl($url)
    {
        // Handle different Alibaba URL formats
        // Format 1: /product-detail/PRODUCT-NAME_PRODUCTID.html
        if (preg_match('/product-detail\/[^_]+_(\d+)\.html/', $url, $matches)) {
            return $matches[1];
        }
        
        // Format 2: /product/PRODUCTID.html (legacy format)
        if (preg_match('/product\/(\d+)\.html/', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}