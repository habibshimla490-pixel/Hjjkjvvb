# 📥 WinyPay Integration - সম্পূর্ণ ফাইল ডাউনলোড গাইড

**সকল ফাইল সরাসরি ডাউনলোড করুন এবং আপনার প্রজেক্টে পেস্ট করুন**

---

## 🔗 সরাসরি ডাউনলোড লিংক

### ✅ কনফিগারেশন ফাইল (১টি)

| ফাইল | লিংক | বর্ণনা |
|------|------|--------|
| **config/payment-gateway.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/config/payment-gateway.php) | WinyPay সকল সেটিংস |

---

### ✅ ব্যাকএন্ড ক্লাস ফাইল (৫টি)

| ফাইল | লিংক | বর্ণনা |
|------|------|--------|
| **src/Database/Connection.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/src/Database/Connection.php) | PDO ডাটাবেস সংযোগ |
| **src/Logger/PaymentLogger.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/src/Logger/PaymentLogger.php) | সম্পূর্ণ লগিং সিস্টেম |
| **src/Payment/DepositHandler.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/src/Payment/DepositHandler.php) | ডেপোজিট প্রসেসিং |
| **src/Payment/WithdrawHandler.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/src/Payment/WithdrawHandler.php) | উইথড্র প্রসেসিং |
| **src/Security/CallbackValidator.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/src/Security/CallbackValidator.php) | HMAC স্বাক্ষর যাচাইকরণ |

---

### ✅ API এন্ডপয়েন্ট ফাইল (৬টি)

| ফাইল | লিংক | বর্ণনা |
|------|------|--------|
| **api/payment/deposit.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/api/payment/deposit.php) | ডেপোজিট শুরু করুন |
| **api/payment/deposit-callback.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/api/payment/deposit-callback.php) | ডেপোজিট কলব্যাক হ্যান্ডলার |
| **api/payment/withdraw.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/api/payment/withdraw.php) | উইথড্র শুরু করুন |
| **api/payment/withdraw-callback.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/api/payment/withdraw-callback.php) | উইথড্র কলব্যাক হ্যান্ডলার |
| **api/payment/bind-account.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/api/payment/bind-account.php) | পেমেন্ট অ্যাকাউন্ট বাঁধুন |
| **api/payment/wallet.php** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/api/payment/wallet.php) | ওয়ালেট বিস্তারিত পান |

---

### ✅ ডাটাবেস ফাইল (১টি)

| ফাইল | লিংক | বর্ণনা |
|------|------|--------|
| **database/migrations/001_create_payment_tables.sql** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/database/migrations/001_create_payment_tables.sql) | সম্পূর্ণ ডাটাবেস স্কিমা |

---

### ✅ ফ্রন্টএন্ড ফাইল (১টি)

| ফাইল | লিংক | বর্ণনা |
|------|------|--------|
| **js/winypay-integration.js** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/js/winypay-integration.js) | HTML এর সাথে সংযোগ |

---

### ✅ ডকুমেন্টেশন ফাইল (২টি)

