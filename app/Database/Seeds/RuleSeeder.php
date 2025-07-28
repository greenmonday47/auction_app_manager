<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RuleSeeder extends Seeder
{
    public function run()
    {
        $rules = [
            // Auction Settings
            [
                'name' => 'Minimum Bid Increment',
                'key' => 'min_bid_increment',
                'category' => 'auction',
                'value' => '1000',
                'description' => 'Minimum amount by which a bid must exceed the current highest bid',
                'is_active' => 1,
                'is_custom' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Maximum Auction Duration',
                'key' => 'max_auction_duration',
                'category' => 'auction',
                'value' => '24',
                'description' => 'Maximum duration of an auction in hours',
                'is_active' => 1,
                'is_custom' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Auto Extend Minutes',
                'key' => 'auto_extend_minutes',
                'category' => 'auction',
                'value' => '5',
                'description' => 'Number of minutes to extend auction when a bid is placed near the end',
                'is_active' => 1,
                'is_custom' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            
            // User Settings
            [
                'name' => 'Minimum Tokens to Bid',
                'key' => 'min_tokens_to_bid',
                'category' => 'user',
                'value' => '1',
                'description' => 'Minimum number of tokens required to place a bid',
                'is_active' => 1,
                'is_custom' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Maximum Concurrent Bids',
                'key' => 'max_concurrent_bids',
                'category' => 'user',
                'value' => '5',
                'description' => 'Maximum number of auctions a user can bid on simultaneously',
                'is_active' => 1,
                'is_custom' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Token Expiry Days',
                'key' => 'token_expiry_days',
                'category' => 'user',
                'value' => '365',
                'description' => 'Number of days before tokens expire',
                'is_active' => 1,
                'is_custom' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        // Insert rules
        foreach ($rules as $rule) {
            $this->db->table('rules')->insert($rule);
        }
    }
} 