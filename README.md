# WinyPay Bangladesh Payment Gateway Integration

**Complete, Production-Ready Integration for Wallet Application**

![Status](https://img.shields.io/badge/status-production%20ready-success)
![PHP](https://img.shields.io/badge/php-7.4%2B-blue)
![Database](https://img.shields.io/badge/database-MySQL%205.7%2B-orange)
![Security](https://img.shields.io/badge/security-HMAC%20SHA256-red)

---

## 📱 Overview

A complete payment gateway integration for WinyPay Bangladesh supporting:
- **Deposits:** bKash, Nagad, Rocket, USDT
- **Withdrawals:** Direct payout to bound accounts
- **Security:** HMAC-SHA256 verification, SQL injection prevention
- **Reliability:** Duplicate callback prevention, transaction rollback

---

## ✨ Features

### Core Features
- ✅ Deposit & Withdrawal processing
- ✅ Real-time balance management
- ✅ Daily withdrawal limits (configurable)
- ✅ Payment method binding
- ✅ Commission calculation (1% on withdrawals)
- ✅ Complete transaction history

### Security Features
- ✅ HMAC-SHA256 callback signature verification
- ✅ PDO prepared statements (SQL injection safe)
- ✅ Duplicate callback prevention with unique constraints
- ✅ ACID database transactions with rollback
- ✅ Balance reservation for withdrawals
- ✅ Input validation on all endpoints

### Operational Features
- ✅ Comprehensive API logging
- ✅ Transaction state tracking (pending → processing → success/failed)
- ✅ Automatic wallet creation for new users
- ✅ Daily withdrawal tracking
- ✅ Error recovery and graceful degradation

---

## 📂 Project Structure

```
Hjjkjvvb/
├── config/
│   └── payment-gateway.php          # Configuration file
├── src/
│   ├── Database/
│   │   └── Connection.php           # PDO database connection
│   ├── Logger/
│   │   └── PaymentLogger.php        # Comprehensive logging
│   ├── Payment/
│   │   ├── DepositHandler.php       # Deposit processing
│   │   └── WithdrawHandler.php      # Withdrawal processing
│   └── Security/
│       └── CallbackValidator.php    # Signature & duplicate verification
├── api/
│   └── payment/
│       ├── deposit.php              # Deposit initiation endpoint
│       ├── deposit-callback.php     # Deposit callback handler
│       ├── withdraw.php             # Withdrawal initiation endpoint
│       ├── withdraw-callback.php    # Withdrawal callback handler
│       ├── bind-account.php         # Account binding endpoint
│       └── wallet.php               # Wallet details endpoint
├── database/
│   └── migrations/
│       └── 001_create_payment_tables.sql  # Database schema
├── js/
│   └── winypay-integration.js       # Frontend integration
├── logs/
│   ├── api_YYYY-MM-DD.log          # API request/response logs
│   ├── callback_YYYY-MM-DD.log     # Callback logs
│   ├── transaction_YYYY-MM-DD.log  # Transaction logs
│   └── error_YYYY-MM-DD.log        # Error logs
├── INTEGRATION_GUIDE.md             # Complete setup guide
├── README.md                        # This file
└── API_DOCUMENTATION.md             # API reference
```

---

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- cURL enabled
- PDO MySQL driver

### Installation

**1. Clone or download the project:**
```bash
cd /var/www/html
git clone https://github.com/habibshimla490-pixel/Hjjkjvvb.git
cd Hjjkjvvb
```

**2. Create database tables:**
```bash
mysql -u root -p wallet_db < database/migrations/001_create_payment_tables.sql
```

**3. Create logs directory:**
```bash
mkdir -p logs
chmod 755 logs
```

**4. Configure environment variables:**
```bash
# Create .env file or set system variables
export WINYPAY_MERCHANT_CODE="M1001"
export WINYPAY_SECRET_KEY="abc123"
export WINYPAY_PAYOUT_KEY="abc123"
export WINYPAY_BASE_URL="https://bd.gopostman.com"
export DB_HOST="localhost"
export DB_NAME="wallet_db"
export DB_USER="root"
export DB_PASSWORD=""
```

**5. Include frontend script in your HTML:**
```html
<script src="/js/winypay-integration.js"></script>
```

**6. Test the integration:**
```bash
# Test deposit endpoint
curl -X POST http://localhost/api/payment/deposit.php \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 123,
    "amount": 500,
    "payment_method": "bkash"
  }'
```

---

## 🔌 API Endpoints

### Deposit Endpoints

**POST /api/payment/deposit.php** - Initiate deposit
```json
Request: {
  "user_id": 123,
  "amount": 500,
  "payment_method": "bkash"
}

Response: {
  "success": true,
  "order_id": "DEP-20260703170530-456789",
  "pay_url": "https://payment-provider.com/pay/xyz"
}
```

**POST /api/payment/deposit-callback.php** - Deposit callback (from WinyPay)
```json
Request: {
  "status": "success",
  "amount": "500.00",
  "user_id": "123",
  "order_id": "DEP-20260703170530-456789",
  "transaction_id": "DEP202607031234"
}

Header: X-Callback-Sign: <HMAC-SHA256>

Response: {
  "status": "success"
}
```

### Withdrawal Endpoints

**POST /api/payment/withdraw.php** - Initiate withdrawal
```json
Request: {
  "user_id": 123,
  "amount": 500,
  "payment_method": "bkash",
  "account_name": "John Doe",
  "account_number": "01712345678"
}

Response: {
  "success": true,
  "order_id": "WDR-20260703170530-456789"
}
```

**POST /api/payment/withdraw-callback.php** - Withdrawal callback (from WinyPay)
```json
Request: {
  "status": "success",
  "amount": "500.00",
  "user_id": "123",
  "order_id": "WDR-20260703170530-456789",
  "transaction_id": "WDR202607031234"
}

Header: X-Callback-Sign: <HMAC-SHA256>

Response: {
  "status": "success"
}
```

### Utility Endpoints

**GET /api/payment/wallet.php?user_id=123** - Get wallet details
```json
Response: {
  "success": true,
  "wallet": {
    "balance": 1500.50,
    "bonus": 100.00,
    "daily_limit": 50000,
    "remaining_daily_limit": 49500.00
  },
  "transactions": [...]
}
```

**POST /api/payment/bind-account.php** - Bind payment account
```json
Request: {
  "user_id": 123,
  "payment_method": "bkash",
  "account_name": "John Doe",
  "account_number": "01712345678"
}

Response: {
  "success": true,
  "message": "Account bound successfully"
}
```

---

## 🔄 Transaction Flow

### Deposit Flow
```
User enters amount → Select payment method → Click Deposit
     ↓
API: POST /api/payment/deposit.php
     ↓
Validate & generate order_id → Save transaction (PENDING)
     ↓
Call WinyPay API → Get pay_url
     ↓
Redirect user to pay_url
     ↓
User completes payment
     ↓
WinyPay sends callback → POST /api/payment/deposit-callback.php
     ↓
Verify signature → Check for duplicates → Validate amount
     ↓
Credit wallet → Update transaction (SUCCESS)
     ↓
User wallet updated
```

### Withdrawal Flow
```
User binds account → Enter amount → Click Withdraw
     ↓
API: POST /api/payment/withdraw.php
     ↓
Validate balance & daily limit → Reserve balance → Save transaction (PENDING)
     ↓
Call WinyPay Payout API
     ↓
If success: Mark PROCESSING
If error: Release balance, mark FAILED
     ↓
WinyPay processes payout
     ↓
WinyPay sends callback → POST /api/payment/withdraw-callback.php
     ↓
Verify signature → Check for duplicates → Validate amount
     ↓
If success: Mark SUCCESS (balance deducted)
If failed: Release balance (refund)
     ↓
User notified
```

---

## 🔐 Security Implementation

### Signature Verification
```php
// HMAC-SHA256 verification
$expectedSignature = hash_hmac('sha256', $rawBody, $secretKey);
hash_equals($expectedSignature, $providedSignature);
```

### SQL Injection Prevention
```php
// PDO Prepared Statements
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
```

### Duplicate Callback Prevention
```php
// Unique constraint on transaction_id
if ($validator->isDuplicateCallback($transactionId)) {
    return ['status' => 'success']; // Already processed
}
```

### Balance Safety
```php
// Withdrawal reserves balance immediately
UPDATE user_wallets 
SET balance = balance - ? 
WHERE user_id = ? AND balance >= ?;
```

### Transaction Safety
```php
$db->beginTransaction();
try {
    // Operations
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
}
```

---

## 📊 Database Tables

| Table | Purpose |
|-------|---------|
| `payment_transactions` | All deposits/withdrawals |
| `user_wallets` | User balances & limits |
| `user_payment_accounts` | Bound payment methods |
| `callback_tracking` | Processed callbacks |
| `api_logs` | API audit trail |

---

## 🛠️ Configuration

### Test Mode (Default)
```php
'merchant_code' => 'M1001',
'secret_key' => 'abc123',
'base_url' => 'https://bd.gopostman.com',
```

### Live Mode (After Approval)
```php
'merchant_code' => 'M1234', // Your live code
'secret_key' => 'live_secret_key',
'base_url' => 'https://api.winypay.com',
```

### Limits Configuration
```php
'min_deposit' => 100,                    // ৳100
'max_deposit' => 1000000,                // ৳1,000,000
'min_withdraw' => 200,                   // ৳200
'max_withdraw' => 500000,                // ৳500,000
'daily_withdraw_limit' => 50000,         // ৳50,000 per day
'withdraw_commission_percent' => 1,      // 1% commission
```

---

## 📝 Frontend Integration

### Auto-Initialization
```html
<script src="/js/winypay-integration.js"></script>
<!-- Script auto-initializes and attaches to existing UI -->
```

### Manual Function Calls
```javascript
// Deposit
WinyPayIntegration.initiateDeposit(500, 'bkash');

// Bind account
WinyPayIntegration.bindPaymentAccount('bkash', 'John Doe', '01712345678');

// Withdraw
WinyPayIntegration.initiateWithdraw(500, 'bkash', 'John Doe', '01712345678');

// Refresh balance
WinyPayIntegration.refreshWalletBalance();

// Show message
WinyPayIntegration.showMessage('Success!', 'success');
```

---

## 📋 Files Created & Why

| File | Purpose |
|------|---------|
| `config/payment-gateway.php` | Centralized configuration with environment variables |
| `src/Database/Connection.php` | Singleton PDO connection for database operations |
| `src/Logger/PaymentLogger.php` | Comprehensive logging for audit trail & compliance |
| `src/Payment/DepositHandler.php` | Complete deposit logic with WinyPay API integration |
| `src/Payment/WithdrawHandler.php` | Withdrawal logic with balance reservation & limits |
| `src/Security/CallbackValidator.php` | Signature verification & duplicate callback prevention |
| `api/payment/deposit.php` | User-facing deposit initiation endpoint |
| `api/payment/deposit-callback.php` | Receives & processes WinyPay deposit confirmations |
| `api/payment/withdraw.php` | User-facing withdrawal initiation endpoint |
| `api/payment/withdraw-callback.php` | Receives & processes WinyPay withdrawal confirmations |
| `api/payment/bind-account.php` | Stores user payment account details |
| `api/payment/wallet.php` | Provides wallet balance & transaction history |
| `js/winypay-integration.js` | Frontend integration connecting HTML to backend |
| `database/migrations/001_create_payment_tables.sql` | SQL schema for all payment tables |
| `INTEGRATION_GUIDE.md` | Complete setup & configuration guide |

---

## 🧪 Testing

### Test Deposit Flow
```bash
# 1. Initiate deposit
curl -X POST http://localhost/api/payment/deposit.php \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 123,
    "amount": 500,
    "payment_method": "bkash"
  }'

# 2. View logs
tail -f logs/api_$(date +%Y-%m-%d).log
```

### View Logs
```bash
# All API logs
tail -f logs/api_*.log

# Transaction logs
tail -f logs/transaction_*.log

# Error logs
tail -f logs/error_*.log
```

---

## 🐛 Troubleshooting

See **INTEGRATION_GUIDE.md** for detailed troubleshooting guide.

Common issues:
- Database connection failed → Check MySQL credentials
- Signature verification failed → Verify secret key matches
- Callback not received → Check firewall & webhook URL
- Insufficient balance → Check wallet balance in database

---

## 📈 Live Transition

1. Get Live Credentials from WinyPay
2. Update `config/payment-gateway.php`
3. Update Callback URLs in WinyPay dashboard
4. Install SSL Certificate
5. Test with small amounts
6. Monitor logs for first 24 hours

See **INTEGRATION_GUIDE.md** for complete steps.

---

## 📄 Documentation

- **README.md** (this file) - Overview & quick start
- **INTEGRATION_GUIDE.md** - Complete installation & setup guide
- **API_DOCUMENTATION.md** - Detailed API endpoint reference

---

## ✅ Checklist

- ✅ Deposit processing implemented
- ✅ Withdrawal processing implemented
- ✅ Callback handling with signature verification
- ✅ Duplicate callback prevention
- ✅ SQL injection prevention
- ✅ Balance reservation for safety
- ✅ Daily withdrawal limits
- ✅ Commission calculation
- ✅ Comprehensive logging
- ✅ Error handling & recovery
- ✅ Frontend integration
- ✅ Database schema
- ✅ Complete documentation

**Everything ready for production! 🚀**

---

**Created:** 2026-07-03  
**Integration:** WinyPay Bangladesh  
**Repository:** https://github.com/habibshimla490-pixel/Hjjkjvvb
