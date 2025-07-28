<?php

namespace App\Controllers\Admin;

class AuthController extends BaseAdminController
{
    public function login()
    {
        if ($this->isLoggedIn()) {
            return redirect()->to('/admin/dashboard');
        }

        return $this->render('auth/login');
    }

    public function doLogin()
    {
        $pin = $this->request->getPost('pin');
        
        if (!$pin) {
            $this->errorMessage('PIN is required');
            return redirect()->back();
        }

        $admin = $this->adminModel->verifyPin($pin);
        if ($admin) {
            $this->session->set([
                'admin_logged_in' => true,
                'admin_id' => $admin['id']
            ]);
            
            $this->successMessage('Login successful');
            return redirect()->to('/admin/dashboard');
        } else {
            $this->errorMessage('Invalid PIN');
            return redirect()->back();
        }
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/admin/login');
    }

    public function setup()
    {
        // Check if admin already exists
        if ($this->adminModel->countAll() > 0) {
            $this->errorMessage('Admin already exists');
            return redirect()->to('/admin/login');
        }

        $pin = $this->request->getPost('pin');
        
        if (!$pin || strlen($pin) < 4) {
            $this->errorMessage('PIN must be at least 4 characters');
            return redirect()->back();
        }

        log_message('debug', 'AuthController::setup - Attempting to create admin with PIN: ' . $pin);
        
        $created = $this->adminModel->createAdmin($pin);
        log_message('debug', 'AuthController::setup - Create result: ' . json_encode($created));
        
        if ($created) {
            $this->successMessage('Admin account created successfully. You can now login.');
            return redirect()->to('/admin/login');
        } else {
            $this->errorMessage('Failed to create admin account');
            return redirect()->back();
        }
    }
} 