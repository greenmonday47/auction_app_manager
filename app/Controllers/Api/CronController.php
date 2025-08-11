<?php

namespace App\Controllers\Api;

use App\Models\PaymentModel;
use App\Models\UserModel;

class CronController extends BaseApiController
{
    protected $format = 'json';

    /**
     * Check pending payment transactions status
     * This endpoint should be called by a cron job every minute
     */
    public function checkPendingPayments()
    {
        try {
            $paymentModel = new PaymentModel();
            $pendingTransactions = $paymentModel->getPendingTransactions();
            
            $results = [
                'checked' => 0,
                'updated' => 0,
                'errors' => 0,
                'details' => []
            ];

            foreach ($pendingTransactions as $transaction) {
                $results['checked']++;
                
                try {
                    // Check GMPay status
                    $gmpayStatus = $this->checkGMPayStatus($transaction['transaction_id']);
                    
                    // Normalize status to match database enum
                    $normalizedStatus = $this->normalizeStatus($gmpayStatus);
                    
                    if ($gmpayStatus && $normalizedStatus !== $transaction['status']) {
                        // Update payment status
                        $updated = $paymentModel->updatePaymentStatus($transaction['transaction_id'], $normalizedStatus);
                        
                        if ($updated) {
                            $results['updated']++;
                            
                            // If payment is successful, approve the transaction
                            if ($normalizedStatus === 'success') {
                                $approvalResult = $paymentModel->approveTransaction($transaction['id']);
                                if ($approvalResult['success']) {
                                    $results['details'][] = [
                                        'transaction_id' => $transaction['transaction_id'],
                                        'status' => $gmpayStatus,
                                        'action' => 'approved',
                                        'tokens_added' => $transaction['tokens']
                                    ];
                                } else {
                                    $results['details'][] = [
                                        'transaction_id' => $transaction['transaction_id'],
                                        'status' => $gmpayStatus,
                                        'action' => 'status_updated_but_approval_failed',
                                        'error' => $approvalResult['message']
                                    ];
                                }
                            } else {
                                $results['details'][] = [
                                    'transaction_id' => $transaction['transaction_id'],
                                    'status' => $gmpayStatus,
                                    'action' => 'status_updated'
                                ];
                            }
                        } else {
                            $results['errors']++;
                            $results['details'][] = [
                                'transaction_id' => $transaction['transaction_id'],
                                'error' => 'Failed to update status in database'
                            ];
                        }
                    } else {
                        $results['details'][] = [
                            'transaction_id' => $transaction['transaction_id'],
                            'status' => $gmpayStatus ?: 'no_change',
                            'action' => 'no_update_needed'
                        ];
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['details'][] = [
                        'transaction_id' => $transaction['transaction_id'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Log the results
            log_message('info', 'Cron job checked ' . $results['checked'] . ' pending transactions. Updated: ' . $results['updated'] . ', Errors: ' . $results['errors']);

            return $this->successResponse($results, 'Payment status check completed');

        } catch (\Exception $e) {
            log_message('error', 'Cron job failed: ' . $e->getMessage());
            return $this->errorResponse('Cron job failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Check GMPay status for a specific transaction
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
            log_message('error', 'GMPay status check failed for transaction ' . $transactionId . ': ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Health check endpoint for cron monitoring
     */
    public function healthCheck()
    {
        try {
            $paymentModel = new PaymentModel();
            $pendingCount = $paymentModel->where('status', 'pending')->countAllResults();
            
            return $this->successResponse([
                'status' => 'healthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'pending_transactions' => $pendingCount,
                'server_time' => time()
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Health check failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Normalize GMPay status to match database enum values
     */
    private function normalizeStatus($gmpayStatus)
    {
        if (!$gmpayStatus) {
            return null;
        }

        $status = strtolower($gmpayStatus);
        
        switch ($status) {
            case 'success':
                return 'success';
            case 'failed':
            case 'rejected':
                return 'failed';
            case 'pending':
                return 'pending';
            default:
                log_message('warning', 'Unknown GMPay status: ' . $gmpayStatus);
                return 'pending';
        }
    }
} 