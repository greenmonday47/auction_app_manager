<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuctionSeeder extends Seeder
{
    public function run()
    {
        $auctions = [
            [
                'item_name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with advanced camera system and titanium design',
                'image' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400&h=225&fit=crop',
                'start_time' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                'end_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
                'starting_price' => 2500000.00,
                'is_completed' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'item_name' => 'MacBook Air M2',
                'description' => 'Powerful laptop with Apple M2 chip and all-day battery life',
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=225&fit=crop',
                'start_time' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
                'end_time' => date('Y-m-d H:i:s', strtotime('+1 hour 30 minutes')),
                'starting_price' => 3500000.00,
                'is_completed' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'item_name' => 'Samsung Galaxy S24',
                'description' => 'Premium Android smartphone with AI features',
                'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=225&fit=crop',
                'start_time' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'end_time' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
                'starting_price' => 1800000.00,
                'is_completed' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'item_name' => 'AirPods Pro',
                'description' => 'Wireless earbuds with active noise cancellation',
                'image' => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=400&h=225&fit=crop',
                'start_time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'end_time' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'starting_price' => 450000.00,
                'is_completed' => 1,
                'winner_id' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'item_name' => 'iPad Air',
                'description' => 'Versatile tablet perfect for work and entertainment',
                'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&h=225&fit=crop',
                'start_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
                'end_time' => date('Y-m-d H:i:s', strtotime('+3 hours')),
                'starting_price' => 1200000.00,
                'is_completed' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        // Insert auctions
        foreach ($auctions as $auction) {
            $this->db->table('auctions')->insert($auction);
        }
    }
} 