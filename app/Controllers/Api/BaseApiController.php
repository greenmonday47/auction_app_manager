<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class BaseApiController extends ResourceController
{
    protected $userModel;
    protected $auctionModel;
    protected $bidModel;
    protected $transactionModel;
    protected $ruleModel;
    protected $paymentModel;
    protected $currentUser = null;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
        $this->auctionModel = new \App\Models\AuctionModel();
        $this->bidModel = new \App\Models\BidModel();
        $this->transactionModel = new \App\Models\TransactionModel();
        $this->ruleModel = new \App\Models\RuleModel();
        $this->paymentModel = new \App\Models\PaymentModel();
    }

    protected function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data
        ])->setStatusCode($code);
    }

    protected function errorResponse($message = 'Error', $code = 400, $data = null)
    {
        return $this->response->setJSON([
            'success' => false,
            'message' => $message,
            'data' => $data
        ])->setStatusCode($code);
    }

    protected function formatCurrency($amount)
    {
        return number_format($amount, 0, '.', ',') . ' UGX';
    }

    protected function formatUserData($user)
    {
        if (!$user) {
            return null;
        }

        // Convert string values to appropriate types
        return [
            'id' => (int) $user['id'],
            'phone' => $user['phone'],
            'name' => $user['name'],
            'tokens' => (int) $user['tokens'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at'] ?? null,
        ];
    }

    protected function formatAuctionData($auction)
    {
        if (!$auction) {
            return null;
        }

        // Convert string values to appropriate types
        $formatted = [
            'id' => (int) $auction['id'],
            'item_name' => $auction['item_name'],
            'description' => $auction['description'],
            'image' => $auction['image'],
            'start_time' => $auction['start_time'],
            'end_time' => $auction['end_time'],
            'starting_price' => (float) $auction['starting_price'],
            'is_completed' => (bool) $auction['is_completed'],
            'created_at' => $auction['created_at'],
            'updated_at' => $auction['updated_at'] ?? null,
        ];

        // Handle optional fields
        if (isset($auction['winner_id'])) {
            $formatted['winner_id'] = $auction['winner_id'] ? (int) $auction['winner_id'] : null;
        }

        // Add formatted fields if they exist
        if (isset($auction['starting_price_formatted'])) {
            $formatted['starting_price_formatted'] = $auction['starting_price_formatted'];
        }
        if (isset($auction['start_time_formatted'])) {
            $formatted['start_time_formatted'] = $auction['start_time_formatted'];
        }
        if (isset($auction['end_time_formatted'])) {
            $formatted['end_time_formatted'] = $auction['end_time_formatted'];
        }
        if (isset($auction['current_highest_bid'])) {
            $formatted['current_highest_bid'] = (float) $auction['current_highest_bid'];
        }
        if (isset($auction['current_highest_bid_formatted'])) {
            $formatted['current_highest_bid_formatted'] = $auction['current_highest_bid_formatted'];
        }
        if (isset($auction['total_bids'])) {
            $formatted['total_bids'] = (int) $auction['total_bids'];
        }
        if (isset($auction['final_bid'])) {
            $formatted['final_bid'] = (float) $auction['final_bid'];
        }
        if (isset($auction['final_bid_formatted'])) {
            $formatted['final_bid_formatted'] = $auction['final_bid_formatted'];
        }
        if (isset($auction['is_live'])) {
            $formatted['is_live'] = (bool) $auction['is_live'];
        }
        if (isset($auction['winner_name'])) {
            $formatted['winner_name'] = $auction['winner_name'];
        }
        if (isset($auction['winner_phone'])) {
            $formatted['winner_phone'] = $auction['winner_phone'];
        }
        if (isset($auction['bids'])) {
            $formatted['bids'] = array_map([$this, 'formatBidData'], $auction['bids']);
        }

        return $formatted;
    }

    protected function formatBidData($bid)
    {
        if (!$bid) {
            return null;
        }

        $formatted = [
            'id' => (int) $bid['id'],
            'auction_id' => (int) $bid['auction_id'],
            'user_id' => (int) $bid['user_id'],
            'amount' => (float) $bid['amount'],
            'tokens_used' => (int) $bid['tokens_used'],
            'created_at' => $bid['created_at'],
        ];

        // Add optional fields
        if (isset($bid['user_name'])) {
            $formatted['user_name'] = $bid['user_name'];
        }
        if (isset($bid['amount_formatted'])) {
            $formatted['amount_formatted'] = $bid['amount_formatted'];
        }
        if (isset($bid['created_at_formatted'])) {
            $formatted['created_at_formatted'] = $bid['created_at_formatted'];
        }

        return $formatted;
    }

    protected function formatTransactionData($transaction)
    {
        if (!$transaction) {
            return null;
        }

        $formatted = [
            'id' => (int) $transaction['id'],
            'user_id' => (int) $transaction['user_id'],
            'amount' => (float) $transaction['amount'],
            'tokens' => (int) $transaction['tokens'],
            'status' => $transaction['status'],
            'created_at' => $transaction['created_at'],
        ];

        // Add optional fields
        if (isset($transaction['updated_at'])) {
            $formatted['updated_at'] = $transaction['updated_at'];
        }
        if (isset($transaction['note'])) {
            $formatted['note'] = $transaction['note'];
        }
        if (isset($transaction['amount_formatted'])) {
            $formatted['amount_formatted'] = $transaction['amount_formatted'];
        }
        if (isset($transaction['created_at_formatted'])) {
            $formatted['created_at_formatted'] = $transaction['created_at_formatted'];
        }
        if (isset($transaction['updated_at_formatted'])) {
            $formatted['updated_at_formatted'] = $transaction['updated_at_formatted'];
        }

        return $formatted;
    }

    protected function authenticateUser()
    {
        // Initialize userModel if not already done
        if (!$this->userModel) {
            $this->userModel = new UserModel();
        }
        
        // First try to get from Authorization header (Bearer token)
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
            $credentials = base64_decode($token);
            $parts = explode(':', $credentials, 2);
            
            if (count($parts) === 2) {
                $phone = $parts[0];
                $pin = $parts[1];
                
                $user = $this->userModel->verifyPin($phone, $pin);
                if ($user) {
                    $this->currentUser = $user;
                    return true;
                }
            }
        }
        
        // Fallback to POST/GET parameters for backward compatibility
        $phone = $this->request->getPost('phone') ?? $this->request->getGet('phone');
        $pin = $this->request->getPost('pin') ?? $this->request->getGet('pin');

        if (!$phone || !$pin) {
            return false;
        }

        $user = $this->userModel->verifyPin($phone, $pin);
        if ($user) {
            $this->currentUser = $user;
            return true;
        }

        return false;
    }

    protected function requireAuth()
    {
        if (!$this->authenticateUser()) {
            return $this->errorResponse('Authentication required', 401);
        }
        return null;
    }

    protected function validateRequest($rules)
    {
        // Try to get JSON data first
        $jsonData = null;
        try {
            $jsonData = $this->request->getJSON(true);
        } catch (\Exception $e) {
            // JSON parsing failed, continue with form data
        }
        
        if ($jsonData) {
            // Manual validation for JSON data
            $validation = \Config\Services::validation();
            $validation->setRules($rules);
            
            if (!$validation->run($jsonData)) {
                $errors = $validation->getErrors();
                return $this->errorResponse('Validation failed', 400, $errors);
            }
        } else {
            // Fallback to manual validation for POST data
            $validation = \Config\Services::validation();
            $validation->setRules($rules);
            
            // Get POST data
            $postData = [];
            foreach ($rules as $field => $rule) {
                $postData[$field] = $this->request->getPost($field);
            }
            
            // Debug: Log what we received
            log_message('info', 'POST data received: ' . json_encode($this->request->getPost()));
            log_message('info', 'POST data for validation: ' . json_encode($postData));
            
            if (!$validation->run($postData)) {
                $errors = $validation->getErrors();
                return $this->errorResponse('Validation failed', 400, $errors);
            }
        }
        
        return null;
    }
} 