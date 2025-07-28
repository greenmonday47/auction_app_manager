<?php

namespace App\Controllers\Api;

use App\Models\UserModel;

class AuthController extends BaseApiController
{
    public function register()
    {
        $rules = [
            'phone' => 'required|min_length[10]|max_length[20]|is_unique[users.phone]',
            'pin' => 'required|min_length[4]|max_length[6]',
            'name' => 'required|min_length[2]|max_length[100]',
        ];

        $validation = $this->validateRequest($rules);
        if ($validation) {
            return $validation;
        }

        // Try to get data from JSON first, then fallback to POST
        $jsonData = $this->request->getJSON(true);
        $userData = [
            'phone' => $jsonData['phone'] ?? $this->request->getPost('phone'),
            'pin' => $jsonData['pin'] ?? $this->request->getPost('pin'),
            'name' => $jsonData['name'] ?? $this->request->getPost('name'),
            'tokens' => 0
        ];

        try {
            $userId = $this->userModel->insert($userData);
            
            if ($userId) {
                $user = $this->userModel->find($userId);
                unset($user['pin']); // Don't send PIN back
                
                // For registration, we'll use phone+pin as a simple token
                $pin = $jsonData['pin'] ?? $this->request->getPost('pin');
                $token = base64_encode($user['phone'] . ':' . $pin);
                
                return $this->successResponse([
                    'user' => $this->formatUserData($user),
                    'token' => $token
                ], 'User registered successfully', 201);
            } else {
                return $this->errorResponse('Failed to register user');
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed: ' . $e->getMessage());
        }
    }

    public function login()
    {
        $rules = [
            'phone' => 'required|min_length[10]|max_length[20]',
            'pin' => 'required|min_length[4]|max_length[6]',
        ];

        $validation = $this->validateRequest($rules);
        if ($validation) {
            return $validation;
        }

        // Try to get data from JSON first, then fallback to POST
        $jsonData = $this->request->getJSON(true);
        $phone = $jsonData['phone'] ?? $this->request->getPost('phone');
        $pin = $jsonData['pin'] ?? $this->request->getPost('pin');

        $user = $this->userModel->verifyPin($phone, $pin);
        if ($user) {
            unset($user['pin']); // Don't send PIN back
            
            // Create a simple token using phone+pin
            $token = base64_encode($user['phone'] . ':' . $pin);
            
            return $this->successResponse([
                'user' => $this->formatUserData($user),
                'token' => $token
            ], 'Login successful');
        } else {
            return $this->errorResponse('Invalid phone number or PIN', 401);
        }
    }
} 