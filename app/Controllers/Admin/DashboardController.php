<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use App\Models\AuctionModel;
use App\Models\TransactionModel;
use App\Models\BidModel;

class DashboardController extends BaseAdminController
{
    public function index()
    {
        $this->requireAuth();

        $userModel = new UserModel();
        $auctionModel = new AuctionModel();
        $transactionModel = new TransactionModel();
        $bidModel = new BidModel();

        // Get statistics
        $stats = [
            'total_users' => $userModel->countAll(),
            'total_auctions' => $auctionModel->countAll(),
            'live_auctions' => count($auctionModel->getLive()),
            'completed_auctions' => count($auctionModel->getCompleted()),
            'pending_transactions' => count($transactionModel->getPendingTransactions()),
            'total_bids' => $bidModel->countAll(),
        ];

        // Get recent activities
        $recentUsers = $userModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
        $recentAuctions = $auctionModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
        
        // Add status to recent auctions
        foreach ($recentAuctions as &$auction) {
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
        
        $recentTransactions = $transactionModel->getWithUser();

        $data = [
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'recent_auctions' => $recentAuctions,
            'recent_transactions' => array_slice($recentTransactions, 0, 5),
        ];

        return $this->render('dashboard/index', $data);
    }
} 