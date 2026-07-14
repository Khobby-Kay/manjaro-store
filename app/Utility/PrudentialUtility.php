<?php

namespace App\Utility;
use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PrudentialUtility
{
    // Prudential Bank API endpoints
    public static function api_url()
    {
        return env('PRUDENTIAL_API_URL', 'https://3dss2.quipu.de:8443/order');
    }

    // Get the action URL for Prudential Bank
    public static function get_action_url()
    {
        return self::api_url();
    }

    // Create checkout form for cart payment
    public static function create_checkout_form($combined_order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        $order_data = self::prepare_order_data($combined_order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
        return view('frontend.prudential.checkout_form', compact('combined_order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city', 'order_data'));
    }

    // Create order re-payment form
    public static function create_order_re_payment_form($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        $order_data = self::prepare_order_data($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
        return view('frontend.prudential.order_re_payment_form', compact('order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city', 'order_data'));
    }

    // Create wallet form
    public static function create_wallet_form($user_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        $order_data = self::prepare_order_data($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
        return view('frontend.prudential.wallet_form', compact('user_id', 'order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city', 'order_data'));
    }

    // Create customer package form
    public static function create_customer_package_form($user_id, $package_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        $order_data = self::prepare_order_data($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
        return view('frontend.prudential.customer_package_form', compact('user_id', 'package_id', 'order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city', 'order_data'));
    }

    // Create seller package form
    public static function create_seller_package_form($user_id, $package_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        $order_data = self::prepare_order_data($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
        return view('frontend.prudential.seller_package_form', compact('user_id', 'package_id', 'order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city', 'order_data'));
    }

    // Prepare order data for Prudential Bank API
    private static function prepare_order_data($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        return [
            "order" => [
                "typeRid" => env('PRUDENTIAL_ORDER_TYPE_RID', '225'),
                "amount" => number_format($amount, 2, '.', ''),
                "currency" => env('PRUDENTIAL_CURRENCY', 'GHS'),
                "description" => "Payment for Order #{$order_id}",
                "language" => "en",
                "hppRedirectUrl" => env('PRUDENTIAL_CALLBACK_URL', 'https://manjaro.store/prudential/callback'),
                "consumerDevice" => [
                    "browser" => [
                        "javaEnabled" => false,
                        "jsEnabled" => true,
                        "acceptHeader" => "application/json",
                        "ip" => request()->ip(),
                        "colorDepth" => "24",
                        "screenW" => "1080",
                        "screenH" => "1920",
                        "tzOffset" => "-300",
                        "language" => "en-EN",
                        "userAgent" => request()->userAgent()
                    ]
                ],
                "consumer" => [
                    "email" => $email,
                    "phone" => $phone,
                    "address" => [
                        "firstName" => $first_name,
                        "lastName" => $last_name,
                        "address1" => $address,
                        "city" => $city,
                        "country" => "GH"
                    ]
                ]
            ]
        ];
    }

    // Create order via Prudential Bank API
    public static function create_order($order_data)
    {
        try {
            // Check if certificate files exist
            $certPath = base_path(env('PRUDENTIAL_CERT_PATH', 'storage/app/certs/bank/cert.pem'));
            $keyPath = base_path(env('PRUDENTIAL_KEY_PATH', 'storage/app/certs/bank/key.pem'));
            $caPath = base_path(env('PRUDENTIAL_CA_PATH', 'storage/app/certs/bank/ca.pem'));
            
            if (!file_exists($certPath) || !file_exists($keyPath)) {
                \Log::error('Prudential Bank: Certificate files not found', [
                    'cert_path' => $certPath,
                    'key_path' => $keyPath,
                    'order_data' => $order_data
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Payment system configuration error. Please contact support.',
                    'error_code' => 'CERT_MISSING',
                    'response' => null
                ];
            }
            
            $client = new Client([
                'cert' => [$certPath, ''],
                'ssl_key' => $keyPath,
                'verify' => file_exists($caPath) ? $caPath : false,
                'timeout' => env('PAYMENT_GATEWAY_TIMEOUT', 30),
                'connect_timeout' => 10,
                'http_errors' => false,
            ]);

            $response = $client->post(self::api_url(), [
                'json' => $order_data,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'ManjaroStore/1.0'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            \Log::info('Prudential Bank API Response', [
                'status_code' => $statusCode,
                'response' => $responseData,
                'order_data' => $order_data
            ]);
            
            if ($statusCode === 200 && isset($responseData['order']['orderId'])) {
                return [
                    'success' => true,
                    'order_id' => $responseData['order']['orderId'],
                    'redirect_url' => $responseData['order']['hppUrl'] ?? null,
                    'response' => $responseData
                ];
            }

            $errorMessage = 'Payment processing failed. Please try again.';
            if (isset($responseData['error'])) {
                $errorMessage = $responseData['error'];
            } elseif (isset($responseData['message'])) {
                $errorMessage = $responseData['message'];
            } elseif ($statusCode !== 200) {
                $errorMessage = 'Payment service temporarily unavailable. Please try again later.';
            }

            \Log::error('Prudential Bank: Order creation failed', [
                'status_code' => $statusCode,
                'response' => $responseData,
                'order_data' => $order_data
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'error_code' => 'ORDER_CREATION_FAILED',
                'response' => $responseData
            ];

        } catch (RequestException $e) {
            \Log::error('Prudential Bank: Request exception', [
                'error' => $e->getMessage(),
                'order_data' => $order_data
            ]);
            
            return [
                'success' => false,
                'error' => 'Payment service temporarily unavailable. Please try again later.',
                'error_code' => 'API_ERROR',
                'response' => null
            ];
        } catch (\Exception $e) {
            \Log::error('Prudential Bank: Unexpected error', [
                'error' => $e->getMessage(),
                'order_data' => $order_data
            ]);
            
            return [
                'success' => false,
                'error' => 'An unexpected error occurred. Please contact support.',
                'error_code' => 'UNEXPECTED_ERROR',
                'response' => null
            ];
        }
    }

    // Verify callback from Prudential Bank
    public static function verify_callback($request)
    {
        // Get the order ID from the callback
        $order_id = $request->input('order_id');
        
        if (!$order_id) {
            return false;
        }

        try {
            $client = new Client([
                'cert' => [base_path(env('PRUDENTIAL_CERT_PATH', 'storage/app/certs/bank/cert.pem')), ''],
                'ssl_key' => base_path(env('PRUDENTIAL_KEY_PATH', 'storage/app/certs/bank/key.pem')),
                'verify' => false,
                'timeout' => 30,
            ]);

            // Get order status from Prudential Bank
            $response = $client->get(self::api_url() . '/' . $order_id, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            // Check if order is completed
            if (isset($responseData['order']['status'])) {
                $status = $responseData['order']['status'];
                return in_array($status, ['Completed', 'APPROVED', 'SUCCESS']);
            }

            return false;

        } catch (RequestException $e) {
            \Log::error('Prudential verification error: ' . $e->getMessage());
            return false;
        }
    }

    // Get order status
    public static function get_order_status($order_id)
    {
        try {
            $client = new Client([
                'cert' => [base_path(env('PRUDENTIAL_CERT_PATH', 'storage/app/certs/bank/cert.pem')), ''],
                'ssl_key' => base_path(env('PRUDENTIAL_KEY_PATH', 'storage/app/certs/bank/key.pem')),
                'verify' => false,
                'timeout' => 30,
            ]);

            $response = $client->get(self::api_url() . '/' . $order_id, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            \Log::error('Prudential status check error: ' . $e->getMessage());
            return null;
        }
    }

    // Test Prudential Bank connection
    public static function test_connection()
    {
        try {
            $client = new Client([
                'cert' => [base_path(env('PRUDENTIAL_CERT_PATH', 'storage/app/certs/bank/cert.pem')), ''],
                'ssl_key' => base_path(env('PRUDENTIAL_KEY_PATH', 'storage/app/certs/bank/key.pem')),
                'verify' => false,
                'timeout' => 10,
            ]);

            // Try to make a simple request to test connection
            $response = $client->get(self::api_url(), [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            return $response->getStatusCode() === 200;

        } catch (RequestException $e) {
            \Log::error('Prudential connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
