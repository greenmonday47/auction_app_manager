<?php

namespace App\Models;

use CodeIgniter\Model;

class BidModel extends Model
{
    protected $table = 'bids';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['auction_id', 'user_id', 'amount', 'tokens_used'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;

    // Validation
    protected $validationRules = [
        'auction_id' => 'required|integer|is_not_unique[auctions.id]',
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'amount' => 'required|decimal|greater_than[0]',
        'tokens_used' => 'required|integer|greater_than[0]',
    ];

    protected $validationMessages = [
        'auction_id' => [
            'required' => 'Auction ID is required',
            'integer' => 'Auction ID must be an integer',
            'is_not_unique' => 'Auction does not exist',
        ],
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be an integer',
            'is_not_unique' => 'User does not exist',
        ],
        'amount' => [
            'required' => 'Bid amount is required',
            'decimal' => 'Bid amount must be a valid decimal',
            'greater_than' => 'Bid amount must be greater than 0',
        ],
        'tokens_used' => [
            'required' => 'Tokens used is required',
            'integer' => 'Tokens used must be an integer',
            'greater_than' => 'Tokens used must be greater than 0',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getBidsForAuction($auctionId)
    {
        return $this->select('bids.*, users.name as user_name, users.phone as user_phone')
                    ->join('users', 'users.id = bids.user_id')
                    ->where('auction_id', $auctionId)
                    ->orderBy('amount', 'DESC')
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    public function getHighestBid($auctionId)
    {
        return $this->where('auction_id', $auctionId)
                    ->orderBy('amount', 'DESC')
                    ->orderBy('created_at', 'ASC')
                    ->first();
    }

    public function getUserBids($userId)
    {
        return $this->select('bids.*, auctions.item_name, auctions.start_time, auctions.end_time')
                    ->join('auctions', 'auctions.id = bids.auction_id')
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function isHighestBidder($auctionId, $userId)
    {
        $highestBid = $this->getHighestBid($auctionId);
        return $highestBid && $highestBid['user_id'] == $userId;
    }

    public function canBid($auctionId, $userId, $amount)
    {
        // Check if auction is live
        $auctionModel = new AuctionModel();
        if (!$auctionModel->isLive($auctionId)) {
            return ['success' => false, 'message' => 'Auction is not live'];
        }

        // Check if user has enough tokens
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user || $user['tokens'] < 1) {
            return ['success' => false, 'message' => 'Insufficient tokens'];
        }

        // Check if user is already the highest bidder
        if ($this->isHighestBidder($auctionId, $userId)) {
            return ['success' => false, 'message' => 'You already have the highest bid'];
        }

        // Check if bid amount is higher than current highest
        $highestBid = $this->getHighestBid($auctionId);
        if ($highestBid && $amount <= $highestBid['amount']) {
            return ['success' => false, 'message' => 'Bid must be higher than current highest bid'];
        }

        return ['success' => true];
    }

    public function placeBid($auctionId, $userId, $amount)
    {
        $validation = $this->canBid($auctionId, $userId, $amount);
        if (!$validation['success']) {
            return $validation;
        }

        $this->db->transStart();

        try {
            // Create the bid
            $bidData = [
                'auction_id' => $auctionId,
                'user_id' => $userId,
                'amount' => $amount,
                'tokens_used' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->insert($bidData);

            // Deduct token from user
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            $userModel->updateTokens($userId, $user['tokens'] - 1);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return ['success' => false, 'message' => 'Failed to place bid'];
            }

            return ['success' => true, 'message' => 'Bid placed successfully'];
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => 'Error placing bid: ' . $e->getMessage()];
        }
    }
} 