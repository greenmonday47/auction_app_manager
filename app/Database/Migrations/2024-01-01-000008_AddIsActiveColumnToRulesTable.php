<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsActiveColumnToRulesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('rules', [
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'after' => 'category',
            ],
            'is_custom' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'is_active',
            ],
            'key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'after' => 'is_custom',
            ],
            'value' => [
                'type' => 'TEXT',
                'after' => 'key',
            ],
            'description' => [
                'type' => 'TEXT',
                'after' => 'value',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'after' => 'description',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'created_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('rules', ['is_active', 'is_custom', 'key', 'value', 'description', 'created_at', 'updated_at']);
    }
} 