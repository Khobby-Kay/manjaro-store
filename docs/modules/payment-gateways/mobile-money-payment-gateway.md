# 📱 MOBILE MONEY PAYMENT GATEWAY - MODULE DOCUMENTATION

## **📋 OVERVIEW**

The Mobile Money Payment Gateway is a comprehensive payment integration for the Manjaro e-commerce platform, supporting MTN Mobile Money, Vodafone Cash, and AirtelTigo Money transactions. This module provides seamless mobile money payment processing for all e-commerce transactions.

## **🏗️ ARCHITECTURE**

### **Core Components**
- **Controller**: `app/Http/Controllers/Payment/MobileMoneyController.php`
- **Utility**: `app/Utility/MobileMoneyUtility.php`
- **Views**: `resources/views/frontend/mobile_money/`
- **Admin Config**: `resources/views/backend/setup_configurations/payment_method/partials/mobile_money.blade.php`
- **Routes**: `routes/web.php` (10 routes)
- **Database**: `payment_methods` and `business_settings` tables

### **API Integration**
- **Base URL**: `https://digihub.prudentialbank.com.gh/MobileMoneyPayment/api/Transaction`
- **Authentication**: HTTP Basic Authentication
- **Methods**: WalletNameEnquiry, DebitWallet, CheckTransactionStatus
- **Security**: IP whitelisting required

## **🔧 IMPLEMENTATION DETAILS**

### **1. Controller Structure**
```php
class MobileMoneyController extends Controller
{
    // Testing methods
    public function checkout_testing()
    public function wallet_testing()
    public function customer_package_payment_testing()
    public function seller_package_payment_testing()
    
    // API methods
    public function wallet_name_enquiry(Request $request)
    public function debit_wallet(Request $request)
    public function check_transaction_status(Request $request)
    
    // Callback methods
    public function mobile_money_callback(Request $request)
    public function mobile_money_return(Request $request)
    public function mobile_money_cancel(Request $request)
}
```

### **2. Utility Class Methods**
```php
class MobileMoneyUtility
{
    public static function wallet_name_enquiry($wallet_type, $wallet_number)
    public static function debit_wallet($wallet_type, $wallet_name, $wallet_number, $amount, $transaction_id, $remarks)
    public static function check_transaction_status($wallet_type, $client_reference)
}
```

### **3. Supported Payment Types**
- **Cart Payment** - Regular e-commerce transactions
- **Wallet Recharge** - User wallet top-up
- **Customer Package** - Customer subscription packages
- **Seller Package** - Seller subscription packages
- **Order Re-payment** - Failed order retry payments

### **4. Supported Wallet Types**
- **MTN Mobile Money** - Full support
- **Vodafone Cash** - Full support
- **AirtelTigo Money** - Full support

## **⚙️ CONFIGURATION**

### **Environment Variables**
```env
# Mobile Money Payment Gateway Configuration
MOBILE_MONEY_API_URL=https://digihub.prudentialbank.com.gh/MobileMoneyPayment/api/Transaction
MOBILE_MONEY_CLIENT_ID=476E3A87-CC97-48DB-8A15-9AE03516AA71
MOBILE_MONEY_USERNAME=momoapi.user.manjaro
MOBILE_MONEY_PASSWORD=!p@5s4M@nj@r0
MOBILE_MONEY_CURRENCY=GHS
MOBILE_MONEY_CALLBACK_URL=https://manjaro.store/mobile_money/callback
MOBILE_MONEY_SANDBOX_MODE=0
```

### **Database Entries**
```sql
-- Payment method entry
INSERT INTO payment_methods (name, active, created_at, updated_at) 
VALUES ('mobile_money', 1, NOW(), NOW());

-- Sandbox mode setting
INSERT INTO business_settings (type, value, created_at, updated_at) 
VALUES ('mobile_money_sandbox_mode', '0', NOW(), NOW());
```

