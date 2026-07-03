# WinyPay Bangladesh Payment Gateway Integration Guide

**Version:** 1.0.0  
**Last Updated:** 2026-07-03  
**Status:** Production Ready

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [API Endpoints](#api-endpoints)
5. [Deposit Flow](#deposit-flow)
6. [Withdrawal Flow](#withdrawal-flow)
7. [Database Schema](#database-schema)
8. [Frontend Integration](#frontend-integration)
9. [Callback Handling](#callback-handling)
10. [Security Features](#security-features)
11. [Error Handling](#error-handling)
12. [Troubleshooting](#troubleshooting)
13. [Live Mode Transition](#live-mode-transition)

---

## Overview

This integration provides a complete WinyPay Bangladesh payment gateway solution with:

- **Deposit Processing:** Accept payments via bKash, Nagad, Rocket, USDT
- **Withdrawal Management:** Process payouts with balance reservation
- **Callback Handling:** Secure verification of payment confirmations
- **Wallet Management:** Real-time balance tracking
- **Audit Logging:** Complete transaction history
- **Security:** HMAC-SHA256 signature verification, SQL injection prevention, duplicate callback prevention

### Key Features

✅ Unique order ID generation  
✅ Transaction state management (pending → processing → success/failed)  
✅ Balance reservation for withdrawals  
✅ Daily withdrawal limits  
✅ Commission calculation (1% for withdrawals)  
✅ Duplicate callback prevention  
✅ Complete API request/response logging  
✅ Error recovery and rollback on failure  
✅ PDO prepared statements (SQL injection safe)  

---

## Installation

### Prerequisites

- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.2+
- cURL enabled
- PDO MySQL driver

### Step 1: Create Directory Structure

```bash
mkdir -p config src/{Database,Logger,Payment,Security} api/payment database/migrations logs
```

### Step 2: Copy Configuration Files

Copy `config/payment-gateway.php` to your project.

### Step 3: Create Database Tables

Execute `database/migrations/001_create_payment_tables.sql`:

```bash
mysql -u root -p wallet_db < database/migrations/001_create_payment_tables.sql
```

### Step 4: Copy Source Files

Copy all files from `src/` directory maintaining the structure:

```
src/
├── Database/Connection.php
├── Logger/PaymentLogger.php
├── Payment/DepositHandler.php
├── Payment/WithdrawHandler.php
└── Security/CallbackValidator.php
```

### Step 5: Copy API Endpoints

Copy all files from `api/payment/`:

```
api/payment/
├── deposit.php
├── deposit-callback.php
├── withdraw.php
├── withdraw-callback.php
├── bind-account.php
└── wallet.php
```

### Step 6: Include Frontend Script

Add this to your HTML before closing `</body>` tag:

```html
<script src="/js/winypay-integration.js"></script>
```

---

## Configuration

### Environment Variables

Create a `.env` file or set these in your system:

```env
# WinyPay Credentials
WINYPAY_MERCHANT_CODE=M1001
WINYPAY_SECRET_KEY=abc123
WINYPAY_PAYOUT_KEY=abc123
WINYPAY_BASE_URL=https://bd.gopostman.com

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=wallet_db
DB_USER=root
DB_PASSWORD=

# Application
APP_URL=https://yourdomain.com
LOG_PATH=/var/log/payment
```

### config/payment-gateway.php

The configuration file loads environment variables and provides sensible defaults:

```php
$config = require 'config/payment-gateway.php';
```

Key settings:

```php
'merchant_code' => 'M1001',      // Test mode (change for Live)
'secret_key' => 'abc123',         // Test mode (change for Live)
'payout_key' => 'abc123',         // Test mode (change for Live)
'base_url' => 'https://bd.gopostman.com', // Test mode
'min_deposit' => 100,             // Minimum BDT
'max_deposit' => 1000000,         // Maximum BDT
'daily_withdraw_limit' => 50000,  // Daily limit
'withdraw_commission_percent' => 1, // 1% commission
```

---

## API Endpoints

### 1. Initiate Deposit

**Endpoint:** `POST /api/payment/deposit.php`

**Request:**
```json
{
  "user_id": 123,
  "amount": 500,
  "payment_method": "bkash"
}
```

**Response (Success):**
```json
{
  "success": true,
  "order_id": "DEP-20260703170530-456789",
  "internal_txn_id": "DEP202607031234",
  "pay_url": "https://payment-provider.com/pay/xyz123",
  "message": "Redirect user to pay_url to complete payment"
}
```

**Response (Error):**
```json
{
  "success": false,
  "order_id": "DEP-20260703170530-456789",
  "message": "Amount must be at least 100"
}
```

### 2. Deposit Callback

**Endpoint:** `POST /api/payment/deposit-callback.php`

**Header:**
```
X-Callback-Sign: <HMAC-SHA256 signature>
```

**Request Body:**
```json
{
  "status": "success",
  "amount": "500.00",
  "user_id": "123",
  "pay_type": "bkash",
  "order_id": "DEP-20260703170530-456789",
  "transaction_id": "DEP202607031234"
}
```

**Response:**
```json
{
  "status": "success"
}
```

### 3. Initiate Withdrawal

**Endpoint:** `POST /api/payment/withdraw.php`

**Request:**
```json
{
  "user_id": 123,
  "amount": 500,
  "payment_method": "bkash",
  "account_name": "John Doe",
  "account_number": "01712345678"
}
```

**Response (Success):**
```json
{
  "success": true,
  "order_id": "WDR-20260703170530-456789",
  "internal_txn_id": "WDR202607031234",
  "message": "Withdrawal request submitted. Awaiting confirmation."
}
```

### 4. Withdrawal Callback

**Endpoint:** `POST /api/payment/withdraw-callback.php`

**Header:**
```
X-Callback-Sign: <HMAC-SHA256 signature>
```

**Request Body:**
```json
{
  "status": "success",
  "amount": "500.00",
  "user_id": "123",
  "pay_type": "bkash",
  "order_id": "WDR-20260703170530-456789",
  "transaction_id": "WDR202607031234"
}
```

### 5. Get Wallet Details

**Endpoint:** `GET /api/payment/wallet.php?user_id=123`

**Response:**
```json
{
  "success": true,
  "wallet": {
    "balance": 1500.50,
    "bonus": 100.00,
    "total_deposited": 5000.00,
    "total_withdrawn": 3500.00,
    "daily_limit": 50000,
    "daily_withdrawn": 500.00,
    "remaining_daily_limit": 49500.00
  },
  "transactions": [
    {
      "id": 1,
      "transaction_type": "deposit",
      "amount": 500.00,
      "status": "success",
      "created_at": "2026-07-03 17:05:30"
    }
  ]
}
```

### 6. Bind Payment Account

**Endpoint:** `POST /api/payment/bind-account.php`

**Request:**
```json
{
  "user_id": 123,
  "payment_method": "bkash",
  "account_name": "John Doe",
  "account_number": "01712345678"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Account bound successfully",
  "payment_method": "bkash",
  "account_name": "John Doe",
  "account_number": "01712345678"
}
```

---

## Deposit Flow

```
1. User enters amount on Deposit page
2. User selects payment method (bKash/Nagad/Rocket/USDT)
3. Click "Deposit" button
4. Frontend calls POST /api/payment/deposit.php
   ├─ Backend validates amount & method
   ├─ Generates unique order_id
   ├─ Saves transaction as PENDING
   ├─ Calls WinyPay API
   └─ Returns pay_url
5. Frontend redirects to pay_url
6. User completes payment on payment provider gateway
7. Payment provider sends callback to /api/payment/deposit-callback.php
   ├─ Backend verifies signature
   ├─ Checks for duplicate callback
   ├─ Validates amount
   ├─ Credits wallet
   └─ Updates transaction to SUCCESS
8. User redirected back to wallet page
9. Wallet balance updated
```

### Deposit Handler Code Flow

```php
DepositHandler::initiateDeposit()
├─ Validate inputs
├─ Generate order_id
├─ BEGIN TRANSACTION
├─ Save transaction (PENDING)
├─ Build API request
├─ Call WinyPay API
├─ Update with internal_txn_id
├─ COMMIT
└─ Return pay_url

DepositHandler::processDepositCallback()
├─ Find transaction by order_id
├─ Validate amount
├─ BEGIN TRANSACTION
├─ Credit wallet
├─ Update transaction (SUCCESS)
├─ COMMIT
└─ Return success
```

---

## Withdrawal Flow

```
1. User enters amount on Withdrawal page
2. User binds payment account (bKash/Nagad/Rocket/USDT)
   └─ Calls POST /api/payment/bind-account.php
3. Account saved and displayed
4. User clicks "Withdraw"
5. Frontend calls POST /api/payment/withdraw.php
   ├─ Backend validates amount & balance
   ├─ Checks daily limit
   ├─ Generates unique order_id
   ├─ BEGIN TRANSACTION
   ├─ Reserves balance (deducts from wallet)
   ├─ Saves transaction as PENDING
   ├─ Calls WinyPay Payout API
   └─ Returns order_id (balance now frozen)
6. Transaction marked PROCESSING
7. WinyPay processes payout
8. WinyPay sends callback to /api/payment/withdraw-callback.php
   ├─ Backend verifies signature
   ├─ Checks for duplicate
   ├─ If success: Updates transaction (SUCCESS)
   ├─ If failed: Releases balance back to wallet
   └─ Updates transaction (FAILED)
9. User notified of status
10. Wallet updated
```

### Withdrawal Handler Code Flow

```php
WithdrawHandler::initiateWithdraw()
├─ Validate inputs
├─ Check balance
├─ Check daily limit
├─ Generate order_id
├─ BEGIN TRANSACTION
├─ Reserve balance (deduct)
├─ Save transaction (PENDING)
├─ Build API request
├─ Call WinyPay Payout API
├─ If success:
│  ├─ Update with internal_txn_id
│  ├─ Mark PROCESSING
│  ├─ COMMIT
│  └─ Return order_id
└─ If error:
   ├─ Release balance
   ├─ Mark FAILED
   ├─ COMMIT
   └─ Return error

WithdrawHandler::processWithdrawCallback()
├─ Find transaction by order_id
├─ Validate amount
├─ BEGIN TRANSACTION
├─ If success:
│  ├─ Update transaction (SUCCESS)
│  ├─ Update daily withdrawal tracking
│  ├─ COMMIT
│  └─ Return success
└─ If failed:
   ├─ Release balance (refund)
   ├─ Update transaction (FAILED)
   ├─ COMMIT
   └─ Return success
```

---

## Database Schema

### payment_transactions

Stores all deposit and withdrawal transactions:

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | User identifier |
| transaction_type | ENUM | 'deposit' or 'withdraw' |
| order_id | VARCHAR(50) | Unique order ID |
| internal_txn_id | VARCHAR(100) | WinyPay internal ID |
| transaction_id | VARCHAR(100) | WinyPay final transaction ID |
| gateway | VARCHAR(50) | Always 'winypay' |
| payment_method | VARCHAR(50) | bkash, nagad, rocket, usdt |
| amount | DECIMAL(12,2) | Transaction amount |
| commission_amount | DECIMAL(12,2) | Withdrawal commission |
| net_amount | DECIMAL(12,2) | Amount after commission |
| status | ENUM | pending, processing, success, failed, cancelled |
| api_request_payload | LONGTEXT | JSON request sent to WinyPay |
| api_response_payload | LONGTEXT | JSON response from WinyPay |
| callback_payload | LONGTEXT | JSON callback received |
| error_message | TEXT | Error message if failed |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

### user_wallets

User wallet balances and limits:

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | User identifier |
| balance | DECIMAL(12,2) | Current balance |
| bonus | DECIMAL(12,2) | Bonus amount |
| total_deposited | DECIMAL(12,2) | Total deposits lifetime |
| total_withdrawn | DECIMAL(12,2) | Total withdrawals lifetime |
| daily_withdraw_amount | DECIMAL(12,2) | Amount withdrawn today |
| daily_withdraw_date | DATE | Date of daily tracking |

### user_payment_accounts

Bound payment accounts:

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | User identifier |
| payment_method | VARCHAR(50) | bkash, nagad, rocket, usdt |
| account_name | VARCHAR(255) | Account holder name |
| account_number | VARCHAR(100) | Phone/wallet number |
| is_primary | BOOLEAN | Primary account flag |
| is_active | BOOLEAN | Active status |

### callback_tracking

Prevents duplicate callback processing:

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| transaction_id | VARCHAR(100) | Transaction ID (unique) |
| order_id | VARCHAR(50) | Order ID reference |
| callback_signature | VARCHAR(255) | HMAC signature |
| raw_payload | LONGTEXT | Raw callback JSON |
| processed_at | TIMESTAMP | Processing timestamp |

### api_logs

Audit trail of all API requests/responses:

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | User identifier |
| transaction_id | VARCHAR(100) | Transaction reference |
| endpoint | VARCHAR(255) | API endpoint called |
| method | VARCHAR(10) | HTTP method |
| request_payload | LONGTEXT | Request JSON |
| response_payload | LONGTEXT | Response JSON |
| http_status_code | INT | HTTP response code |
| error_message | TEXT | Error message if any |
| execution_time | INT | Milliseconds to complete |
| ip_address | VARCHAR(45) | Client IP address |
| created_at | TIMESTAMP | Timestamp |

---

## Frontend Integration

### Include Script

Add to your HTML file before closing `</body>`:

```html
<script src="/js/winypay-integration.js"></script>
```

### Initialize

The script auto-initializes and attaches to existing UI elements:

```javascript
WinyPayIntegration.init();
```

### Deposit Button Integration

Existing deposit button will work automatically:

```html
<button class="cta-btn" id="depositBtn">Deposit ৳100</button>
```

When clicked:
1. Reads amount from `#amountDisplay`
2. Reads payment method from active `.method-btn`
3. Calls `/api/payment/deposit.php`
4. Redirects to `pay_url` on success

### Withdrawal Integration

Existing withdrawal form works automatically:

```html
<button class="cta-btn" id="withdrawBtn">Withdraw</button>
```

When clicked:
1. Checks if account is bound
2. Reads amount from active `.withdraw-amount-btn`
3. Calls `/api/payment/withdraw.php`
4. Updates wallet balance

### Bind Account Modal

The modal form submits automatically:

```html
<button class="cta-btn" id="submitBindFormBtn">Submit</button>
```

When clicked:
1. Reads payment method from active `.bind-method-card`
2. Reads account details from form inputs
3. Calls `/api/payment/bind-account.php`
4. Updates UI to show bound account

### Available JavaScript Functions

```javascript
// Initiate deposit
WinyPayIntegration.initiateDeposit(amount, paymentMethod)

// Bind payment account
WinyPayIntegration.bindPaymentAccount(paymentMethod, accountName, accountNumber)

// Initiate withdrawal
WinyPayIntegration.initiateWithdraw(amount, paymentMethod, accountName, accountNumber)

// Refresh wallet balance
WinyPayIntegration.refreshWalletBalance()

// Show message
WinyPayIntegration.showMessage(message, type)
// type: 'success', 'error', 'warning', 'info'
```

### User ID Handling

The script retrieves user ID from (in order):

1. `localStorage.getItem('user_id')`
2. `sessionStorage.getItem('user_id')`
3. Default to ID 1

**Update this in `winypay-integration.js`:**

```javascript
function getUserIdFromSession() {
  // Replace with your actual implementation
  return sessionStorage.getItem('authenticated_user_id');
}
```

---

## Callback Handling

### Signature Verification

WinyPay signs callbacks using HMAC-SHA256:

```
Signature = HMAC-SHA256(raw_json_body, secret_key)
```

**Verification Code:**

```php
$validator = new CallbackValidator($secretKey);

if (!$validator->validateSignature($rawBody, $signature)) {
    // Signature mismatch - reject callback
    http_response_code(401);
    exit;
}
```

### Duplicate Prevention

The system tracks processed callbacks using the `callback_tracking` table:

```php
if ($validator->isDuplicateCallback($transactionId)) {
    // Already processed - return success to prevent retry
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    exit;
}

// Mark as processed
$validator->markCallbackProcessed($transactionId, $orderId, $signature, $rawBody);
```

### Callback Response

Always return HTTP 200 with success status:

```php
http_response_code(200);
echo json_encode(['status' => 'success']);
```

This tells WinyPay the callback was received and prevents retries.

---

## Security Features

### 1. SQL Injection Prevention

All queries use PDO prepared statements:

```php
$query = "SELECT * FROM payment_transactions WHERE order_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$orderId]);
```

### 2. HMAC-SHA256 Signature Verification

Callbacks verified using `hash_hmac()`:

```php
$expectedSignature = hash_hmac('sha256', $rawJsonBody, $secretKey);
hash_equals($expectedSignature, $providedSignature);
```

### 3. Unique Order IDs

Generated using timestamp + random numbers:

```php
$orderId = 'DEP-' . date('YmdHis') . '-' . random_int(100000, 999999);
```

### 4. Duplicate Callback Prevention

Uses unique constraint on `transaction_id`:

```sql
UNIQUE KEY `unique_transaction_id` (`transaction_id`)
```

### 5. Amount Validation

Callback amounts validated against stored transaction:

```php
abs(floatval($expectedAmount) - floatval($callbackAmount)) < 0.01
```

### 6. Database Transactions

Critical operations use ACID transactions:

```php
$db->beginTransaction();
try {
    // Operations
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
}
```

### 7. Balance Reservation

Withdrawals reserve balance immediately:

```php
UPDATE user_wallets SET balance = balance - ? WHERE balance >= ?
```

This prevents overdrafts even if multiple requests occur.

### 8. Input Validation

All inputs validated before processing:

```php
if (!is_numeric($userId) || $userId <= 0) {
    throw new Exception("Invalid user ID");
}
```

---

## Error Handling

### Deposit Errors

| Error | Cause | Resolution |
|-------|-------|-----------|
| "Amount must be at least 100" | Below minimum | Increase amount |
| "Amount exceeds maximum limit" | Above limit | Reduce amount |
| "Invalid payment method" | Unknown method | Select valid method |
| "Provider relay failed" | WinyPay API error | Retry or contact support |

### Withdrawal Errors

| Error | Cause | Resolution |
|-------|-------|-----------|
| "Insufficient balance" | Not enough funds | Deposit more |
| "Daily withdrawal limit exceeded" | Over daily limit | Wait for next day |
| "Minimum withdrawal: 200" | Below minimum | Increase amount |
| "Provider relay failed" | WinyPay API error | Retry or contact support |

### Callback Errors

| Issue | Cause | Solution |
|-------|-------|----------|
| "Signature verification failed" | Wrong signature | Verify secret key |
| "Missing required fields" | Incomplete payload | Check WinyPay API docs |
| "Amount mismatch" | Wrong amount in callback | Contact WinyPay support |
| "Transaction not found" | Wrong order_id | Verify order_id matches |

---

## Troubleshooting

### 1. "Database Connection Failed"

**Cause:** Database credentials incorrect or server unavailable

**Solution:**
```php
// Check credentials in config/payment-gateway.php
// Verify database is running:
mysql -u root -p -e "SELECT 1"
// Check user permissions:
GRANT ALL ON wallet_db.* TO 'user'@'localhost';
```

### 2. Callbacks Not Being Received

**Cause:** Webhook URL not accessible or firewall blocking

**Solution:**
```bash
# Test webhook URL
curl -X POST https://yourdomain.com/api/payment/deposit-callback.php \
  -H "Content-Type: application/json" \
  -H "X-Callback-Sign: test" \
  -d '{"test": "data"}'

# Check firewall
sudo ufw allow 443
```

### 3. "Signature verification failed"

**Cause:** Wrong secret key in config

**Solution:**
```php
// Verify in config/payment-gateway.php
'secret_key' => 'abc123', // Must match WinyPay configuration
```

### 4. Duplicate Callback Error

**Cause:** Callback table has unique constraint violation

**Solution:**
```sql
-- Check for duplicates
SELECT transaction_id, COUNT(*) 
FROM callback_tracking 
GROUP BY transaction_id 
HAVING COUNT(*) > 1;

-- This should not happen - each transaction_id is unique
```

### 5. Balance Not Updated After Deposit

**Cause:** Callback not received or processed

**Solution:**
```php
// Check callback logs
$logs = file_get_contents(__DIR__ . '/../logs/callback_' . date('Y-m-d') . '.log');

// Check transaction status
SELECT * FROM payment_transactions WHERE order_id = 'DEP-...'\G

// Manually process if needed (only as emergency)
UPDATE user_wallets SET balance = balance + 500 WHERE user_id = 123;
```

### 6. "Port 3306: Connection refused"

**Cause:** MySQL not running

**Solution:**
```bash
# Start MySQL
sudo service mysql start
# or
sudo systemctl start mysql

# Verify running
sudo service mysql status
```

### 7. Withdrawal Balance Not Reserved

**Cause:** SQL constraint issue or database error

**Solution:**
```php
// Check wallet record exists
SELECT * FROM user_wallets WHERE user_id = 123\G

// If not exists, create
INSERT INTO user_wallets (user_id, balance) VALUES (123, 0);
```

---

## Live Mode Transition

### Step 1: Get Live Credentials from WinyPay

Contact WinyPay to obtain:
- Live Merchant Code
- Live Secret Key
- Live Payout Key
- Live API Base URL

### Step 2: Update Configuration

Update `config/payment-gateway.php`:

```php
return [
    'winypay' => [
        'merchant_code' => getenv('WINYPAY_MERCHANT_CODE') ?: 'M1234', // Live code
        'secret_key' => getenv('WINYPAY_SECRET_KEY') ?: 'live_secret_123',
        'payout_key' => getenv('WINYPAY_PAYOUT_KEY') ?: 'live_payout_456',
        'base_url' => getenv('WINYPAY_BASE_URL') ?: 'https://api.winypay.com', // Live URL
        // ... rest of config
    ],
];
```

### Step 3: Update Environment Variables

On your production server, set:

```bash
export WINYPAY_MERCHANT_CODE="M1234"
export WINYPAY_SECRET_KEY="live_secret_123"
export WINYPAY_PAYOUT_KEY="live_payout_456"
export WINYPAY_BASE_URL="https://api.winypay.com"
```

### Step 4: Update Callback URLs

Update callback URLs in your account to production URLs:

```
Deposit Callback: https://yourlivedomcom/api/payment/deposit-callback.php
Withdraw Callback: https://yourlive domain.com/api/payment/withdraw-callback.php
```

### Step 5: SSL Certificate

Ensure your domain has valid SSL certificate:

```bash
# Check certificate
openssl s_client -connect yourdomain.com:443

# For free certificates, use Let's Encrypt
sudo certbot certonly --webroot -w /var/www/html -d yourdomain.com
```

### Step 6: Test Live Transactions

1. Make small test deposit (BDT 100)
2. Verify callback is received
3. Check wallet balance updated
4. Make small test withdrawal
5. Verify funds received

### Step 7: Monitor and Logs

Keep monitoring logs during first 24 hours:

```bash
tail -f logs/api_$(date +%Y-%m-%d).log
tail -f logs/callback_$(date +%Y-%m-%d).log
tail -f logs/transaction_$(date +%Y-%m-%d).log
```

---

## Support

For issues or questions:

1. Check logs: `logs/` directory
2. Review error messages in transaction records
3. Contact WinyPay support with order ID and transaction ID
4. Provide complete error logs for debugging

---

**End of Integration Guide**
