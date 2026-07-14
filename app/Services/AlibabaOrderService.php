<?php

namespace App\Services;

use App\Models\AlibabaOrder;
use App\Models\AlibabaProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\AlibabaApiService;

class AlibabaOrderService
{
    protected $apiService;

    public function __construct(AlibabaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function createOrder($orderData)
    {
        $product = AlibabaProduct::find($orderData['product_id']);
        
        if (!$product) {
            throw new \Exception('Product not found');
        }

        $localOrder = Order::create([
            'user_id' => 1,
            'order_details' => json_encode([
                'product_id' => $product->id,
                'product_name' => $product->title,
                'quantity' => $orderData['quantity'],
                'price' => $product->retail_price
            ]),
            'grand_total' => $product->retail_price * $orderData['quantity'],
            'payment_status' => 'paid',
            'order_status' => 'pending',
            'payment_type' => 'cash_on_delivery',
            'payment_details' => json_encode([
                'customer_name' => $orderData['customer_name'],
                'customer_email' => $orderData['customer_email'],
                'customer_phone' => $orderData['customer_phone'],
                'shipping_address' => $orderData['shipping_address']
            ])
        ]);

        $alibabaOrder = AlibabaOrder::create([
            'local_order_id' => $localOrder->id,
            'supplier_id' => $orderData['supplier_id'],
            'product_id' => $product->id,
            'quantity' => $orderData['quantity'],
            'total_cost' => $product->original_price * $orderData['quantity'],
            'shipping_cost' => 0,
            'status' => 'pending',
            'notes' => $orderData['notes'] ?? null
        ]);

        return $alibabaOrder;
    }

    public function placeOrderWithSupplier(AlibabaOrder $order)
    {
        $order->load(['product', 'supplier']);

        $orderData = [
            'product_id' => $order->product->alibaba_product_id,
            'quantity' => $order->quantity,
            'customer_info' => [
                'name' => $order->localOrder->payment_details['customer_name'] ?? 'Customer',
                'email' => $order->localOrder->payment_details['customer_email'] ?? '',
                'phone' => $order->localOrder->payment_details['customer_phone'] ?? '',
                'address' => $order->localOrder->payment_details['shipping_address'] ?? ''
            ],
            'notes' => $order->notes
        ];

        $result = $this->apiService->placeOrder($orderData);

        if ($result) {
            $order->update([
                'alibaba_order_id' => $result['order_id'],
                'status' => 'ordered',
                'order_data' => $result
            ]);

            return true;
        }

        throw new \Exception('Failed to place order with supplier');
    }

    public function updateLocalOrderStatus(AlibabaOrder $order)
    {
        $statusMapping = [
            'pending' => 'pending',
            'ordered' => 'processing',
            'shipped' => 'on_delivery',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled'
        ];

        $localStatus = $statusMapping[$order->status] ?? 'pending';

        $order->localOrder->update([
            'order_status' => $localStatus
        ]);
    }

    public function syncOrderStatuses()
    {
        $orders = AlibabaOrder::whereIn('status', ['ordered', 'shipped'])
            ->whereNotNull('alibaba_order_id')
            ->get();

        foreach ($orders as $order) {
            try {
                $trackingInfo = $this->apiService->trackOrder($order->alibaba_order_id);
                
                if ($trackingInfo) {
                    $order->update([
                        'status' => $trackingInfo['status'],
                        'tracking_number' => $trackingInfo['tracking_number'] ?? $order->tracking_number
                    ]);

                    $this->updateLocalOrderStatus($order);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to sync order status: ' . $e->getMessage());
            }
        }
    }
}