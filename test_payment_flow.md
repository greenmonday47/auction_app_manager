# Payment Flow Test Guide

This guide helps you test the complete payment gateway integration in the auction app.

## Prerequisites

1. **Backend Setup**
   - Ensure the backend is running with all payment endpoints
   - Verify the cron job is set up and running
   - Check that GMPay endpoints are accessible

2. **App Setup**
   - Build and run the Flutter app
   - Ensure you have a registered user account
   - Verify the user has a valid phone number

## Test Flow

### 1. Wallet Screen Access
- Navigate to the Wallet screen in the app
- Verify the balance card shows current tokens
- Check that the "Top Up Wallet" section is visible

### 2. Payment Initialization
- Tap "Top Up Wallet" to expand the form
- Enter a valid phone number (e.g., "0705721545") - exactly 10 digits
- Verify real-time validation feedback
- Enter an amount (minimum 500 UGX, e.g., "1000")
- Tap "Top Up Wallet" button

### 3. Payment Processing
- Verify the loading indicator appears
- Check that the payment status card shows:
  - Transaction ID
  - Amount
  - Token calculation
  - Progress indicator

### 4. GMPay Integration
- Check your phone for the payment prompt
- Complete the mobile money payment
- Verify the payment status updates in the app

### 5. Success Verification
- Confirm tokens are added to wallet
- Check transaction history shows the new transaction
- Verify the payment status card disappears

## Expected Behavior

### Success Flow
1. **Form Validation**
   - Phone number required (format: 07XXXXXXXX, exactly 10 digits)
   - Real-time validation with visual feedback
   - Amount minimum 500 UGX
   - User authentication required

2. **Payment Initialization**
   - Backend generates transaction ID
   - Creates pending transaction record
   - Returns GMPay payload

3. **GMPay Submission**
   - Phone number automatically formatted to 256 prefix
   - Exact payload sent to GMPay
   - User receives payment prompt

4. **Status Monitoring**
   - App polls payment status every 5 seconds
   - Updates UI based on status changes
   - Timeout after 5 minutes

5. **Completion**
   - Tokens added to wallet on success
   - Transaction history updated
   - Form cleared and hidden

### Error Handling
1. **Invalid Amount**
   - Error message for amounts below 500 UGX
   - Form validation prevents submission

2. **Network Errors**
   - Clear error messages
   - Retry functionality available

3. **Payment Failures**
   - Status updates to failed
   - User can retry payment

4. **Timeout**
   - Monitoring stops after 5 minutes
   - User notified to check wallet manually

## Manual Testing Commands

### Backend Testing
```bash
# Test payment initialization
curl -X POST "https://your-domain.com/auction/public/api/payment/initializeTopUp" \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "amount": 1000}'

# Test payment verification
curl "https://your-domain.com/auction/public/api/payment/verify/{transaction_id}"

# Test cron health
curl "https://your-domain.com/auction/public/api/cron/health"
```

### GMPay Testing
```bash
# Test GMPay deposit
curl -X POST "https://debit.gmpayapp.site/public/deposit/custom" \
  -H "Content-Type: application/json" \
  -d '{"msisdn": "256705721545", "amount": "1000", "transactionId": "test123"}'

# Test GMPay status
curl "https://debit.gmpayapp.site/public/transaction-status/{transaction_id}"
```

## Debugging

### Common Issues

1. **Payment Not Initializing**
   - Check backend logs for errors
   - Verify user authentication
   - Check API endpoint accessibility

2. **GMPay Not Responding**
   - Verify phone number format
   - Check GMPay service status
   - Test with small amounts first

3. **Status Not Updating**
   - Check cron job is running
   - Verify GMPay status endpoint
   - Review transaction logs

4. **Tokens Not Added**
   - Check transaction approval process
   - Verify user model update
   - Review database transactions

### Log Monitoring
```bash
# Backend logs
tail -f /path/to/application/logs

# Cron job logs
tail -f /var/log/cron

# Application logs
flutter logs
```

## Performance Testing

### Load Testing
- Test with multiple concurrent payments
- Verify cron job handles multiple transactions
- Check database performance under load

### Stress Testing
- Test with maximum amounts
- Verify timeout handling
- Check error recovery

## Security Testing

### Input Validation
- Test with invalid amounts
- Test with invalid phone numbers
- Verify SQL injection protection

### Authentication
- Test with invalid tokens
- Verify user authorization
- Check session handling

## Integration Testing

### End-to-End Flow
1. User registration
2. Wallet access
3. Payment initialization
4. GMPay integration
5. Status monitoring
6. Token addition
7. Transaction history

### Cross-Platform Testing
- Test on Android devices
- Test on iOS devices
- Verify responsive design

## Success Criteria

✅ **Payment Initialization**
- Form validation works
- Backend generates transaction ID
- GMPay payload is correct

✅ **GMPay Integration**
- Payment prompt received
- Transaction processed
- Status updates correctly

✅ **Status Monitoring**
- Real-time status updates
- Success/failure handling
- Timeout management

✅ **Token Management**
- Tokens added on success
- Balance updates correctly
- Transaction history updated

✅ **Error Handling**
- Clear error messages
- Graceful failure handling
- User-friendly feedback

✅ **UI/UX**
- Loading indicators
- Progress feedback
- Status notifications

## Post-Testing Checklist

- [ ] All payment flows work correctly
- [ ] Error handling is robust
- [ ] UI/UX is intuitive
- [ ] Performance is acceptable
- [ ] Security measures are in place
- [ ] Documentation is complete
- [ ] Monitoring is set up
- [ ] Backup procedures are ready

---

**Note**: Always test with small amounts first and verify the complete flow before processing larger transactions. 