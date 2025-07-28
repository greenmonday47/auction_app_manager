<?php

namespace App\Controllers\Api;

use App\Models\PaymentModel;
use App\Models\UserModel;

class PaymentController extends BaseApiController
{
    protected $format = 'json';

    /**
     * Initialize wallet top-up payment
     */
    public function initializeTopUp()
    {
        $rules = [
            'user_id' => 'required|integer|is_natural_no_zero',
            'amount' => 'required|numeric|greater_than[99]',
        ];

        $validationResult = $this->validateRequest($rules);
        if ($validationResult) {
            return $validationResult;
        }

        $userId = $this->request->getPost('user_id');
        $amount = $this->request->getPost('amount');

        try {
            // Get user details for phone number
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            // Format phone number for GMPay (add 256 prefix if not present)
            $phoneNumber = $user['phone'];
            if (!str_starts_with($phoneNumber, '256')) {
                // Remove leading 0 if present and add 256
                $phoneNumber = '256' . ltrim($phoneNumber, '0');
            }

            // Generate 13-digit transaction ID for GMPay
            $transactionId = $this->generateGMPayTransactionId();

            // Create top-up transaction
            $paymentId = $this->paymentModel->createTopUp(
                $userId, 
                $amount, 
                $transactionId,
                'Wallet top-up via GMPay'
            );

            if ($paymentId) {
                // Prepare GMPay payload
                $gmpayPayload = [
                    'msisdn' => $phoneNumber,
                    'amount' => $amount,
                    'transactionId' => $transactionId
                ];

                return $this->successResponse([
                    'payment_id' => $paymentId,
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'tokens' => intval($amount / 100),
                    'gmpay_payload' => $gmpayPayload,
                    'gmpay_url' => 'https://debit.gmpayapp.site/public/deposit/custom'
                ], 'Top-up payment initialized successfully', 201);
            } else {
                return $this->errorResponse('Failed to initialize top-up payment', 500);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Top-up payment initialization failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate 13-digit transaction ID for GMPay
     */
    private function generateGMPayTransactionId()
    {
        do {
            $transactionId = date('YmdHis') . rand(10, 99);
        } while ($this->paymentModel->getByTransactionId($transactionId));

        return $transactionId;
    }

    /**
     * Verify payment status from GMPay
     */
    public function verify($transactionId = null)
    {
        if (!$transactionId) {
            return $this->errorResponse('Transaction ID required', 400);
        }

        try {
            $payment = $this->paymentModel->getByTransactionId($transactionId);
            
            if (!$payment) {
                return $this->errorResponse('Payment not found', 404);
            }

            // Check GMPay status
            $gmpayStatus = $this->checkGMPayStatus($transactionId);
            
            // Update payment status if it has changed
            if ($gmpayStatus && $gmpayStatus !== $payment['status']) {
                $this->paymentModel->updatePaymentStatus($transactionId, $gmpayStatus);
                $payment['status'] = $gmpayStatus;
                
                // If payment is successful, approve the transaction
                if ($gmpayStatus === 'SUCCESS') {
                    $this->paymentModel->approveTransaction($payment['id']);
                }
            }

            return $this->successResponse([
                'transaction_id' => $payment['transaction_id'],
                'status' => $payment['status'],
                'amount' => $payment['amount'],
                'tokens' => $payment['tokens'],
                'payment_type' => $payment['payment_type'],
                'created_at' => $payment['created_at'],
                'gmpay_status' => $gmpayStatus
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Payment verification failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Check payment status from GMPay API
     */
    private function checkGMPayStatus($transactionId)
    {
        $url = "https://debit.gmpayapp.site/public/transaction-status/{$transactionId}";
        
        try {
            $client = \Config\Services::curlrequest();
            $response = $client->get($url, [
                'timeout' => 10,
                'verify' => false, // Disable SSL verification
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                
                if (isset($data['status']) && $data['status'] === 'success') {
                    $transaction = $data['transaction'];
                    return strtoupper($transaction['status']); // SUCCESS, FAILED, PENDING
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'GMPay status check failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Update payment status (webhook from payment gateway)
     */
    public function updateStatus()
    {
        $rules = [
            'transaction_id' => 'required|min_length[10]|max_length[100]',
            'status' => 'required|in_list[pending,success,failed]',
        ];

        $validationResult = $this->validateRequest($rules);
        if ($validationResult) {
            return $validationResult;
        }

        $transactionId = $this->request->getPost('transaction_id');
        $status = $this->request->getPost('status');

        try {
            $payment = $this->paymentModel->getByTransactionId($transactionId);
            
            if (!$payment) {
                return $this->errorResponse('Payment not found', 404);
            }

            $updated = $this->paymentModel->updatePaymentStatus($transactionId, $status);

            if ($updated) {
                // If payment is successful, approve the transaction
                if ($status === 'success') {
                    $this->paymentModel->approveTransaction($payment['id']);
                }

                return $this->successResponse([
                    'transaction_id' => $transactionId,
                    'status' => $status
                ], 'Payment status updated');
            } else {
                return $this->errorResponse('Failed to update payment status', 500);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Payment status update failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GMPay webhook endpoint
     */
    public function gmpayWebhook()
    {
        // Get the raw JSON data
        $jsonData = $this->request->getJSON(true);
        
        if (!$jsonData) {
            return $this->errorResponse('Invalid JSON data', 400);
        }

        // Log the webhook data for debugging
        log_message('info', 'GMPay webhook received: ' . json_encode($jsonData));

        try {
            // Extract transaction data
            if (isset($jsonData['status']) && $jsonData['status'] === 'success' && isset($jsonData['transaction'])) {
                $transaction = $jsonData['transaction'];
                $transactionId = $transaction['transaction_id'];
                $status = strtoupper($transaction['status']); // SUCCESS, FAILED, PENDING
                
                // Update payment status
                $updated = $this->paymentModel->updatePaymentStatus($transactionId, $status);
                
                if ($updated) {
                    // Get the payment record
                    $payment = $this->paymentModel->getByTransactionId($transactionId);
                    
                    // If payment is successful, approve the transaction
                    if ($status === 'SUCCESS' && $payment) {
                        $this->paymentModel->approveTransaction($payment['id']);
                    }

                    return $this->successResponse([
                        'transaction_id' => $transactionId,
                        'status' => $status
                    ], 'Payment status updated via webhook');
                } else {
                    return $this->errorResponse('Failed to update payment status', 500);
                }
            } else {
                return $this->errorResponse('Invalid webhook data structure', 400);
            }
        } catch (\Exception $e) {
            log_message('error', 'GMPay webhook processing failed: ' . $e->getMessage());
            return $this->errorResponse('Webhook processing failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user's payment history
     */
    public function getUserPayments()
    {
        // Require authentication
        $auth = $this->requireAuth();
        if ($auth) {
            return $auth;
        }

        try {
            $userId = $this->currentUser['id'];
            $payments = $this->paymentModel->getUserPayments($userId);
            
            return $this->successResponse($payments);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch payments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get payment history for current user
     */
    public function getPaymentHistory()
    {
        try {
            // Get current user from session or token
            $userId = $this->getCurrentUserId();
            
            if (!$userId) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $payments = $this->paymentModel->getUserPayments($userId);

            if ($payments) {
                return $this->successResponse($payments, 'Payment history retrieved successfully');
            } else {
                return $this->successResponse([], 'No payment history found');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error getting payment history: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve payment history', 500);
        }
    }

    /**
     * Get wallet balance and transaction history
     */
    public function getWalletStats()
    {
        // Require authentication
        $auth = $this->requireAuth();
        if ($auth) {
            return $auth;
        }

        try {
            $userId = $this->currentUser['id'];
            
            // Get user details
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            
            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            // Get recent transactions
            $transactions = $this->paymentModel->getUserTransactions($userId);
            
            // Get payment statistics
            $paymentStats = $this->paymentModel->getPaymentStats();

            return $this->successResponse([
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'phone' => $user['phone'],
                    'tokens' => $user['tokens'] ?? 0,
                    'balance' => ($user['tokens'] ?? 0) * 100 // Convert tokens to UGX
                ],
                'recent_transactions' => array_slice($transactions, 0, 10),
                'statistics' => $paymentStats
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get wallet stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get current user ID (implement based on your authentication system)
     */
    private function getCurrentUserId()
    {
        // Implement based on your authentication system
        // This could be from session, JWT token, etc.
        return $this->request->getPost('user_id') ?? $this->request->getGet('user_id');
    }
} 