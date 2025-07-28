<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'John Doe',
                'phone' => '256701234567',
                'pin' => password_hash('1234', PASSWORD_DEFAULT),
                'tokens' => 1000,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Jane Smith',
                'phone' => '256702345678',
                'pin' => password_hash('1234', PASSWORD_DEFAULT),
                'tokens' => 500,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Bob Johnson',
                'phone' => '256703456789',
                'pin' => password_hash('1234', PASSWORD_DEFAULT),
                'tokens' => 2000,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        // Insert users
        foreach ($users as $user) {
            $this->db->table('users')->insert($user);
        }
    }
} 