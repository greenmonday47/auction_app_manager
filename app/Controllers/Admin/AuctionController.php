<?php

namespace App\Controllers\Admin;

use App\Models\AuctionModel;
use App\Models\BidModel;
use App\Models\UserModel;

class AuctionController extends BaseAdminController
{
    protected $auctionModel;
    protected $bidModel;
    protected $userModel;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->auctionModel = new AuctionModel();
        $this->bidModel = new BidModel();
        $this->userModel = new UserModel();
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
        $query = $this->auctionModel;
        
        if ($search) {
            $query = $query->like('item_name', $search)->orLike('description', $search);
        }
        
        if ($status) {
            $now = date('Y-m-d H:i:s');
            switch ($status) {
                case 'live':
                    $query = $query->where('start_time <=', $now)
                                   ->where('end_time >', $now)
                                   ->where('is_completed', 0);
                    break;
                case 'completed':
                    $query = $query->where('is_completed', 1);
                    break;
                case 'pending':
                    $query = $query->where('start_time >', $now)
                                   ->where('is_completed', 0);
                    break;
            }
        }

        // Apply sorting
        switch ($sort) {
            case 'created_at_asc':
                $query = $query->orderBy('created_at', 'ASC');
                break;
            case 'title_asc':
                $query = $query->orderBy('item_name', 'ASC');
                break;
            case 'title_desc':
                $query = $query->orderBy('item_name', 'DESC');
                break;
            case 'starting_price_desc':
                $query = $query->orderBy('starting_price', 'DESC');
                break;
            case 'starting_price_asc':
                $query = $query->orderBy('starting_price', 'ASC');
                break;
            default:
                $query = $query->orderBy('created_at', 'DESC');
        }

        $totalAuctions = $query->countAllResults(false);
        $auctions = $query->limit($perPage, ($page - 1) * $perPage)->findAll();

        // Get additional data for each auction
        foreach ($auctions as &$auction) {
            $auction['bid_count'] = $this->bidModel->where('auction_id', $auction['id'])->countAllResults();
            $auction['current_bid'] = $this->bidModel->where('auction_id', $auction['id'])
                ->orderBy('amount', 'DESC')
                ->first()['amount'] ?? 0;
            
            // Calculate status
            $now = date('Y-m-d H:i:s');
            if ($auction['is_completed']) {
                $auction['status'] = 'completed';
            } elseif ($auction['start_time'] <= $now && $auction['end_time'] > $now) {
                $auction['status'] = 'live';
            } elseif ($auction['start_time'] > $now) {
                $auction['status'] = 'pending';
            } else {
                $auction['status'] = 'expired';
            }
        }

        // Get statistics
        $stats = [
            'total_auctions' => $this->auctionModel->countAll(),
            'live_auctions' => count($this->auctionModel->getLive()),
            'completed_auctions' => count($this->auctionModel->getCompleted()),
            'pending_auctions' => count($this->auctionModel->getUpcoming()),
        ];

        $pager = service('pager');
        $pager->setPath('admin/auctions');
        $pager->makeLinks($page, $perPage, $totalAuctions);

        $data = [
            'title' => 'Auction Management',
            'auctions' => $auctions,
            'total_auctions' => $totalAuctions,
            'stats' => $stats,
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'pager' => $pager,
        ];

