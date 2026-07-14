<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Api\V2\Seller\SellerPackageController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CheckoutController;
use App\Models\User;
use App\Models\Wallet;
use App\Models\CombinedOrder;
use App\Utility\PrudentialUtility;
use App\Models\CustomerPackage;
use App\Models\CustomerPackagePayment;
use App\Models\Order;
use App\Models\SellerPackage;
use App\Models\StaffActivityLog;
use Session;
use Auth;
use Illuminate\Http\Request;

class PrudentialController extends Controller
{
    public function __construct()
    {
    }

    public function pay(Request $request)
    {
        if (Session::has('payment_type')) {
            $paymentType = Session::get('payment_type');
            $paymentData = Session::get('payment_data');

            // Log payment initiation
            StaffActivityLog::logInfo(auth()->id(), 'prudential_payment_initiated', 'Prudential Bank payment initiated for type: ' . $paymentType);

            $user = Auth::user();
            $user_id = $user->id;
            $first_name = $user->name;
            $last_name = 'X';
            $phone = '123456789';
            $email = $user->email;
            $address = 'dummy address';
            $city = 'Colombo';

            if ($paymentType == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                $combined_order_id = $combined_order->id;
                $amount = $combined_order->grand_total;
                $first_name = json_decode($combined_order->shipping_address)->name;
                $phone = json_decode($combined_order->shipping_address)->phone;
                $email = json_decode($combined_order->shipping_address)->email;
                $address = json_decode($combined_order->shipping_address)->address;
                $city = json_decode($combined_order->shipping_address)->city;
                return PrudentialUtility::create_checkout_form($combined_order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
            }
            elseif ($paymentType == 'order_re_payment') {
                $order = Order::findOrFail($paymentData['order_id']);
                $order_id = $order->id;
                $amount = $order->grand_total;
                $first_name = json_decode($order->shipping_address)->name;
                $phone = json_decode($order->shipping_address)->phone;
                $email = json_decode($order->shipping_address)->email;
                $address = json_decode($order->shipping_address)->address;
                $city = json_decode($order->shipping_address)->city;
                return PrudentialUtility::create_order_re_payment_form($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
            }
            elseif ($paymentType == 'wallet_payment') {
                $order_id = rand(100000, 999999);
                $amount = $request->amount;
                return PrudentialUtility::create_wallet_form($user_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
            }
            elseif ($paymentType == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail($paymentData['customer_package_id']);
                $order_id = rand(100000, 999999);
                $package_id = $customer_package->id;
                $amount = $customer_package->amount;
                return PrudentialUtility::create_customer_package_form($user_id, $package_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
            }
            elseif ($paymentType == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail($paymentData['seller_package_id']);
                $order_id = rand(100000, 999999);
                $package_id = $seller_package->id;
                $amount = $seller_package->amount;
                return PrudentialUtility::create_seller_package_form($user_id, $package_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
            }
        }
    }

    public function checkout_testing()
    {
        $order_id = rand(100000, 999999);
        $amount = 88.00;
        $first_name = 'Hasan';
        $last_name = 'Taluker';
        $phone = '2135421321';
        $email = 'hasan@taluker.com';
        $address = '22/b baker street';
        $city = 'Colombo';

        return PrudentialUtility::create_checkout_form($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
    }

    public function wallet_testing()
    {
        $order_id = rand(100000, 999999);
        $user_id = Auth::user()->id;
        $amount = 88.00;
        $first_name = 'Hasan';
        $last_name = 'Taluker';
        $phone = '2135421321';
        $email = 'hasan@taluker.com';
        $address = '22/b baker street';
        $city = 'Colombo';

        return PrudentialUtility::create_wallet_form($user_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
    }

    public function customer_package_payment_testing()
    {
        $order_id = rand(100000, 999999);
        $user_id = Auth::user()->id;
        $package_id = 4;
        $amount = 88.00;
        $first_name = 'Hasan';
        $last_name = 'Taluker';
        $phone = '2135421321';
        $email = 'hasan@taluker.com';
        $address = '22/b baker street';
        $city = 'Colombo';

        return PrudentialUtility::create_customer_package_form($user_id, $package_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
    }

    public function seller_package_payment_testing()
    {
        $order_id = rand(100000, 999999);
        $user_id = Auth::user()->id;
        $package_id = 4;
        $amount = 88.00;
        $first_name = 'Hasan';
        $last_name = 'Taluker';
        $phone = '2135421321';
        $email = 'hasan@taluker.com';
        $address = '22/b baker street';
        $city = 'Colombo';

        return PrudentialUtility::create_seller_package_form($user_id, $package_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
    }

    public function prudential_callback(Request $request)
    {
        // Handle Prudential Bank callback
        $order_id = $request->input('order_id');
        $status = $request->input('status');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $transaction_id = $request->input('transaction_id');

        // Log callback received (use admin user if no auth, or system user)
        $admin_user_id = auth()->id() ?? 1; // Default to admin user ID 1 if no auth
        StaffActivityLog::logInfo($admin_user_id, 'prudential_callback_received', 'Prudential Bank callback received for order: ' . $order_id);

        // Verify the callback using PrudentialUtility
        if (PrudentialUtility::verify_callback($request)) {
            $payment = [
                "status" => "Success",
                "transaction_id" => $transaction_id,
                "amount" => $amount,
                "currency" => $currency
            ];

            // Log successful payment
            StaffActivityLog::logSuccess($admin_user_id, 'prudential_payment_success', 'Prudential Bank payment successful for order: ' . $order_id . ', Amount: ' . $amount);

            $payment_type = Session::get('payment_type');
            $paymentData = Session::get('payment_data');

            if ($payment_type == 'cart_payment') {
                return (new CheckoutController)->checkout_done(Session::get('combined_order_id'), json_encode($payment));
            }
            elseif ($payment_type == 'order_re_payment') {
                return (new CheckoutController)->orderRePaymentDone($paymentData, json_encode($payment));
            }
            elseif ($payment_type == 'wallet_payment') {
                $user = Auth::user();
                $wallet = new Wallet;
                $wallet->user_id = $user->id;
                $wallet->amount = $paymentData['amount'];
                $wallet->payment_method = 'prudential_bank';
                $wallet->payment_details = json_encode($payment);
                $wallet->save();

                flash(translate('Payment completed'))->success();
                return redirect()->route('wallet.index');
            }
            elseif ($payment_type == 'customer_package_payment') {
                $user = Auth::user();
                $customer_package = CustomerPackage::findOrFail($paymentData['customer_package_id']);
                $user->customer_package_id = $customer_package->id;
                $user->remaining_uploads += $customer_package->product_upload;
                $user->save();

                $customer_package_payment = new CustomerPackagePayment();
                $customer_package_payment->user_id = $user->id;
                $customer_package_payment->customer_package_id = $customer_package->id;
                $customer_package_payment->amount = $customer_package->amount;
                $customer_package_payment->payment_method = 'prudential_bank';
                $customer_package_payment->payment_details = json_encode($payment);
                $customer_package_payment->save();

                flash(translate('Payment completed'))->success();
                return redirect()->route('dashboard');
            }
            elseif ($payment_type == 'seller_package_payment') {
                return (new SellerPackageController)->purchase_payment_done($paymentData, json_encode($payment));
            }
        } else {
            // Log failed payment
            StaffActivityLog::logError($admin_user_id, 'prudential_payment_failed', 'Prudential Bank payment failed for order: ' . $order_id . ', Callback verification failed');
        }

        flash(translate('Payment failed'))->error();
        return redirect()->route('home');
    }

    public function prudential_return(Request $request)
    {
        // Handle successful return from Prudential Bank
        flash(translate('Payment completed successfully'))->success();
        return redirect()->route('home');
    }

    public function prudential_cancel(Request $request)
    {
        // Handle cancelled payment from Prudential Bank
        flash(translate('Payment was cancelled'))->error();
        return redirect()->route('home');
    }

    public function create_order(Request $request)
    {
        try {
            $order_data = $request->input('order_data');
            $order_id = $request->input('order_id');
            $amount = $request->input('amount');
            $first_name = $request->input('first_name');
            $last_name = $request->input('last_name');
            $phone = $request->input('phone');
            $email = $request->input('email');
            $address = $request->input('address');
            $city = $request->input('city');

            // Create order via Prudential Bank API
            $result = PrudentialUtility::create_order($order_data);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'order_id' => $result['order_id'],
                    'redirect_url' => $result['redirect_url'],
                    'message' => 'Order created successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => 'Please try again later. ' . $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred while creating the order'
            ], 500);
        }
    }

    public function callback_testing()
    {
        // Test callback functionality
        $test_data = [
            'order_id' => 'TEST_' . rand(100000, 999999),
            'status' => 'success',
            'amount' => '88.00',
            'currency' => 'USD',
            'transaction_id' => 'TXN_' . rand(100000, 999999)
        ];

        // Simulate a callback request
        $request = new \Illuminate\Http\Request();
        $request->merge($test_data);

        // Test the callback method
        return $this->prudential_callback($request);
    }

    public function admin_callback_monitor()
    {
        // Admin-only callback monitoring
        if (!auth()->check() || !auth()->user()->user_type == 'admin') {
            abort(403, 'Access denied');
        }

        // Get recent callback logs
        $recent_callbacks = \App\Models\StaffActivityLog::where('action', 'like', '%prudential%')
            ->where('action', 'like', '%callback%')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('backend.payment.prudential.callback_monitor', compact('recent_callbacks'));
    }

    public function admin_callback_test()
    {
        // Admin-only callback testing
        if (!auth()->check() || !auth()->user()->user_type == 'admin') {
            abort(403, 'Access denied');
        }

        // Test callback with admin authentication
        $test_data = [
            'order_id' => 'ADMIN_TEST_' . rand(100000, 999999),
            'status' => 'success',
            'amount' => '100.00',
            'currency' => 'USD',
            'transaction_id' => 'ADMIN_TXN_' . rand(100000, 999999)
        ];

        // Simulate a callback request
        $request = new \Illuminate\Http\Request();
        $request->merge($test_data);

        // Test the callback method
        $result = $this->prudential_callback($request);

        return response()->json([
            'success' => true,
            'message' => 'Admin Prudential callback test completed',
            'test_data' => $test_data,
            'result' => $result
        ]);
    }
}
