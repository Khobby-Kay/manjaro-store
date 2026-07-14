# 🔧 Payment Gateway Configuration Guide

## 📋 **Environment Variables Setup**

Add these variables to your `.env` file:

```env
# ===========================================
# PRUDENTIAL BANK CONFIGURATION
# ===========================================
PRUDENTIAL_API_URL=https://3dss2.quipu.de:8443/order
PRUDENTIAL_ORDER_TYPE_RID=225
PRUDENTIAL_CURRENCY=GHS
PRUDENTIAL_CALLBACK_URL=https://manjaro.store/prudential/callback
PRUDENTIAL_RETURN_URL=https://manjaro.store/prudential/return
PRUDENTIAL_CANCEL_URL=https://manjaro.store/prudential/cancel
PRUDENTIAL_CERT_PATH=storage/app/certs/bank/cert.pem
PRUDENTIAL_KEY_PATH=storage/app/certs/bank/key.pem
PRUDENTIAL_CA_PATH=storage/app/certs/bank/ca.pem

# ===========================================
# MOBILE MONEY CONFIGURATION
# ===========================================
MOBILE_MONEY_API_URL=https://digihub.prudentialbank.com.gh/MobileMoneyPayment/api/Transaction
MOBILE_MONEY_USERNAME=momoapi.user.manjaro
MOBILE_MONEY_PASSWORD=!p@5s4M@nj@r0
MOBILE_MONEY_CLIENT_ID=476E3A87-CC97-48DB-8A15-9AE03516AA71
MOBILE_MONEY_CALLBACK_URL=https://manjaro.store/mobile_money/callback
MOBILE_MONEY_RETURN_URL=https://manjaro.store/mobile_money/return
MOBILE_MONEY_CANCEL_URL=https://manjaro.store/mobile_money/cancel

# ===========================================
# PAYMENT GATEWAY SETTINGS
# ===========================================
PAYMENT_GATEWAY_TIMEOUT=30
PAYMENT_GATEWAY_RETRY_ATTEMPTS=3
PAYMENT_GATEWAY_LOG_LEVEL=info
PAYMENT_GATEWAY_DEBUG_MODE=false
```

## 🔐 **SSL Certificate Setup**

### Prudential Bank Certificates:
1. Create directory: `storage/app/certs/bank/`
2. Place certificate files:
   - `cert.pem` - Client certificate
   - `key.pem` - Private key
   - `ca.pem` - Certificate authority (optional)

### File Permissions:
```bash
chmod 600 storage/app/certs/bank/*.pem
chown www-data:www-data storage/app/certs/bank/*.pem
```

## 🚀 **Production Deployment Checklist**

- [ ] Environment variables configured
- [ ] SSL certificates installed
- [ ] API endpoints updated for production
- [ ] Callback URLs configured
- [ ] Error logging enabled
- [ ] Payment monitoring setup
- [ ] Security headers configured
- [ ] Rate limiting enabled
- [ ] **Admin Payment Method Images** - Fixed broken images in admin panel
- [ ] **Production .env** - Optimized configuration provided

## 🧪 **Testing Checklist**

- [ ] Test Prudential Bank payment flow
- [ ] Test Mobile Money payment flow
- [ ] Test error scenarios
- [ ] Test callback processing
- [ ] Test refund scenarios
- [ ] Test timeout handling
- [ ] Test invalid data handling
