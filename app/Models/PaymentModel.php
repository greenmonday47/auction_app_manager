<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'amount', 'tokens', 'status', 'note', 'transaction_id', 'payment_type'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'amount' => 'required|numeric|greater_than[0]',
        'tokens' => 'required|integer|greater_than[0]',
        'status' => 'required|in_list[pending,approved,rejected,success,failed]',
        'transaction_id' => 'permit_empty|min_length[10]|max_length[100]',
        'payment_type' => 'permit_empty|in_list[topup,withdrawal]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer',
            'is_not_unique' => 'User does not exist',
        ],
        'amount' => [
            'required' => 'Amount is required',
            'numeric' => 'Amount must be a valid number',
            'greater_than' => 'Amount must be greater than 0',
        ],
        'tokens' => [
            'required' => 'Tokens is required',
            'integer' => 'Tokens must be an integer',
            'greater_than' => 'Tokens must be greater than 0',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be pending, approved, rejected, success, or failed',
        ],
        'transaction_id' => [
            'min_length' => 'Transaction ID must be at least 10 characters',
            'max_length' => 'Transaction ID cannot exceed 100 characters',
        ],
        'payment_type' => [
            'in_list' => 'Payment type must be topup or withdrawal',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Create a wallet top-up transaction
     */
    public function createTopUp($userId, $amount, $transactionId = null, $note = 'Wallet top-up')
    {
        // Calculate tokens (1 token = 100 UGX)
        $tokens = intval($amount / 100);
        
        if ($tokens < 5) {
            return false; // Minimum top-up amount is 500 UGX (5 tokens)
        }

        $transactionData = [
            'user_id' => $userId,
            'amount' => $amount,
            'tokens' => $tokens,
            'status' => 'pending',
            'payment_type' => 'topup',
            'note' => $note
        ];

        if ($transactionId) {
            $transactionData['transaction_id'] = $transactionId;
        }

        // Debug: Log the transaction data
        log_message('debug', 'PaymentModel::createTopUp - Transaction data: ' . json_encode($transactionData));

        $result = $this->insert($transactionData);
        
        // Debug: Log the result
        log_message('debug', 'PaymentModel::createTopUp - Insert result: ' . ($result ? 'success' : 'failed'));
        if (!$result) {
            log_message('error', 'PaymentModel::createTopUp - Validation errors: ' . json_encode($this->errors()));
            log_message('error', 'PaymentModel::createTopUp - Database errors: ' . json_encode($this->db->error()));
        }
        
        return $result;
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($transactionId, $status)
    {
        return $this->where('transaction_id', $transactionId)
                   ->set(['status' => $status])
                   ->update();
    }

    /**
     * Get payment by transaction ID
     */
    public function getByTransactionId($transactionId)
    {
        return $this->where('transaction_id', $transactionId)->first();
    }

    /**
     * Get all payments for a user
     */
    public function getUserPayments($userId)
    {
        return $this->where('user_id', $userId)
                   ->whereIn('payment_type', ['topup', 'withdrawal'])
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Generate unique transaction ID
     */
    public function generateTransactionId()
    {
        do {
            $transactionId = 'TXN' . date('YmdHis') . rand(1000, 9999);
        } while ($this->getByTransactionId($transactionId));

        return $transactionId;
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats()
    {
        $stats = [
            'total_payments' => $this->whereIn('payment_type', ['topup', 'withdrawal'])->countAllResults(),
            'successful_payments' => $this->whereIn('payment_type', ['topup', 'withdrawal'])
                                        ->whereIn('status', ['approved', 'success'])->countAllResults(),
            'pending_payments' => $this->whereIn('payment_type', ['topup', 'withdrawal'])
                                     ->where('status', 'pending')->countAllResults(),
            'failed_payments' => $this->whereIn('payment_type', ['topup', 'withdrawal'])
                                    ->whereIn('status', ['rejected', 'failed'])->countAllResults(),
            'total_revenue' => $this->whereIn('payment_type', ['topup', 'withdrawal'])
                                  ->whereIn('status', ['approved', 'success'])
                                  ->selectSum('amount')->first()['amount'] ?? 0,
        ];

        return $stats;
    }

    /**
     * Get user's transaction history (all types)
     */
    public function getUserTransactions($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Approve transaction and add tokens to user
     */
    public function approveTransaction($transactionId)
    {
        $transaction = $this->find($transactionId);
        if (!$transaction) {
            log_message('error', 'PaymentModel::approveTransaction - Transaction not found: ' . $transactionId);
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        log_message('info', 'PaymentModel::approveTransaction - Transaction data: ' . json_encode($transaction));

        // Check if transaction is already approved
        if ($transaction['status'] === 'approved') {
            log_message('info', 'PaymentModel::approveTransaction - Transaction already approved: ' . $transaction['status']);
            
            // Check if tokens were already credited using multiple methods
            $tokensAlreadyCredited = false;
            
            // Method 1: Check tokens_credited field
            if (isset($transaction['tokens_credited']) && $transaction['tokens_credited'] == 1) {
                $tokensAlreadyCredited = true;
                log_message('info', 'PaymentModel::approveTransaction - Tokens already credited (tokens_credited field)');
            }
            
            // Method 2: Check if note contains TOKENS_CREDITED
            if (isset($transaction['note']) && strpos($transaction['note'], 'TOKENS_CREDITED') !== false) {
                $tokensAlreadyCredited = true;
                log_message('info', 'PaymentModel::approveTransaction - Tokens already credited (note field)');
            }
            
            // Method 3: Check if user has enough tokens (basic sanity check)
            $userModel = new UserModel();
            $user = $userModel->find($transaction['user_id']);
            $expectedTokens = $user['tokens'] - $transaction['tokens'];
            if ($user['tokens'] >= $expectedTokens + $transaction['tokens']) {
                $tokensAlreadyCredited = true;
                log_message('info', 'PaymentModel::approveTransaction - Tokens already credited (user has sufficient tokens)');
            }
            
            if ($tokensAlreadyCredited) {
                return ['success' => true, 'message' => 'Transaction already approved and tokens credited'];
            }
            
            // Credit tokens and mark as credited
            $newTokens = $user['tokens'] + $transaction['tokens'];
            
            log_message('info', 'PaymentModel::approveTransaction - Crediting tokens. Old: ' . $user['tokens'] . ', New: ' . $newTokens);
            $updateResult = $userModel->updateTokens($transaction['user_id'], $newTokens);
            
            // Mark transaction as tokens credited using multiple methods
            try {
                $this->update($transactionId, [
                    'tokens_credited' => 1,
                    'note' => $transaction['note'] . ' TOKENS_CREDITED'
                ]);
                log_message('info', 'PaymentModel::approveTransaction - Marked as credited successfully');
            } catch (\Exception $e) {
                log_message('error', 'PaymentModel::approveTransaction - Error marking as credited: ' . $e->getMessage());
                // Continue anyway since tokens were already credited
            }
            
            log_message('info', 'PaymentModel::approveTransaction - Token update result: ' . ($updateResult ? 'success' : 'failed'));
            return ['success' => true, 'message' => 'Tokens credited successfully'];
        }

        // Only allow approval of pending or success transactions
        if ($transaction['status'] !== 'pending' && $transaction['status'] !== 'success') {
            log_message('error', 'PaymentModel::approveTransaction - Transaction cannot be approved (status: ' . $transaction['status'] . ')');
            return ['success' => false, 'message' => 'Transaction cannot be approved (status: ' . $transaction['status'] . ')'];
        }

        $this->db->transStart();

        try {
            // Update transaction status and mark as tokens credited
            try {
                $this->update($transactionId, [
                    'status' => 'approved',
                    'tokens_credited' => 1
                ]);
                log_message('info', 'PaymentModel::approveTransaction - Updated transaction status to approved and marked as credited');
            } catch (\Exception $e) {
                log_message('error', 'PaymentModel::approveTransaction - Error updating transaction: ' . $e->getMessage());
                // Try updating just the status
                $this->update($transactionId, ['status' => 'approved']);
            }

            // Add tokens to user
            $userModel = new UserModel();
            $user = $userModel->find($transaction['user_id']);
            log_message('info', 'PaymentModel::approveTransaction - User before update: ' . json_encode($user));
            
            $newTokens = $user['tokens'] + $transaction['tokens'];
            $updateResult = $userModel->updateTokens($transaction['user_id'], $newTokens);
            log_message('info', 'PaymentModel::approveTransaction - Update result: ' . ($updateResult ? 'success' : 'failed'));

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'PaymentModel::approveTransaction - Transaction failed');
                return ['success' => false, 'message' => 'Failed to approve transaction'];
            }

            log_message('info', 'PaymentModel::approveTransaction - Transaction approved successfully');
            return ['success' => true, 'message' => 'Transaction approved successfully'];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'PaymentModel::approveTransaction - Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error approving transaction: ' . $e->getMessage()];
        }
    }

    /**
     * Reject transaction
     */
    public function rejectTransaction($transactionId, $note = '')
    {
        $transaction = $this->find($transactionId);
        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        if ($transaction['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Transaction is not pending'];
        }

        $updateData = ['status' => 'rejected'];
        if ($note) {
            $updateData['note'] = $note;
        }

        return $this->update($transactionId, $updateData);
    }

    /**
     * Get pending transactions
     */
    public function getPendingTransactions()
    {
        return $this->where('status', 'pending')
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }
} 