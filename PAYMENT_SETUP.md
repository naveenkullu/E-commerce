# 💳 Payment Gateway Setup Guide

## 🚀 Razorpay Integration (Recommended for India)

### Step 1: Create Razorpay Account
1. Go to https://razorpay.com/
2. Sign up for a free account
3. Complete KYC verification (for live mode)

### Step 2: Get API Keys
1. Login to Razorpay Dashboard
2. Go to **Settings** → **API Keys**
3. Generate **Test Keys** (for testing)
4. Copy:
   - **Key ID** (starts with `rzp_test_`)
   - **Key Secret**

### Step 3: Configure in Your Site
1. Login to **Admin Panel**
2. Go to **Settings**
3. Scroll to **Payment Gateway Settings**
4. Enter:
   - **Razorpay Key ID**: `rzp_test_xxxxxxxxxx`
   - **Razorpay Key Secret**: `xxxxxxxxxxxxxxxxxx`
   - Enable **Razorpay**
5. Click **Save Settings**

### Step 4: Test Payment
1. Add products to cart
2. Go to checkout
3. Complete order
4. You'll be redirected to payment page
5. Click **Pay Now**
6. Use Razorpay test cards:
   - **Card Number**: `4111 1111 1111 1111`
   - **CVV**: Any 3 digits
   - **Expiry**: Any future date
   - **Name**: Any name

### Step 5: Go Live
1. Complete KYC on Razorpay
2. Generate **Live Keys** from dashboard
3. Replace test keys with live keys in admin settings
4. Start accepting real payments!

---

## 💰 Payment Flow

```
Cart → Checkout → Create Order → Payment Page → Razorpay → Verify → Success
```

1. **Cart**: User adds products
2. **Checkout**: User enters details
3. **Create Order**: System creates order in database
4. **Payment Page**: Shows amount and payment button
5. **Razorpay**: Opens secure payment modal
6. **Verify**: Backend verifies payment signature
7. **Success**: Order marked as paid, user gets download links

---

## 🔒 Security Features

✅ **Signature Verification** - Every payment is verified
✅ **HTTPS Required** - Secure connection
✅ **No Card Data Storage** - Razorpay handles everything
✅ **PCI DSS Compliant** - Industry standard security

---

## 📊 Test Cards

### Success Cards
- **Visa**: `4111 1111 1111 1111`
- **Mastercard**: `5555 5555 5555 4444`
- **Rupay**: `6522 2100 0000 0000`

### Failed Cards
- **Insufficient Funds**: `4000 0000 0000 9995`
- **Card Declined**: `4000 0000 0000 0002`

**CVV**: Any 3 digits  
**Expiry**: Any future date  
**OTP**: `123456` (for test mode)

---

## 🛠️ Troubleshooting

### Payment Not Working?
1. Check if Razorpay is enabled in settings
2. Verify API keys are correct
3. Check browser console for errors
4. Ensure XAMPP/server is running

### Order Created but Payment Failed?
- Order will remain in "pending" status
- User can retry payment from orders page
- Admin can manually mark as paid

### Signature Verification Failed?
- Check if Key Secret is correct
- Ensure no extra spaces in keys
- Try regenerating API keys

---

## 📱 Mobile Payments

Razorpay automatically supports:
- ✅ UPI (Google Pay, PhonePe, Paytm)
- ✅ Wallets (Paytm, Mobikwik, etc.)
- ✅ Net Banking
- ✅ Cards (Credit/Debit)
- ✅ EMI Options

---

## 💡 Tips

1. **Test Thoroughly** - Use test mode before going live
2. **Enable Webhooks** - For automatic payment updates
3. **Set Up Refunds** - Configure refund policy
4. **Monitor Dashboard** - Check Razorpay dashboard regularly
5. **Customer Support** - Keep Razorpay support contact handy

---

## 🎯 Next Steps

1. ✅ Setup Razorpay account
2. ✅ Get API keys
3. ✅ Configure in admin panel
4. ✅ Test with test cards
5. ✅ Complete KYC
6. ✅ Go live!

---

**Need Help?** Contact Razorpay Support: support@razorpay.com
