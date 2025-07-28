<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use App\Models\TransactionModel;

class UserController extends BaseAdminController
{
    protected $userModel;
    protected $transactionModel;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->transactionModel = new TransactionModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $this->requireAuth();

        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');
        $sort = $this->request->getGet('sort') ?: 'created_at_desc';
        $page = $this->request->getGet('page') ?: 1;
        $perPage = 20;

        // Build query
        $query = $this->userModel;
        
        if ($search) {
            $query = $query->like('name', $search)->orLike('phone', $search);
        }
        
        if ($status === 'active') {
            $query = $query->where('tokens >', 0);
        } elseif ($status === 'inactive') {
            $query = $query->where('tokens <=', 0);
        }

        // Apply sorting
        switch ($sort) {
            case 'created_at_asc':
                $query = $query->orderBy('created_at', 'ASC');
                break;
            case 'name_asc':
                $query = $query->orderBy('name', 'ASC');
                break;
            case 'name_desc':
                $query = $query->orderBy('name', 'DESC');
                break;
            case 'tokens_desc':
                $query = $query->orderBy('tokens', 'DESC');
                break;
            case 'tokens_asc':
                $query = $query->orderBy('tokens', 'ASC');
                break;
            default:
                $query = $query->orderBy('created_at', 'DESC');
        }

        $totalUsers = $query->countAllResults(false);
        $users = $query->limit($perPage, ($page - 1) * $perPage)->findAll();

        $pager = service('pager');
        $pager->setPath('admin/users');
        $pager->makeLinks($page, $perPage, $totalUsers);

        $data = [
            'title' => 'User Management',
            'users' => $users,
            'total_users' => $totalUsers,
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'pager' => $pager,
        ];

        return $this->render('users/index', $data);
    }

    public function add()
    {
        $this->requireAuth();

        $name = $this->request->getPost('name');
        $phone = $this->request->getPost('phone');
        $pin = $this->request->getPost('pin');
        $tokens = $this->request->getPost('tokens') ?: 0;

        if (!$name || !$phone || !$pin) {
            $this->errorMessage('All fields are required');
            return redirect()->back();
        }

        // Check if phone already exists
        if ($this->userModel->where('phone', $phone)->first()) {
            $this->errorMessage('Phone number already registered');
            return redirect()->back();
        }

        $userData = [
            'name' => $name,
            'phone' => $phone,
            'pin' => password_hash($pin, PASSWORD_DEFAULT),
            'tokens' => $tokens,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->userModel->insert($userData)) {
            $this->successMessage('User created successfully');
        } else {
            $this->errorMessage('Failed to create user');
        }

        return redirect()->to('/admin/users');
    }

    public function edit()
    {
        $this->requireAuth();

        $userId = $this->request->getPost('user_id');
        $name = $this->request->getPost('name');
        $phone = $this->request->getPost('phone');

        if (!$userId || !$name || !$phone) {
            $this->errorMessage('All fields are required');
            return redirect()->back();
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->errorMessage('User not found');
            return redirect()->back();
        }

        // Check if phone already exists for another user
        $existingUser = $this->userModel->where('phone', $phone)->where('id !=', $userId)->first();
        if ($existingUser) {
            $this->errorMessage('Phone number already registered to another user');
            return redirect()->back();
        }

        $updateData = [
            'name' => $name,
            'phone' => $phone,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->userModel->update($userId, $updateData)) {
            $this->successMessage('User updated successfully');
        } else {
            $this->errorMessage('Failed to update user');
        }

        return redirect()->to('/admin/users');
    }

    public function addTokens()
    {
        $this->requireAuth();

        $userId = $this->request->getPost('user_id');
        $amount = $this->request->getPost('amount');
        $reason = $this->request->getPost('reason');

        if (!$userId || !$amount || !$reason) {
            $this->errorMessage('All fields are required');
            return redirect()->back();
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->errorMessage('User not found');
            return redirect()->back();
        }

        // Update user tokens
        $newTokens = $user['tokens'] + $amount;
        $this->userModel->update($userId, ['tokens' => $newTokens]);

        // Create transaction record
        $transactionData = [
            'user_id' => $userId,
            'type' => 'deposit',
            'amount' => $amount,
            'description' => $reason,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->transactionModel->insert($transactionData);

        $this->successMessage("Added {$amount} tokens to user successfully");
        return redirect()->to('/admin/users');
    }

    public function delete($userId)
    {
        $this->requireAuth();

        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->errorMessage('User not found');
            return redirect()->to('/admin/users');
        }

        // Check if user has any active bids or transactions
        $hasBids = $this->db->table('bids')->where('user_id', $userId)->countAllResults() > 0;
        $hasTransactions = $this->transactionModel->where('user_id', $userId)->countAllResults() > 0;

        if ($hasBids || $hasTransactions) {
            $this->errorMessage('Cannot delete user with existing bids or transactions');
            return redirect()->to('/admin/users');
        }

        if ($this->userModel->delete($userId)) {
            $this->successMessage('User deleted successfully');
        } else {
            $this->errorMessage('Failed to delete user');
        }

        return redirect()->to('/admin/users');
    }

    public function view($userId)
    {
        $this->requireAuth();

        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->errorMessage('User not found');
            return redirect()->to('/admin/users');
        }

        // Get user's bids
        $bids = $this->db->table('bids')
            ->select('bids.*, auctions.item_name as auction_title, auctions.starting_price')
            ->join('auctions', 'auctions.id = bids.auction_id')
            ->where('bids.user_id', $userId)
            ->orderBy('bids.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Get user's transactions
        $transactions = $this->transactionModel->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'User Details',
            'user' => $user,
            'bids' => $bids,
            'transactions' => $transactions,
        ];

        return $this->render('users/view', $data);
    }

    public function get($userId)
    {
        $this->requireAuth();

        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found'])->setStatusCode(404);
        }

        return $this->response->setJSON($user);
    }
} 