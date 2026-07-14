<?php

namespace App\Utility;
use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MobileMoneyUtility
{
    // Mobile Money API endpoints
    public static function api_url()
    {
        return env('MOBILE_MONEY_API_URL', 'https://digihub.prudentialbank.com.gh/MobileMoneyPayment/api/Transaction');
    }

    // Get the action URL for Mobile Money
    public static function get_action_url()
    {
        return self::api_url();
    }

    // Create checkout form for cart payment
    public static function create_checkout_form($combined_order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        return view('frontend.mobile_money.checkout_form', compact('combined_order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city'));
    }

    // Create order re-payment form
    public static function create_order_re_payment_form($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        return view('frontend.mobile_money.order_re_payment_form', compact('order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city'));
    }

    // Create wallet form
    public static function create_wallet_form($user_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        return view('frontend.mobile_money.wallet_form', compact('user_id', 'order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city'));
    }

    // Create customer package form
    public static function create_customer_package_form($user_id, $package_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        return view('frontend.mobile_money.customer_package_form', compact('user_id', 'package_id', 'order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city'));
    }

    // Create seller package form
    public static function create_seller_package_form($user_id, $package_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city)
    {
        return view('frontend.mobile_money.seller_package_form', compact('user_id', 'package_id', 'order_id', 'amount', 'first_name', 'last_name', 'phone', 'email', 'address', 'city'));
    }

    // Wallet Name Enquiry
    public static function wallet_name_enquiry($wallet_type, $wallet_number)
    {
        // Validate inputs
        if (empty($wallet_type) || empty($wallet_number)) {
            return [
                'success' => false,
                'error' => 'Wallet type and number are required',
                'error_code' => 'INVALID_INPUT',
                'response' => null
            ];
        }

        // Format wallet number
        $wallet_number = self::format_wallet_number($wallet_number);
        
        // Validate wallet number
        if (!self::validate_wallet_number($wallet_number, $wallet_type)) {
            return [
                'success' => false,
                'error' => 'Invalid wallet number format',
                'error_code' => 'INVALID_WALLET_NUMBER',
                'response' => null
            ];
        }

        // Test mode for localhost - return mock data
        if (app()->environment('local') || str_contains(request()->getHost(), 'localhost')) {
            \Log::info('Mobile Money: Wallet name enquiry (test mode)', [
                'wallet_type' => $wallet_type,
                'wallet_number' => $wallet_number
            ]);
            
            return [
                'success' => true,
                'account_name' => 'Test User (Localhost)',
                'response' => ['test_mode' => true]
            ];
        }

        try {
            $client = new Client([
                'auth' => [
                    env('MOBILE_MONEY_USERNAME', 'momoapi.user.manjaro'),
                    env('MOBILE_MONEY_PASSWORD', '!p@5s4M@nj@r0')
                ],
                'verify' => false,
                'timeout' => env('PAYMENT_GATEWAY_TIMEOUT', 30),
                'connect_timeout' => 10,
                'http_errors' => false,
            ]);

            $requestData = [
                'clientId' => env('MOBILE_MONEY_CLIENT_ID', '476E3A87-CC97-48DB-8A15-9AE03516AA71'),
                'walletType' => $wallet_type,
                'walletNumber' => $wallet_number
            ];

            \Log::info('Mobile Money: Wallet name enquiry request', [
                'wallet_type' => $wallet_type,
                'wallet_number' => $wallet_number,
                'request_data' => $requestData
            ]);

            $response = $client->post(self::api_url() . '/WalletNameEnquiry', [
                'json' => $requestData,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            if (isset($responseData['statusCode']) && $responseData['statusCode'] == '00') {
                return [
                    'success' => true,
                    'account_name' => $responseData['details']['accountName'] ?? 'Unknown',
                    'response' => $responseData
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['statusMessage'] ?? 'Failed to retrieve wallet name',
                'response' => $responseData
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage(),
                'response' => null
            ];
        }
    }

    // Debit Wallet
    public static function debit_wallet($wallet_type, $wallet_name, $wallet_number, $amount, $transaction_id, $remarks)
    {
        // Test mode for localhost - return mock success
        if (app()->environment('local') || str_contains(request()->getHost(), 'localhost')) {
            return [
                'success' => true,
                'transaction_id' => 'TEST_' . $transaction_id,
                'reference' => 'TEST_REF_' . time(),
                'response' => ['test_mode' => true, 'message' => 'Test payment successful']
            ];
        }

        try {
            $client = new Client([
                'auth' => [
                    env('MOBILE_MONEY_USERNAME', 'momoapi.user.manjaro'),
                    env('MOBILE_MONEY_PASSWORD', '!p@5s4M@nj@r0')
                ],
                'verify' => false,
                'timeout' => 30,
            ]);

            $requestData = [
                'clientId' => env('MOBILE_MONEY_CLIENT_ID', '476E3A87-CC97-48DB-8A15-9AE03516AA71'),
                'walletType' => $wallet_type,
                'walletName' => $wallet_name,
                'walletNumber' => $wallet_number,
                'amount' => (float) $amount,
                'transactionId' => $transaction_id,
                'remarks' => $remarks
            ];

            $response = $client->post(self::api_url() . '/DebitWallet', [
                'json' => $requestData,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            if (isset($responseData['status']) && $responseData['status'] == '0') {
                return [
                    'success' => true,
                    'transaction_id' => $responseData['details']['cb_reference'] ?? $transaction_id,
                    'reference' => $responseData['details']['cb_reference'] ?? '',
                    'response' => $responseData
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Payment failed',
                'response' => $responseData
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage(),
                'response' => null
            ];
        }
    }

    // Check Transaction Status
    public static function check_transaction_status($wallet_type, $client_reference)
    {
        try {
            $client = new Client([
                'auth' => [
                    env('MOBILE_MONEY_USERNAME', 'momoapi.user.manjaro'),
                    env('MOBILE_MONEY_PASSWORD', '!p@5s4M@nj@r0')
                ],
                'verify' => false,
                'timeout' => 30,
            ]);

            $requestData = [
                'clientId' => env('MOBILE_MONEY_CLIENT_ID', '476E3A87-CC97-48DB-8A15-9AE03516AA71'),
                'walletType' => $wallet_type,
                'clientReference' => $client_reference
            ];

            $response = $client->post(self::api_url() . '/CheckTransactionStatus', [
                'json' => $requestData,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            if (isset($responseData['statusCode']) && $responseData['statusCode'] == '00') {
                return [
                    'success' => true,
                    'status' => 'completed',
                    'transaction_date' => $responseData['details']['transactionDate'] ?? '',
                    'tpp_reference' => $responseData['details']['tppReference'] ?? '',
                    'response' => $responseData
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['statusMessage'] ?? 'Failed to check transaction status',
                'response' => $responseData
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage(),
                'response' => null
            ];
        }
    }

    // Verify callback from Mobile Money
    public static function verify_callback($request)
    {
        // Get the transaction ID from the callback
        $transaction_id = $request->input('transaction_id');
        
        if (!$transaction_id) {
            return false;
        }

        // For now, we'll assume the callback is valid if we have a transaction_id
        // In a real implementation, you might want to verify with the API
        return true;
    }

    // Test Mobile Money connection
    public static function test_connection()
    {
        try {
            $client = new Client([
                'auth' => [
                    env('MOBILE_MONEY_USERNAME', 'momoapi.user.manjaro'),
                    env('MOBILE_MONEY_PASSWORD', '!p@5s4M@nj@r0')
                ],
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
            \Log::error('Mobile Money connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    // Get supported wallet types
    public static function get_supported_wallet_types()
    {
        return [
            'mtn' => 'MTN Mobile Money',
            'vodafone' => 'Vodafone Cash',
            'airteltigo' => 'AirtelTigo Money'
        ];
    }

    // Format wallet number
    public static function format_wallet_number($wallet_number)
    {
        // Remove any non-numeric characters
        $wallet_number = preg_replace('/[^0-9]/', '', $wallet_number);
        
        // Add country code if not present
        if (strpos($wallet_number, '233') !== 0) {
            $wallet_number = '233' . $wallet_number;
        }
        
        return $wallet_number;
    }

    // Validate wallet number
    public static function validate_wallet_number($wallet_number, $wallet_type)
    {
        $wallet_number = self::format_wallet_number($wallet_number);
        
        // Basic validation
        if (strlen($wallet_number) < 12) {
            return false;
        }
        
        // Check if it starts with 233 (Ghana country code)
        if (strpos($wallet_number, '233') !== 0) {
            return false;
        }
        
        return true;
    }
}
