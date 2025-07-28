<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuctionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'item_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'image' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'start_time' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'end_time' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'starting_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'is_completed' => [
                'type' => 'BOOLEAN',
                'default' => 0,
            ],
            'winner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('winner_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('auctions');
    }

    public function down()
    {
        $this->forge->dropTable('auctions');
    }
} 