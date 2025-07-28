<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['pin'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;

    // Validation
    protected $validationRules = [
        'pin' => 'required|min_length[4]|max_length[6]',
    ];

    protected $validationMessages = [
        'pin' => [
            'required' => 'PIN is required',
            'min_length' => 'PIN must be at least 4 characters',
            'max_length' => 'PIN cannot exceed 6 characters',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = [];
    protected $beforeUpdate = [];

    protected function hashPin(array $data)
    {
        if (isset($data['data']['pin'])) {
            $data['data']['pin'] = password_hash($data['data']['pin'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    public function verifyPin($pin)
    {
        $admin = $this->first();
        if ($admin && password_verify($pin, $admin['pin'])) {
            return $admin;
        }
        return false;
    }

    public function createAdmin($pin)
    {
        // Check if admin already exists
        if ($this->countAll() > 0) {
            log_message('error', 'AdminModel::createAdmin - Admin already exists');
            return false;
        }

        // Validate the original PIN before hashing
        if (strlen($pin) < 4 || strlen($pin) > 6) {
            log_message('error', 'AdminModel::createAdmin - PIN length validation failed');
            return false;
        }

        $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');
        
        log_message('debug', 'AdminModel::createAdmin - Attempting to insert with hashed PIN');
        
        try {
            // Use the database builder directly to avoid data transformation issues
            $builder = $this->db->table($this->table);
            $result = $builder->insert([
                'pin' => $hashedPin,
                'created_at' => $now
            ]);
            
            log_message('debug', 'AdminModel::createAdmin - Insert result: ' . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'AdminModel::createAdmin - Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function updatePin($pin)
    {
        $admin = $this->first();
        if (!$admin) {
            return false;
        }

        return $this->update($admin['id'], ['pin' => $pin]);
    }
} 