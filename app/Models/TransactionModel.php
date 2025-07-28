<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'amount', 'tokens', 'status', 'note'];

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
        'status' => 'required|in_list[pending,approved,rejected]',
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
            'in_list' => 'Status must be pending, approved, or rejected',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getWithUser($transactionId = null)
    {
        $builder = $this->db->table('transactions t');
        $builder->select('t.*, u.name as user_name, u.phone as user_phone');
        $builder->join('users u', 't.user_id = u.id');
        
        if ($transactionId) {
            $builder->where('t.id', $transactionId);
        }
        
        $builder->orderBy('t.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    public function getUserTransactions($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function createTopUp($userId, $amount, $note = '')
    {
        // Calculate tokens (1 token = 100 UGX)
        $tokens = intval($amount / 100);
        
        if ($tokens < 1) {
            return ['success' => false, 'message' => 'Minimum top-up amount is 100 UGX'];
        }

        $transactionData = [
            'user_id' => $userId,
            'amount' => $amount,
            'tokens' => $tokens,
            'status' => 'pending',
            'note' => $note
        ];

        return $this->insert($transactionData);
    }

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

    public function getPendingTransactions()
    {
        return $this->where('status', 'pending')
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    public function getStats()
    {
        $stats = [
            'total_transactions' => $this->countAll(),
            'pending_transactions' => $this->where('status', 'pending')->countAllResults(),
            'approved_transactions' => $this->where('status', 'approved')->countAllResults(),
            'rejected_transactions' => $this->where('status', 'rejected')->countAllResults(),
            'total_amount' => $this->selectSum('amount')->where('status', 'approved')->get()->getRow()->amount ?? 0,
            'total_tokens' => $this->selectSum('tokens')->where('status', 'approved')->get()->getRow()->tokens ?? 0,
        ];

        return $stats;
    }
} 