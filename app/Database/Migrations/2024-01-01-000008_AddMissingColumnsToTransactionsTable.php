<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transactions', [
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['deposit', 'withdrawal', 'refund'],
                'default' => 'deposit',
                'after' => 'user_id',
            ],
            'reference' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'type',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'reference',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', ['type', 'reference', 'description']);
    }
} 