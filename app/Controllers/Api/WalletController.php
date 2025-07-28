<?php

namespace App\Controllers\Api;

use App\Models\TransactionModel;

class WalletController extends BaseApiController
{
    public function getWallet()
    {
        // Require authentication
        $auth = $this->requireAuth();
        if ($auth) {
            return $auth;
        }

        $userId = $this->currentUser['id'];
        $user = $this->userModel->find($userId);

        $walletData = [
            'tokens' => (int) $user['tokens'],
            'tokens_formatted' => number_format($user['tokens'], 0, '.', ','),
            'equivalent_amount' => (int) $user['tokens'] * 100,
            'equivalent_amount_formatted' => $this->formatCurrency($user['tokens'] * 100),
        ];

        return $this->successResponse($walletData, 'Wallet information retrieved successfully');
    }

    public function topUp()
    {
        // Require authentication
        $auth = $this->requireAuth();
        if ($auth) {
            return $auth;
        }

        $rules = [
            'amount' => 'required|decimal|greater_than_equal_to[100]',
            'note' => 'permit_empty|max_length[500]',
        ];

        $validation = $this->validateRequest($rules);
        if ($validation) {
            return $validation;
        }

        $amount = (float)$this->request->getPost('amount');
        $note = $this->request->getPost('note') ?? '';
        $userId = $this->currentUser['id'];

        $transactionId = $this->transactionModel->createTopUp($userId, $amount, $note);
        
        if ($transactionId) {
            $transaction = $this->transactionModel->find($transactionId);
            return $this->successResponse($this->formatTransactionData($transaction), 'Top-up request created successfully. Please wait for admin approval.', 201);
        } else {
            return $this->errorResponse('Failed to create top-up request');
        }
    }

    public function getTransactions()
    {
        // Require authentication
        $auth = $this->requireAuth();
        if ($auth) {
            return $auth;
        }

        $userId = $this->currentUser['id'];
        $transactions = $this->transactionModel->getUserTransactions($userId);

        // Format the data
        foreach ($transactions as &$transaction) {
            $transaction['amount_formatted'] = $this->formatCurrency($transaction['amount']);
            $transaction['created_at_formatted'] = date('M j, Y g:i A', strtotime($transaction['created_at']));
            $transaction['updated_at_formatted'] = $transaction['updated_at'] ? date('M j, Y g:i A', strtotime($transaction['updated_at'])) : null;
        }

        // Format each transaction with proper data types
        $formattedTransactions = array_map([$this, 'formatTransactionData'], $transactions);

        return $this->successResponse($formattedTransactions, 'Transaction history retrieved successfully');
    }
} 