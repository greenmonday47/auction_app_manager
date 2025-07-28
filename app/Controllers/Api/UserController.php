<?php

namespace App\Controllers\Api;

use App\Models\BidModel;

class UserController extends BaseApiController
{
    public function getProfile()
    {
        // Require authentication
        $auth = $this->requireAuth();
        if ($auth) {
            return $auth;
        }

        $user = $this->currentUser;
        unset($user['pin']); // Don't send PIN back

        return $this->successResponse($this->formatUserData($user), 'Profile retrieved successfully');
    }

    public function updateProfile()
    {
        // Require authentication
        $auth = $this->requireAuth();
        if ($auth) {
            return $auth;
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
        ];

        $validation = $this->validateRequest($rules);
        if ($validation) {
            return $validation;
        }

        $name = $this->request->getPost('name');
        $userId = $this->currentUser['id'];

        $updated = $this->userModel->update($userId, ['name' => $name]);
        
        if ($updated) {
            $user = $this->userModel->find($userId);
            unset($user['pin']); // Don't send PIN back
            return $this->successResponse($this->formatUserData($user), 'Profile updated successfully');
        } else {
            return $this->errorResponse('Failed to update profile');
        }
    }

    public function getBidHistory()
    {
        // Require authentication
        $auth = $this->requireAuth();
        if ($auth) {
            return $auth;
        }

        $userId = $this->currentUser['id'];
        $bids = $this->bidModel->getUserBids($userId);

        // Format the data
        foreach ($bids as &$bid) {
            $bid['amount_formatted'] = $this->formatCurrency($bid['amount']);
            $bid['created_at_formatted'] = date('M j, Y g:i A', strtotime($bid['created_at']));
            $bid['start_time_formatted'] = date('M j, Y g:i A', strtotime($bid['start_time']));
            $bid['end_time_formatted'] = date('M j, Y g:i A', strtotime($bid['end_time']));
        }

        // Format each bid with proper data types
        $formattedBids = array_map([$this, 'formatBidData'], $bids);

        return $this->successResponse($formattedBids, 'Bid history retrieved successfully');
    }
} 