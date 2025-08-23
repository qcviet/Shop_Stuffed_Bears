# VNPay Integration for Shop Gấu Yêu

This document explains how the VNPay payment system has been integrated with your existing admin system.

## Overview

The VNPay integration automatically updates order payment status to "Đã thanh toán" (Paid) when payments are successful, without requiring any manual intervention in the admin panel.

## Files Modified

### 1. VNPay Configuration (`vnpay_php/config.php`)
- Updated return URL to point to your project
- Added IPN URL configuration

### 2. VNPay IPN Handler (`vnpay_php/vnpay_ipn.php`)
- Integrated with your database and OrderModel
- Automatically updates order payment status when VNPay confirms payment
- Includes error logging for debugging

### 3. VNPay Return Page (`vnpay_php/vnpay_return.php`)
- Updates order payment status when user returns from VNPay
- Provides navigation back to main site and admin panel

### 4. VNPay Payment Creation (`vnpay_php/vnpay_create_payment.php`)
- Modified to work with your order system
- Uses actual order ID instead of random numbers
- Integrates with your database

### 5. Checkout Page (`views/users/checkout.php`)
- Added VNPay as a payment option
- Redirects to VNPay when VNPay is selected
- Handles different payment methods appropriately

## How It Works

### 1. User Checkout Process
1. User selects VNPay as payment method
2. Order is created with payment status "Chưa thanh toán" (Unpaid)
3. User is redirected to VNPay payment gateway

### 2. Payment Processing
1. VNPay processes the payment
2. VNPay sends IPN (Instant Payment Notification) to your server
3. IPN handler automatically updates order payment status to "Đã thanh toán" (Paid)
4. User is redirected back to your site

### 3. Admin Panel
1. Order payment status is automatically updated
2. Admins can see which orders are paid
3. No manual intervention required

## Testing

### Test Payment Page
Access `vnpay_php/test_payment.php` to test the integration:

1. Click "Test VNPay Payment" button
2. You'll be redirected to VNPay sandbox
3. Use test credentials to complete payment
4. Check admin panel to see payment status updated

### Test Credentials
- **Bank:** NCB
- **Card Number:** 9704198526191432198
- **Account Name:** NGUYEN VAN A
- **Release Date:** 07/15
- **OTP:** 123456

## Configuration

### VNPay Settings
- **Terminal ID:** 0XCL66Z8
- **Secret Key:** CHCHYOKLBC3WXX5ET0DBADTHVYW9HYJG
- **Environment:** Sandbox (for testing)

### URLs
- **Return URL:** `http://localhost/shopgauyeu/vnpay_php/vnpay_return.php`
- **IPN URL:** `http://localhost/shopgauyeu/vnpay_php/vnpay_ipn.php`

## Production Setup

When moving to production:

1. Update VNPay configuration to use production URLs
2. Change `$vnp_Url` from sandbox to production
3. Update return and IPN URLs to your production domain
4. Ensure your server can receive IPN callbacks from VNPay

## Troubleshooting

### Check Logs
The system logs payment updates to PHP error log:
- `VNPay IPN: Order #X payment status updated to 'Đã thanh toán'`
- `VNPay Return: Order #X payment status updated to 'Đã thanh toán'`

### Common Issues
1. **IPN not working:** Check if your server can receive external callbacks
2. **Payment status not updating:** Verify database connection and OrderModel
3. **Amount mismatch:** VNPay returns amount in VND cents (divide by 100)

## Security Notes

- VNPay IPN includes signature verification
- Payment amounts are validated before updating
- Duplicate payment updates are prevented
- All database operations use prepared statements

## Support

If you encounter issues:
1. Check PHP error logs for VNPay messages
2. Verify VNPay configuration settings
3. Test with the provided test payment page
4. Ensure your database and models are working correctly
