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
        'amount' => 'required|decimal|greater_than[0]',
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
            'decimal' => 'Amount must be a valid decimal',
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
        
        if ($tokens < 1) {
            return false; // Minimum top-up amount is 100 UGX
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

        return $this->insert($transactionData);
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
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        if ($transaction['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Transaction is not pending'];
        }

        $this->db->transStart();

        try {
            // Update transaction status
            $this->update($transactionId, ['status' => 'approved']);

            // Add tokens to user
            $userModel = new UserModel();
            $user = $userModel->find($transaction['user_id']);
            $userModel->updateTokens($transaction['user_id'], $user['tokens'] + $transaction['tokens']);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return ['success' => false, 'message' => 'Failed to approve transaction'];
            }

            return ['success' => true, 'message' => 'Transaction approved successfully'];
        } catch (\Exception $e) {
            $this->db->transRollback();
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