## **🎨 FRONTEND IMPLEMENTATION**

### **Form Structure**
Each payment form includes:
- **Wallet Type Selection** - MTN, Vodafone, AirtelTigo
- **Mobile Number Input** - Required field
- **Account Name Input** - Optional field (can be skipped)
- **Amount Display** - Transaction amount
- **Submit Button** - Process payment

### **JavaScript Features**
- **Real-time validation** - Instant feedback
- **Loading states** - Clear user feedback
- **Error handling** - User-friendly error messages
- **Optional name checking** - Users can skip name verification

## **🔒 SECURITY FEATURES**

### **Authentication**
- **HTTP Basic Authentication** - Secure API access
- **CSRF Protection** - All forms protected
- **Input Validation** - Client and server-side validation

### **Error Handling**
- **Try-Catch Blocks** - All API calls protected
- **Secure Error Messages** - No sensitive data exposed
- **Logging** - Comprehensive error logging

## **🧪 TESTING**

### **Localhost Testing**
- **Test Mode** - Mock responses for local development
- **Bypass API Calls** - Skip actual API communication
- **Mock Success Responses** - Simulate successful transactions

### **Production Testing**
- **Real API Calls** - Full integration testing
- **Live Mobile Money** - Test with real numbers
- **Error Scenarios** - Test failure cases

## **📊 MONITORING & LOGGING**

### **Log Files**
- **Laravel Logs** - `storage/logs/laravel.log`
- **API Responses** - Logged for debugging
- **Error Tracking** - Comprehensive error logging

### **Monitoring Points**
- **API Response Times** - Performance monitoring
- **Success Rates** - Transaction success tracking
- **Error Rates** - Failure rate monitoring

## **🚀 DEPLOYMENT**

### **Pre-Deployment Checklist**
- [ ] All files uploaded to VPS
- [ ] Environment variables configured
- [ ] Database entries created
- [ ] Laravel caches cleared
- [ ] File permissions set correctly

### **Post-Deployment Verification**
- [ ] Admin panel shows Mobile Money option
- [ ] Payment forms load correctly
- [ ] API calls work with real mobile money
- [ ] Callback URLs accessible
- [ ] Error handling works properly

## **🔧 TROUBLESHOOTING**

### **Common Issues**
1. **"Try again later" error** - Usually IP whitelisting issue
2. **Payment form not loading** - Check file permissions
3. **API calls failing** - Verify credentials and IP whitelisting
4. **Database errors** - Check payment method entries

### **Solutions**
1. **Clear Laravel caches** - `php artisan optimize:clear`
2. **Check database entries** - Verify payment method exists
3. **Test API connectivity** - Verify server can reach API
4. **Check Laravel logs** - Review error details

## **📈 PERFORMANCE OPTIMIZATION**

### **Caching**
- **Route Caching** - `php artisan route:cache`
- **Config Caching** - `php artisan config:cache`
- **View Caching** - `php artisan view:cache`

### **Database Optimization**
- **Indexed Fields** - Proper database indexing
- **Query Optimization** - Efficient database queries

## **🔄 MAINTENANCE**

### **Regular Tasks**
- **Monitor API responses** - Check for API changes
- **Update credentials** - Keep API credentials current
- **Review logs** - Monitor for errors
- **Test functionality** - Regular testing

### **Updates**
- **API Changes** - Monitor for API updates
- **Security Updates** - Keep Laravel updated
- **Feature Updates** - Add new features as needed

## **📞 SUPPORT**

### **Internal Support**
- **Laravel Logs** - Check `storage/logs/laravel.log`
- **Database Queries** - Verify database entries
- **File Permissions** - Check file access

### **External Support**
- **Prudential Bank** - API-related issues
- **Laravel Community** - Framework issues
- **Server Provider** - Infrastructure issues

---

**Last Updated**: September 7, 2025
**Version**: 1.0
**Status**: Production Ready
**Maintainer**: Development Team
