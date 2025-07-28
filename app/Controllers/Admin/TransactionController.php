<?php

namespace App\Controllers\Admin;

use App\Models\TransactionModel;
use App\Models\UserModel;

class TransactionController extends BaseAdminController
{
    protected $transactionModel;
    protected $userModel;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->transactionModel = new TransactionModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $this->requireAuth();

        $search = $this->request->getGet('search');
        $type = $this->request->getGet('type');
        $status = $this->request->getGet('status');
        $sort = $this->request->getGet('sort') ?: 'created_at_desc';
        $page = $this->request->getGet('page') ?: 1;
        $perPage = 20;

        // Build query with user information
        $query = $this->db->table('transactions')
            ->select('transactions.*, users.name as user_name, users.phone as user_phone')
            ->join('users', 'users.id = transactions.user_id', 'left');
        
        if ($search) {
            $query = $query->like('users.name', $search)->orLike('users.phone', $search);
        }
        
        if ($type) {
            $query = $query->where('transactions.type', $type);
        }
        
        if ($status) {
            $query = $query->where('transactions.status', $status);
        }

        // Apply sorting
        switch ($sort) {
            case 'created_at_asc':
                $query = $query->orderBy('transactions.created_at', 'ASC');
                break;
            case 'amount_desc':
                $query = $query->orderBy('transactions.amount', 'DESC');
                break;
            case 'amount_asc':
                $query = $query->orderBy('transactions.amount', 'ASC');
                break;
            default:
                $query = $query->orderBy('transactions.created_at', 'DESC');
        }

        $totalTransactions = $query->countAllResults(false);
        $transactions = $query->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        // Get statistics
        $stats = [
            'total_transactions' => $this->transactionModel->countAll(),
            'approved_transactions' => $this->transactionModel->where('status', 'approved')->countAllResults(),
            'pending_transactions' => $this->transactionModel->where('status', 'pending')->countAllResults(),
            'rejected_transactions' => $this->transactionModel->where('status', 'rejected')->countAllResults(),
            'total_amount' => $this->transactionModel->selectSum('amount')->where('status', 'approved')->first()['amount'] ?? 0,
        ];

        $pager = service('pager');
        $pager->setPath('admin/transactions');
        $pager->makeLinks($page, $perPage, $totalTransactions);

        // Get users for dropdown
        $users = $this->userModel->select('id, name, phone')->findAll();

        $data = [
            'title' => 'Transaction Management',
            'transactions' => $transactions,
            'total_transactions' => $totalTransactions,
            'stats' => $stats,
            'users' => $users,
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'sort' => $sort,
            'pager' => $pager,
        ];

        return $this->render('transactions/index', $data);
    }

    public function add()
    {
        $this->requireAuth();

        $userId = $this->request->getPost('user_id');
        $type = $this->request->getPost('type');
        $amount = $this->request->getPost('amount');
        $reference = $this->request->getPost('reference');
        $description = $this->request->getPost('description');

        if (!$userId || !$type || !$amount || !$description) {
            $this->errorMessage('All required fields must be filled');
            return redirect()->back();
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->errorMessage('User not found');
            return redirect()->back();
        }

        // Calculate tokens (1 token = 100 UGX)
        $tokens = intval($amount / 100);
        
        // Create transaction
        $transactionData = [
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'tokens' => $tokens,
            'reference' => $reference,
            'description' => $description,
            'status' => 'approved', // Admin transactions are auto-approved
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->transactionModel->insert($transactionData)) {
            // Update user tokens based on transaction type
            $newTokens = $user['tokens'];
            if ($type === 'deposit' || $type === 'refund') {
                $newTokens += $transactionData['tokens'];
            } elseif ($type === 'withdrawal') {
                $newTokens -= $transactionData['tokens'];
            }

            $this->userModel->update($userId, ['tokens' => $newTokens]);

            $this->successMessage('Transaction added successfully');
        } else {
            $this->errorMessage('Failed to add transaction');
        }

        return redirect()->to('/admin/transactions');
    }

    public function edit()
    {
        $this->requireAuth();

        $transactionId = $this->request->getPost('transaction_id');
        $reference = $this->request->getPost('reference');
        $description = $this->request->getPost('description');
        $status = $this->request->getPost('status');

        if (!$transactionId || !$description || !$status) {
            $this->errorMessage('All fields are required');
            return redirect()->back();
        }

        $transaction = $this->transactionModel->find($transactionId);
        if (!$transaction) {
            $this->errorMessage('Transaction not found');
            return redirect()->back();
        }

        $updateData = [
            'reference' => $reference,
            'description' => $description,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->transactionModel->update($transactionId, $updateData)) {
            $this->successMessage('Transaction updated successfully');
        } else {
            $this->errorMessage('Failed to update transaction');
        }

        return redirect()->to('/admin/transactions');
    }

    public function approve($transactionId)
    {
        $this->requireAuth();

        $transaction = $this->transactionModel->find($transactionId);
        if (!$transaction) {
            $this->errorMessage('Transaction not found');
            return redirect()->to('/admin/transactions');
        }

        if ($transaction['status'] !== 'pending') {
            $this->errorMessage('Only pending transactions can be approved');
            return redirect()->to('/admin/transactions');
        }

        $user = $this->userModel->find($transaction['user_id']);
        if (!$user) {
            $this->errorMessage('User not found');
            return redirect()->to('/admin/transactions');
        }

        // Update transaction status
        $this->transactionModel->update($transactionId, [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Update user tokens
        $newTokens = $user['tokens'];
        if ($transaction['type'] === 'deposit' || $transaction['type'] === 'refund') {
            $newTokens += $transaction['amount'];
        } elseif ($transaction['type'] === 'withdrawal') {
            $newTokens -= $transaction['amount'];
        }

        $this->userModel->update($transaction['user_id'], ['tokens' => $newTokens]);

        $this->successMessage('Transaction approved successfully');
        return redirect()->to('/admin/transactions');
    }

    public function reject($transactionId)
    {
        $this->requireAuth();

        $transaction = $this->transactionModel->find($transactionId);
        if (!$transaction) {
            $this->errorMessage('Transaction not found');
            return redirect()->to('/admin/transactions');
        }

        if ($transaction['status'] !== 'pending') {
            $this->errorMessage('Only pending transactions can be rejected');
            return redirect()->to('/admin/transactions');
        }

        $this->transactionModel->update($transactionId, [
            'status' => 'failed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->successMessage('Transaction rejected successfully');
        return redirect()->to('/admin/transactions');
    }

    public function view($transactionId)
    {
        $this->requireAuth();

        $transaction = $this->db->table('transactions')
            ->select('transactions.*, users.name as user_name, users.phone as user_phone')
            ->join('users', 'users.id = transactions.user_id', 'left')
            ->where('transactions.id', $transactionId)
            ->get()
            ->getRowArray();

        if (!$transaction) {
            $this->errorMessage('Transaction not found');
            return redirect()->to('/admin/transactions');
        }

        $data = [
            'title' => 'Transaction Details',
            'transaction' => $transaction,
        ];

        return $this->render('transactions/view', $data);
    }

    public function get($transactionId)
    {
        $this->requireAuth();

        $transaction = $this->transactionModel->find($transactionId);
        if (!$transaction) {
            return $this->response->setJSON(['error' => 'Transaction not found'])->setStatusCode(404);
        }

        return $this->response->setJSON($transaction);
    }
} 