| ফাইল | লিংক | বর্ণনা |
|------|------|--------|
| **INTEGRATION_GUIDE.md** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/INTEGRATION_GUIDE.md) | সম্পূর্ণ সেটআপ নির্দেশনা |
| **README.md** | [📥 ডাউনলোড](https://raw.githubusercontent.com/habibshimla490-pixel/Hjjkjvvb/main/README.md) | প্রকল্প সংক্ষিপ্তসার |

---

## 📦 সম্পূর্ণ প্রজেক্ট ডাউনলোড

### অপশন ১: GitHub থেকে সরাসরি ক্লোন করুন
```bash
git clone https://github.com/habibshimla490-pixel/Hjjkjvvb.git
cd Hjjkjvvb
```

### অপশন ২: ZIP হিসেবে ডাউনলোড করুন
[📥 সম্পূর্ণ প্রজেক্ট ZIP ডাউনলোড করুন](https://github.com/habibshimla490-pixel/Hjjkjvvb/archive/refs/heads/main.zip)

---

## 📂 ফাইল স্ট্রাকচার - যেখানে রাখবেন

আপনার প্রজেক্টে এই স্ট্রাকচার অনুযায়ী ফাইল রাখুন:

```
আপনার প্রজেক্ট/
│
├── config/
│   └── payment-gateway.php          ← যেখানে: config/ ফোল্ডার
│
├── src/
│   ├── Database/
│   │   └── Connection.php            ← যেখানে: src/Database/
│   ├── Logger/
│   │   └── PaymentLogger.php         ← যেখানে: src/Logger/
│   ├── Payment/
│   │   ├── DepositHandler.php        ← যেখানে: src/Payment/
│   │   └── WithdrawHandler.php       ← যেখানে: src/Payment/
│   └── Security/
│       └── CallbackValidator.php     ← যেখানে: src/Security/
│
├── api/
│   └── payment/
│       ├── deposit.php               ← যেখানে: api/payment/
│       ├── deposit-callback.php      ← যেখানে: api/payment/
│       ├── withdraw.php              ← যেখানে: api/payment/
│       ├── withdraw-callback.php     ← যেখানে: api/payment/
│       ├── bind-account.php          ← যেখানে: api/payment/
│       └── wallet.php                ← যেখানে: api/payment/
│
├── database/
│   └── migrations/
│       └── 001_create_payment_tables.sql  ← যেখানে: database/migrations/
│
├── js/
│   └── winypay-integration.js        ← যেখানে: js/
│
├── logs/                             ← নতুন ফোল্ডার তৈরি করুন
│   ├── api_YYYY-MM-DD.log
│   ├── callback_YYYY-MM-DD.log
│   ├── transaction_YYYY-MM-DD.log
│   └── error_YYYY-MM-DD.log
│
└── HTML Files/
    └── wallet.html                   ← আপনার বিদ্যমান HTML ফাইল
```

---

## 🚀 দ্রুত ইনস্টলেশন

### ধাপ ১: সব ফাইল ডাউনলোড করুন
উপরের লিংক থেকে সব ফাইল ডাউনলোড করুন এবং সঠিক ফোল্ডারে রাখুন।

### ধাপ २: ডাটাবেস টেবিল তৈরি করুন
```bash
mysql -u root -p wallet_db < database/migrations/001_create_payment_tables.sql
```

### ধাপ ३: এনভায়রনমেন্ট সেট করুন
```bash
export WINYPAY_MERCHANT_CODE="M1001"
export WINYPAY_SECRET_KEY="abc123"
export WINYPAY_PAYOUT_KEY="abc123"
export DB_HOST="localhost"
export DB_NAME="wallet_db"
export DB_USER="root"
export DB_PASSWORD=""
```

### ধাপ ४: HTML এ যোগ করুন
আপনার `wallet.html` এর শেষে এই লাইন যোগ করুন:
```html
<script src="/js/winypay-integration.js"></script>
```

### ধাপ ५: লগ ফোল্ডার তৈরি করুন
```bash
mkdir -p logs
chmod 755 logs
```

### ধাপ ६: পরীক্ষা করুন
```bash
curl -X POST http://localhost/api/payment/deposit.php \
  -H "Content-Type: application/json" \
  -d '{"user_id": 123, "amount": 500, "payment_method": "bkash"}'
```

---

## 💻 Windows ইউজারদের জন্য

### ফোল্ডার তৈরি করুন (Command Prompt)
```cmd
mkdir config src\Database src\Logger src\Payment src\Security
mkdir api\payment database\migrations js logs
```

### ডাটাবেস এক্সিকিউট করুন
```cmd
mysql -u root -p wallet_db < database\migrations\001_create_payment_tables.sql
```

### এনভায়রনমেন্ট সেট করুন (পাওয়ারশেল)
```powershell
[Environment]::SetEnvironmentVariable("WINYPAY_MERCHANT_CODE", "M1001", "User")
[Environment]::SetEnvironmentVariable("WINYPAY_SECRET_KEY", "abc123", "User")
```

---

## 🔒 গুরুত্বপূর্ণ নোট

⚠️ **কনফিগারেশন:**
- `config/payment-gateway.php` ডাউনলোড করার পরে আপনার তথ্য দিয়ে আপডেট করুন
- সার্ভার পাওয়ার চালু রাখুন যাতে কনফিগারেশন কাজ করে

⚠️ **সিকিউরিটি:**
- কখনও `secret_key` এবং `payout_key` শেয়ার করবেন না
- এনভায়রনমেন্ট ভেরিয়েবল ব্যবহার করুন (কোডে হার্ডকোড করবেন না)

⚠️ **ডাটাবেস:**
- ডাটাবেস ব্যাকআপ নিন ডাউনলোড করার আগে
- SQL ফাইল সাবধানে রান করুন

---

## 📊 ফাইল সাইজ তথ্য

| ফাইল | আকার | ডাউনলোড সময় |
|------|-------|--------------|
| config/payment-gateway.php | ~2 KB | তাৎক্ষণিক |
| src/Database/Connection.php | ~3 KB | তাৎক্ষণিক |
| src/Logger/PaymentLogger.php | ~4 KB | তাৎক্ষণিক |
| src/Payment/DepositHandler.php | ~13 KB | তাৎক্ষণিক |
| src/Payment/WithdrawHandler.php | ~16 KB | তাৎক্ষণিক |
| src/Security/CallbackValidator.php | ~4 KB | তাৎক্ষণিক |
| api/payment/deposit.php | ~3 KB | তাৎক্ষণিক |
| api/payment/deposit-callback.php | ~4 KB | তাৎক্ষণিক |
| api/payment/withdraw.php | ~3 KB | তাৎক্ষণিক |
| api/payment/withdraw-callback.php | ~4 KB | তাৎক্ষণিক |
| api/payment/bind-account.php | ~3 KB | তাৎক্ষণিক |
| api/payment/wallet.php | ~3 KB | তাৎক্ষণিক |
| database/migrations/001_create_payment_tables.sql | ~5 KB | তাৎক্ষণিক |
| js/winypay-integration.js | ~16 KB | তাৎক্ষণিক |
| INTEGRATION_GUIDE.md | ~23 KB | তাৎক্ষণিক |
| README.md | ~13 KB | তাৎক্ষণিক |
| **মোট** | **~127 KB** | **< ১ সেকেন্ড** |

---

## ✅ চেকলিস্ট - ইনস্টলেশনের পরে

- ✅ সব ফাইল সঠিক ফোল্ডারে রাখা হয়েছে
- ✅ ডাটাবেস টেবিল তৈরি হয়েছে
- ✅ এনভায়রনমেন্ট ভেরিয়েবল সেট করা হয়েছে
- ✅ লগ ফোল্ডার তৈরি এবং পারমিশন দেওয়া হয়েছে
- ✅ HTML এ JavaScript যুক্ত করা হয়েছে
- ✅ পরীক্ষা API কল সফল হয়েছে

---

## 🆘 সমস্যা হলে

1. **ফাইল না পাওয়া?** - GitHub রিপোজিটরি সরাসরি চেক করুন: https://github.com/habibshimla490-pixel/Hjjkjvvb

2. **ডাউনলোড ভেঙে যাওয়া?** - ZIP ফাইল ডাউনলোড করুন এবং এক্সট্র্যাক্ট করুন

3. **পারমিশন সমস্যা?** 
   ```bash
   chmod -R 755 logs/
   chmod -R 755 src/
   chmod -R 755 api/
   ```

4. **ডাটাবেস ত্রুটি?**
   ```bash
   mysql -u root -p -e "SHOW DATABASES;" # চেক করুন DB আছে কি
   mysql -u root -p wallet_db < database/migrations/001_create_payment_tables.sql
   ```

---

## 📞 সাপোর্ট

- GitHub Issue: https://github.com/habibshimla490-pixel/Hjjkjvvb/issues
- INTEGRATION_GUIDE.md পড়ুন
- প্রতিটি ফাইলে কোড মন্তব্য দেখুন

---

## 🎉 সফল ইনস্টলেশন!

সব কিছু সেটআপ হয়ে গেলে, আপনার ওয়ালেট অ্যাপ্লিকেশন সম্পূর্ণভাবে WinyPay এর সাথে কাজ করবে! ✅

**Happy Coding! 🚀**

---

**Created:** 2026-07-03  
**Repository:** https://github.com/habibshimla490-pixel/Hjjkjvvb
