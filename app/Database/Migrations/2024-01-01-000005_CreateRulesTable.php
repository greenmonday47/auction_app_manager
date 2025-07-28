<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRulesTable extends Migration
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
            'content' => [
                'type' => 'TEXT',
            ],
            'last_updated' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->createTable('rules');
    }

    public function down()
    {
        $this->forge->dropTable('rules');
    }
} 