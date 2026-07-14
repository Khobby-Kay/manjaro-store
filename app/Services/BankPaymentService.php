<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BankPaymentService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'cert' => [base_path(env('BANK_CERT_PATH')), ''],
            'ssl_key' => base_path(env('BANK_KEY_PATH')),
            'verify' => false,
            'timeout' => 30,
        ]);
    }

    public function createOrder($amount, $currency, $description)
    {
        try {
            $payload = [
                "order" => [
                    "typeRid" => env('BANK_ORDER_TYPE_RID'),
                    "amount" => $amount,
                    "currency" => $currency,
                    "description" => $description,
                    "language" => "en",
                    "hppRedirectUrl" => env('BANK_CALLBACK_URL'),
                    "consumerDevice" => [
                        "browser" => [
                            "javaEnabled" => false,
                            "jsEnabled" => true,
                            "acceptHeader" => "application/json",
                            "ip" => "127.0.0.1",
                            "colorDepth" => "24",
                            "screenW" => "1080",
                            "screenH" => "1920",
                            "tzOffset" => "-300",
                            "language" => "en-EN",
                            "userAgent" => "Laravel-App"
                        ]
                    ]
                ]
            ];

            // ✅ Log the request you are sending
            \Log::info('Prudential Bank CreateOrder Request Payload', ['payload' => $payload]);

            $response = $this->client->post(env('BANK_API_URL'), [
                'json' => $payload
            ]);

            $responseBody = (string) $response->getBody();

            // ✅ Log the raw response you got
            \Log::info('Prudential Bank CreateOrder Raw Response', ['body' => $responseBody]);

            return json_decode($responseBody, true);

        } catch (RequestException $e) {
            \Log::error('BankPaymentService createOrder error: ' . $e->getMessage());
            return null;
        }
    }

    public function getOrderDetails($orderId, $password)
    {
        try {
            $url = env('BANK_GET_URL') . $orderId . '?password=' . $password;

            $response = $this->client->get($url);

            $responseBody = (string) $response->getBody();
            \Log::info('Prudential Bank GetOrderDetails Raw Response', ['body' => $responseBody]);

            return json_decode($responseBody, true);

        } catch (RequestException $e) {
            \Log::error('BankPaymentService getOrderDetails error: ' . $e->getMessage());
            return null;
        }
    }
}
