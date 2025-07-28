<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\AdminModel;

class BaseAdminController extends Controller
{
    protected $adminModel;
    protected $session;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
        $this->session = \Config\Services::session();
    }

    protected function isLoggedIn()
    {
        return $this->session->get('admin_logged_in') === true;
    }

    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/admin/login');
        }
    }

    protected function formatCurrency($amount)
    {
        return number_format($amount, 0, '.', ',') . ' UGX';
    }

    protected function render($view, $data = [])
    {
        // Add common data
        $data['base_url'] = base_url();
        $data['current_page'] = $this->request->getUri()->getSegment(2) ?? 'dashboard';
        
        return view('admin/' . $view, $data);
    }

    protected function successMessage($message)
    {
        $this->session->setFlashdata('success', $message);
    }

    protected function errorMessage($message)
    {
        $this->session->setFlashdata('error', $message);
    }
} 