        return $this->render('auctions/index', $data);
    }

    public function add()
    {
        $this->requireAuth();

        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $startingPrice = $this->request->getPost('starting_price');
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        $imageUrl = $this->request->getPost('image_url');

        if (!$title || !$description || !$startingPrice || !$startTime || !$endTime) {
            $this->errorMessage('All fields are required');
            return redirect()->back();
        }

        // Validate image URL if provided
        if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $this->errorMessage('Please enter a valid image URL');
            return redirect()->back();
        }

        // Validate that end time is after start time
        $startDateTime = new \DateTime($startTime);
        $endDateTime = new \DateTime($endTime);
        
        if ($endDateTime <= $startDateTime) {
            $this->errorMessage('End time must be after start time');
            return redirect()->back();
        }

        $auctionData = [
            'item_name' => $title,
            'description' => $description,
            'image' => $imageUrl ?: null,
            'starting_price' => $startingPrice,
            'start_time' => $startDateTime->format('Y-m-d H:i:s'),
            'end_time' => $endDateTime->format('Y-m-d H:i:s'),
            'is_completed' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->auctionModel->insert($auctionData)) {
            $this->successMessage('Auction created successfully');
        } else {
            $this->errorMessage('Failed to create auction');
        }

        return redirect()->to('/admin/auctions');
    }

    public function edit()
    {
        $this->requireAuth();

        $auctionId = $this->request->getPost('auction_id');
        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $startingPrice = $this->request->getPost('starting_price');
        $imageUrl = $this->request->getPost('image_url');

        if (!$auctionId || !$title || !$description || !$startingPrice) {
            $this->errorMessage('All fields are required');
            return redirect()->back();
        }

        // Validate image URL if provided
        if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $this->errorMessage('Please enter a valid image URL');
            return redirect()->back();
        }

        $auction = $this->auctionModel->find($auctionId);
        if (!$auction) {
            $this->errorMessage('Auction not found');
            return redirect()->back();
        }

        // Don't allow editing if auction is live or completed
        $now = date('Y-m-d H:i:s');
        $isLive = $auction['start_time'] <= $now && $auction['end_time'] > $now && !$auction['is_completed'];
        $isCompleted = $auction['is_completed'];
        
        if ($isLive || $isCompleted) {
            $this->errorMessage('Cannot edit live or completed auctions');
            return redirect()->back();
        }

        $updateData = [
            'item_name' => $title,
            'description' => $description,
            'image' => $imageUrl ?: null,
            'starting_price' => $startingPrice,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->auctionModel->update($auctionId, $updateData)) {
            $this->successMessage('Auction updated successfully');
        } else {
            $this->errorMessage('Failed to update auction');
        }

        return redirect()->to('/admin/auctions');
    }

    public function start($auctionId)
    {
        $this->requireAuth();

        $auction = $this->auctionModel->find($auctionId);
        if (!$auction) {
            $this->errorMessage('Auction not found');
            return redirect()->to('/admin/auctions');
        }

        // Check if auction is pending (not started yet and not completed)
        $now = date('Y-m-d H:i:s');
        $isPending = $auction['start_time'] > $now && !$auction['is_completed'];
        
        if (!$isPending) {
            $this->errorMessage('Only pending auctions can be started');
            return redirect()->to('/admin/auctions');
        }

        // Check if start time has passed
        if (strtotime($auction['start_time']) > time()) {
            $this->errorMessage('Auction start time has not been reached');
            return redirect()->to('/admin/auctions');
        }

        $updateData = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->auctionModel->update($auctionId, $updateData)) {
            $this->successMessage('Auction started successfully');
        } else {
            $this->errorMessage('Failed to start auction');
        }

        return redirect()->to('/admin/auctions');
    }

    public function end($auctionId)
    {
        $this->requireAuth();

        $auction = $this->auctionModel->find($auctionId);
        if (!$auction) {
            $this->errorMessage('Auction not found');
            return redirect()->to('/admin/auctions');
        }

        // Check if auction is live
        $now = date('Y-m-d H:i:s');
        $isLive = $auction['start_time'] <= $now && $auction['end_time'] > $now && !$auction['is_completed'];
        
        if (!$isLive) {
            $this->errorMessage('Only live auctions can be ended');
            return redirect()->to('/admin/auctions');
        }

        // Get the winning bid
        $winningBid = $this->bidModel->where('auction_id', $auctionId)
            ->orderBy('amount', 'DESC')
            ->first();

        $updateData = [
            'is_completed' => 1,
            'winner_id' => $winningBid['user_id'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->auctionModel->update($auctionId, $updateData)) {
            $this->successMessage('Auction ended successfully');
        } else {
            $this->errorMessage('Failed to end auction');
        }

        return redirect()->to('/admin/auctions');
    }

    public function delete($auctionId)
    {
        $this->requireAuth();

        $auction = $this->auctionModel->find($auctionId);
        if (!$auction) {
            $this->errorMessage('Auction not found');
            return redirect()->to('/admin/auctions');
        }

        // Don't allow deletion if auction has bids
        $bidCount = $this->bidModel->where('auction_id', $auctionId)->countAllResults();
        if ($bidCount > 0) {
            $this->errorMessage('Cannot delete auction with existing bids');
            return redirect()->to('/admin/auctions');
        }

        if ($this->auctionModel->delete($auctionId)) {
            $this->successMessage('Auction deleted successfully');
        } else {
            $this->errorMessage('Failed to delete auction');
        }

        return redirect()->to('/admin/auctions');
    }

    public function view($auctionId)
    {
        $this->requireAuth();

        $auction = $this->auctionModel->find($auctionId);
        if (!$auction) {
            $this->errorMessage('Auction not found');
            return redirect()->to('/admin/auctions');
        }

        // Get bids for this auction
        $bids = $this->db->table('bids')
            ->select('bids.*, users.name as user_name, users.phone as user_phone')
            ->join('users', 'users.id = bids.user_id')
            ->where('bids.auction_id', $auctionId)
            ->orderBy('bids.amount', 'DESC')
            ->get()
            ->getResultArray();

        // Get winning bid info
        $winningBid = null;
        if ($auction['winner_id']) {
            $winningBid = $this->db->table('bids')
                ->select('bids.*, users.name as user_name, users.phone as user_phone')
                ->join('users', 'users.id = bids.user_id')
                ->where('bids.user_id', $auction['winner_id'])
                ->where('bids.auction_id', $auctionId)
                ->orderBy('bids.amount', 'DESC')
                ->get()
                ->getRowArray();
        }

        $data = [
            'title' => 'Auction Details',
            'auction' => $auction,
            'bids' => $bids,
            'winning_bid' => $winningBid,
        ];

        return $this->render('auctions/view', $data);
    }

    public function get($auctionId)
    {
        $this->requireAuth();

        $auction = $this->auctionModel->find($auctionId);
        if (!$auction) {
            return $this->response->setJSON(['error' => 'Auction not found'])->setStatusCode(404);
        }

        return $this->response->setJSON($auction);
    }
} 