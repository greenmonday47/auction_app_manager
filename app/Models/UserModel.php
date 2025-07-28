<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['phone', 'pin', 'name', 'tokens'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'phone' => 'required|min_length[10]|max_length[20]|is_unique[users.phone,id,{id}]',
        'pin' => 'required|min_length[4]|max_length[6]',
        'name' => 'required|min_length[2]|max_length[100]',
        'tokens' => 'integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'phone' => [
            'required' => 'Phone number is required',
            'min_length' => 'Phone number must be at least 10 characters',
            'max_length' => 'Phone number cannot exceed 20 characters',
            'is_unique' => 'Phone number already exists',
        ],
        'pin' => [
            'required' => 'PIN is required',
            'min_length' => 'PIN must be at least 4 characters',
            'max_length' => 'PIN cannot exceed 6 characters',
        ],
        'name' => [
            'required' => 'Name is required',
            'min_length' => 'Name must be at least 2 characters',
            'max_length' => 'Name cannot exceed 100 characters',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = ['hashPin'];
    protected $beforeUpdate = ['hashPin'];

    protected function hashPin(array $data)
    {
        if (isset($data['data']['pin'])) {
            $data['data']['pin'] = password_hash($data['data']['pin'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    public function verifyPin($phone, $pin)
    {
        $user = $this->where('phone', $phone)->first();
        if ($user && password_verify($pin, $user['pin'])) {
            return $user;
        }
        return false;
    }

    public function updateTokens($userId, $tokens)
    {
        return $this->update($userId, ['tokens' => $tokens]);
    }



    public function getWithBids($userId = null)
    {
        $builder = $this->db->table('users u');
        $builder->select('u.*, COUNT(b.id) as total_bids, SUM(b.amount) as total_bid_amount');
        $builder->join('bids b', 'u.id = b.user_id', 'left');
        
        if ($userId) {
            $builder->where('u.id', $userId);
        }
        
        $builder->groupBy('u.id');
        $builder->orderBy('u.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }
} 