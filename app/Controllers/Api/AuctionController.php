<?php

namespace App\Controllers\Api;

use App\Models\AuctionModel;
use App\Models\BidModel;

class AuctionController extends BaseApiController
{
    public function getUpcoming()
    {
        $auctions = $this->auctionModel->getUpcoming();
        
        // Format the data
        foreach ($auctions as &$auction) {
            $auction['starting_price_formatted'] = $this->formatCurrency($auction['starting_price']);
            $auction['start_time_formatted'] = date('M j, Y g:i A', strtotime($auction['start_time']));
            $auction['end_time_formatted'] = date('M j, Y g:i A', strtotime($auction['end_time']));
        }

        // Format each auction with proper data types
        $formattedAuctions = array_map([$this, 'formatAuctionData'], $auctions);

        return $this->successResponse($formattedAuctions, 'Upcoming auctions retrieved successfully');
    }

    public function getLive()
    {
        $auctions = $this->auctionModel->getLive();
        
        // Format the data and add current highest bid
        foreach ($auctions as &$auction) {
            $auction['starting_price_formatted'] = $this->formatCurrency($auction['starting_price']);
            $auction['start_time_formatted'] = date('M j, Y g:i A', strtotime($auction['start_time']));
            $auction['end_time_formatted'] = date('M j, Y g:i A', strtotime($auction['end_time']));
            
            // Get current highest bid
            $highestBid = $this->bidModel->getHighestBid($auction['id']);
            $auction['current_highest_bid'] = $highestBid ? $highestBid['amount'] : $auction['starting_price'];
            $auction['current_highest_bid_formatted'] = $this->formatCurrency($auction['current_highest_bid']);
            $auction['total_bids'] = $this->bidModel->where('auction_id', $auction['id'])->countAllResults();
        }

        // Format each auction with proper data types
        $formattedAuctions = array_map([$this, 'formatAuctionData'], $auctions);

        return $this->successResponse($formattedAuctions, 'Live auctions retrieved successfully');
    }

    public function getCompleted()
    {
        $auctions = $this->auctionModel->getCompleted();
        
        // Format the data
        foreach ($auctions as &$auction) {
            $auction['starting_price_formatted'] = $this->formatCurrency($auction['starting_price']);
            $auction['start_time_formatted'] = date('M j, Y g:i A', strtotime($auction['start_time']));
            $auction['end_time_formatted'] = date('M j, Y g:i A', strtotime($auction['end_time']));
            
            // Get winner info and final bid
            $highestBid = $this->bidModel->getHighestBid($auction['id']);
            $auction['final_bid'] = $highestBid ? $highestBid['amount'] : $auction['starting_price'];
            $auction['final_bid_formatted'] = $this->formatCurrency($auction['final_bid']);
            $auction['total_bids'] = $this->bidModel->where('auction_id', $auction['id'])->countAllResults();
        }

        // Format each auction with proper data types
        $formattedAuctions = array_map([$this, 'formatAuctionData'], $auctions);

        return $this->successResponse($formattedAuctions, 'Completed auctions retrieved successfully');
    }

    public function getAuction($id = null)
    {
        if (!$id) {
            return $this->errorResponse('Auction ID is required', 400);
        }

        $auction = $this->auctionModel->getAuctionWithBids($id);
        if (!$auction) {
            return $this->errorResponse('Auction not found', 404);
        }

        // Format the data
        $auction['starting_price_formatted'] = $this->formatCurrency($auction['starting_price']);
        $auction['start_time_formatted'] = date('M j, Y g:i A', strtotime($auction['start_time']));
        $auction['end_time_formatted'] = date('M j, Y g:i A', strtotime($auction['end_time']));
        
        // Format bid amounts
        foreach ($auction['bids'] as &$bid) {
            $bid['amount_formatted'] = $this->formatCurrency($bid['amount']);
            $bid['created_at_formatted'] = date('M j, Y g:i A', strtotime($bid['created_at']));
        }

        // Add current status
        $auction['is_live'] = $this->auctionModel->isLive($id);
        $auction['is_completed'] = (bool)$auction['is_completed'];

        // Format the auction with proper data types
        $formattedAuction = $this->formatAuctionData($auction);

        return $this->successResponse($formattedAuction, 'Auction details retrieved successfully');
    }

    public function placeBid($auctionId = null)
    {
        // Require authentication
        $auth = $this->requireAuth();
        if ($auth) {
            return $auth;
        }

        if (!$auctionId) {
            return $this->errorResponse('Auction ID is required', 400);
        }

        $rules = [
            'amount' => 'required|decimal|greater_than[0]',
        ];

        $validation = $this->validateRequest($rules);
        if ($validation) {
            return $validation;
        }

        $amount = (float)$this->request->getPost('amount');
        $userId = $this->currentUser['id'];

        $result = $this->bidModel->placeBid($auctionId, $userId, $amount);
        
        if ($result['success']) {
            return $this->successResponse(null, $result['message']);
        } else {
            return $this->errorResponse($result['message']);
        }
    }
} 