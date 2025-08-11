# Payment Gateway Integration - GMPay

This document describes the implementation of the GMPay payment gateway integration for wallet top-ups in the auction application.

## Overview

The payment gateway allows users to load their wallets with tokens using mobile money payments. The system uses GMPay as the payment processor with the following specifications:

- **Token Rate**: 1 token = 100 UGX
- **Minimum Top-up**: 500 UGX (5 tokens)
- **Payment Gateway**: GMPay (https://debit.gmpayapp.site)

## Architecture

### Backend Components

1. **PaymentController** (`app/Controllers/Api/PaymentController.php`)
   - Handles payment initialization
   - Processes webhooks from GMPay
   - Manages payment verification

2. **CronController** (`app/Controllers/Api/CronController.php`)
   - URL-based cron job for checking pending transactions
   - Runs every minute to update payment statuses

3. **PaymentModel** (`app/Models/PaymentModel.php`)
   - Database operations for transactions
   - Token calculation and validation

### Frontend Components

1. **PaymentService** (`auction_app/lib/services/payment_service.dart`)
   - API communication with backend
   - GMPay integration methods
   - Payment validation utilities

2. **Payment Model** (`auction_app/lib/models/payment.dart`)
   - Payment data structure
   - Status management

## API Endpoints

### Backend Endpoints

#### Initialize Top-up
```
POST /api/payment/initializeTopUp
```

**Request:**
```json
{
  "user_id": 1,
  "amount": 1000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "payment_id": 123,
    "transaction_id": "20250130123456789",
    "amount": 1000,
    "tokens": 10,
    "gmpay_payload": {
      "msisdn": "256705721545",
      "amount": "1000",
      "transactionId": "20250130123456789"
    },
    "gmpay_url": "https://debit.gmpayapp.site/public/deposit/custom"
  },
  "message": "Top-up payment initialized successfully"
}
```

#### Verify Payment
```
GET /api/payment/verify/{transactionId}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "transaction_id": "20250130123456789",
    "status": "SUCCESS",
    "amount": 1000,
    "tokens": 10,
    "payment_type": "topup",
    "created_at": "2025-01-30 12:34:56",
    "gmpay_status": "SUCCESS"
  }
}
```

#### GMPay Webhook
```
POST /api/payment/gmpayWebhook
```

**Request:**
```json
{
  "status": "success",
  "transaction": {
    "id": "518",
    "msisdn": "256704620023",
    "amount": "5000.00",
    "transaction_id": "1753880702846",
    "reference": "9dbe0a41-f62c-4408-a6cf-6b66271de506",
    "status": "SUCCESS",
    "created_at": "2025-07-30 16:05:05"
  }
}
```

#### Cron Endpoints
```
GET /api/cron/check-payments
GET /api/cron/health
```

### Frontend API Configuration

The Flutter app uses the following endpoints:

```dart
// Payment gateway endpoints
static const String initializeTopUp = '/api/payment/initializeTopUp';
static const String verifyPayment = '/api/payment/verify'; // /{transactionId}
static const String paymentHistory = '/api/payment/getPaymentHistory';
static const String walletStats = '/api/payment/getWalletStats';
```

## GMPay Integration

### Payment Flow

1. **Initialize Payment**
   - User requests top-up with amount
   - Backend generates 13-digit transaction ID
   - Creates pending transaction record
   - Returns GMPay payload

2. **Submit to GMPay**
   - Frontend sends payload to GMPay endpoint
   - GMPay processes mobile money payment
   - User receives payment prompt on phone

3. **Status Monitoring**
   - Cron job checks pending transactions every minute
   - Backend polls GMPay status endpoint
   - Updates transaction status based on response

4. **Completion**
   - On successful payment, tokens are added to user wallet
   - Transaction status updated to 'approved'

### GMPay Endpoints

#### Deposit Endpoint
```
POST https://debit.gmpayapp.site/public/deposit/custom
```

**Payload:**
```json
{
  "msisdn": "256705721545",
  "amount": "1000",
  "transactionId": "20250130123456789"
}
```

#### Status Check Endpoint
```
GET https://debit.gmpayapp.site/public/transaction-status/{transactionId}
```

**Response:**
```json
{
  "status": "success",
  "transaction": {
    "id": "518",
    "msisdn": "256704620023",
    "amount": "5000.00",
    "transaction_id": "1753880702846",
    "reference": "9dbe0a41-f62c-4408-a6cf-6b66271de506",
    "status": "SUCCESS",
    "created_at": "2025-07-30 16:05:05"
  }
}
```

## Database Schema

### Transactions Table
```sql
CREATE TABLE transactions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  tokens INT NOT NULL,
  status ENUM('pending', 'success', 'failed', 'approved', 'rejected') NOT NULL,
  transaction_id VARCHAR(100),
  payment_type ENUM('topup', 'withdrawal'),
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Cron Job Setup

### URL-based Cron
Set up a cron job to call the status check endpoint every minute:

```bash
# Add to crontab
* * * * * curl -s "https://your-domain.com/auction/public/api/cron/check-payments" > /dev/null 2>&1
```

### Health Check
Monitor the cron job health:

```bash
curl "https://your-domain.com/auction/public/api/cron/health"
```

## Error Handling

### Common Error Scenarios

1. **Invalid Amount**
   - Minimum amount: 500 UGX
   - Maximum amount: No limit (configurable)

2. **Phone Number Format**
   - Automatically formats to 256 prefix
   - Removes leading zeros

3. **Transaction ID Conflicts**
   - Generates unique 13-digit IDs
   - Checks for existing transactions

4. **GMPay Failures**
   - Network timeouts
   - Invalid responses
   - Status polling failures

### Error Responses

```json
{
  "success": false,
  "message": "Amount must be at least 500 UGX",
  "status_code": 400
}
```

## Security Considerations

1. **Input Validation**
   - Amount validation (minimum 500 UGX)
   - User ID validation
   - Phone number formatting

2. **Transaction Security**
   - Unique transaction IDs
   - Status verification
   - Webhook signature validation (if available)

3. **Database Transactions**
   - Atomic operations for token updates
   - Rollback on failures

## Testing

### Test Cases

1. **Valid Top-up**
   - Amount: 1000 UGX
   - Expected tokens: 10
   - Status: SUCCESS

2. **Invalid Amount**
   - Amount: 300 UGX
   - Expected: Error (below minimum)

3. **Phone Number Formatting**
   - Input: "0705721545"
   - Output: "256705721545"

4. **Cron Job**
   - Check pending transactions
   - Update statuses
   - Approve successful payments

### Manual Testing

1. **Initialize Payment**
   ```bash
   curl -X POST "https://your-domain.com/auction/public/api/payment/initializeTopUp" \
     -H "Content-Type: application/json" \
     -d '{"user_id": 1, "amount": 1000}'
   ```

2. **Check Status**
   ```bash
   curl "https://your-domain.com/auction/public/api/payment/verify/20250130123456789"
   ```

3. **Cron Health**
   ```bash
   curl "https://your-domain.com/auction/public/api/cron/health"
   ```

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# GMPay Configuration
GMPAY_DEPOSIT_URL=https://debit.gmpayapp.site/public/deposit/custom
GMPAY_STATUS_URL=https://debit.gmpayapp.site/public/transaction-status
GMPAY_TIMEOUT=10

# Payment Settings
MIN_TOPUP_AMOUNT=500
TOKEN_RATE=100
```

### Validation Rules

```php
// PaymentController.php
$rules = [
    'user_id' => 'required|integer|is_natural_no_zero',
    'amount' => 'required|numeric|greater_than[499]', // Minimum 500 UGX
];
```

## Monitoring and Logging

### Log Messages

The system logs important events:

```php
// Successful payment
log_message('info', 'Payment successful: Transaction ID ' . $transactionId);

// Failed payment
log_message('error', 'Payment failed: ' . $errorMessage);

// Cron job results
log_message('info', 'Cron job checked ' . $checked . ' transactions');
```

### Health Monitoring

Monitor the cron job health endpoint for:
- Pending transaction count
- Server timestamp
- System status

## Troubleshooting

### Common Issues

1. **Cron Job Not Running**
   - Check server cron service
   - Verify URL accessibility
   - Check server logs

2. **Payment Status Not Updating**
   - Verify GMPay endpoint availability
   - Check network connectivity
   - Review transaction logs

3. **Token Not Added**
   - Check transaction approval process
   - Verify user model update method
   - Review database transactions

### Debug Steps

1. Check cron job logs
2. Verify GMPay API responses
3. Review database transaction records
4. Test payment flow manually

## Future Enhancements

1. **Webhook Signature Validation**
   - Add signature verification for webhooks
   - Enhance security

2. **Payment Retry Logic**
   - Implement retry mechanism for failed payments
   - Automatic retry on network failures

3. **Payment Notifications**
   - Email/SMS notifications for payment status
   - User dashboard updates

4. **Analytics Dashboard**
   - Payment success rates
   - Revenue tracking
   - User payment patterns

## Support

For issues with the payment gateway integration:

1. Check the application logs
2. Verify GMPay service status
3. Test with small amounts first
4. Contact system administrator

---

**Note**: This integration is designed to work with the GMPay payment gateway. Ensure all endpoints and payload formats match the current GMPay API specification. 