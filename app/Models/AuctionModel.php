<?php

namespace App\Models;

use CodeIgniter\Model;

class AuctionModel extends Model
{
    protected $table = 'auctions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['item_name', 'description', 'image', 'start_time', 'end_time', 'starting_price', 'is_completed', 'winner_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'item_name' => 'required|min_length[3]|max_length[255]',
        'description' => 'permit_empty|max_length[1000]',
        'image' => 'permit_empty|valid_url',
        'start_time' => 'required|valid_date',
        'end_time' => 'required|valid_date',
        'starting_price' => 'required|decimal|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'item_name' => [
            'required' => 'Item name is required',
            'min_length' => 'Item name must be at least 3 characters',
            'max_length' => 'Item name cannot exceed 255 characters',
        ],
        'image' => [
            'valid_url' => 'Please enter a valid image URL',
        ],
        'start_time' => [
            'required' => 'Start time is required',
            'valid_date' => 'Start time must be a valid date',
        ],
        'end_time' => [
            'required' => 'End time is required',
            'valid_date' => 'End time must be a valid date',
        ],
        'starting_price' => [
            'required' => 'Starting price is required',
            'decimal' => 'Starting price must be a valid decimal',
            'greater_than_equal_to' => 'Starting price must be 0 or greater',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getUpcoming()
    {
        return $this->where('start_time >', date('Y-m-d H:i:s'))
                    ->where('is_completed', 0)
                    ->orderBy('start_time', 'ASC')
                    ->findAll();
    }

    public function getLive()
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('start_time <=', $now)
                    ->where('end_time >', $now)
                    ->where('is_completed', 0)
                    ->orderBy('end_time', 'ASC')
                    ->findAll();
    }

    public function getCompleted()
    {
        return $this->where('is_completed', 1)
                    ->orderBy('end_time', 'DESC')
                    ->findAll();
    }

    public function getWithBids($auctionId = null)
    {
        $builder = $this->db->table('auctions a');
        $builder->select('a.*, u.name as winner_name, u.phone as winner_phone, COUNT(b.id) as total_bids, MAX(b.amount) as highest_bid');
        $builder->join('users u', 'a.winner_id = u.id', 'left');
        $builder->join('bids b', 'a.id = b.auction_id', 'left');
        
        if ($auctionId) {
            $builder->where('a.id', $auctionId);
        }
        
        $builder->groupBy('a.id');
        $builder->orderBy('a.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    public function getAuctionWithBids($auctionId)
    {
        $auction = $this->getWithBids($auctionId);
        if (empty($auction)) {
            return null;
        }

        $auction = $auction[0];
        
        // Get all bids for this auction
        $bidModel = new BidModel();
        $auction['bids'] = $bidModel->getBidsForAuction($auctionId);
        
        return $auction;
    }

    public function markAsCompleted($auctionId)
    {
        // Find the highest bidder
        $bidModel = new BidModel();
        $highestBid = $bidModel->getHighestBid($auctionId);
        
        $winnerId = $highestBid ? $highestBid['user_id'] : null;
        
        return $this->update($auctionId, [
            'is_completed' => 1,
            'winner_id' => $winnerId
        ]);
    }

    public function isLive($auctionId)
    {
        $auction = $this->find($auctionId);
        if (!$auction) {
            return false;
        }
        
        $now = date('Y-m-d H:i:s');
        return $auction['start_time'] <= $now && $auction['end_time'] > $now && !$auction['is_completed'];
    }

    /**
     * Check if auction is active (can accept payments/entries)
     */
    public function isActive($auctionId)
    {
        $auction = $this->find($auctionId);
        if (!$auction) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        // Auction is active if it hasn't ended and isn't completed
        return $auction['end_time'] > $now && !$auction['is_completed'];
    }
} 